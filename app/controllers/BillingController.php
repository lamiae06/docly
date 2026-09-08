<?php
namespace App\Controllers;

use App\Models\Invoice;
use App\Models\Patient;

class BillingController extends Controller {

    public function index(): void {
        $page = (int) ($_GET['page'] ?? 1);
        $invoices = Invoice::allWithPatients($page, 20);

        $this->view('billing.index', [
            'invoices' => $invoices,
            'pageTitle' => 'Facturation',
        ]);
    }

    public function create(): void {
        $patients = Patient::searchPatients('', 1, 200)['data'];

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT `value` FROM clinic_settings WHERE `key` = 'tax_rate'");
        $stmt->execute();
        $taxRate = (float) ($stmt->fetchColumn() ?: 20);

        $this->view('billing.create', [
            'patients' => $patients,
            'taxRate' => $taxRate,
            'pageTitle' => 'Nouvelle facture',
        ]);
    }

    public function store(): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $patientId = (int) ($_POST['patient_id'] ?? 0);
        $dueDate = $_POST['due_date'] ?? '';
        $discountAmount = (float) ($_POST['discount_amount'] ?? 0);
        $taxRate = (float) ($_POST['tax_rate'] ?? 0);
        $notes = trim(strip_tags($_POST['notes'] ?? ''));
        $items = $_POST['items'] ?? [];

        $errors = [];
        if (!$patientId || !Patient::find($patientId)) $errors['patient_id'] = 'Patient invalide.';
        if (empty($dueDate) || strtotime($dueDate) === false) $errors['due_date'] = 'Date d\'échéance invalide.';
        if (empty($items) || !is_array($items)) $errors['items'] = 'Ajoutez au moins une ligne.';

        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        // Le sous-total est TOUJOURS recalculé côté serveur à partir des
        // quantités/prix unitaires envoyés : on ne fait jamais confiance à
        // un "total" calculé côté client.
        $subtotal = 0;
        $validItems = [];
        foreach ($items as $item) {
            $description = trim(strip_tags($item['description'] ?? ''));
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $unitPrice = max(0, (float) ($item['unit_price'] ?? 0));
            if ($description === '') continue;
            $totalPrice = round($quantity * $unitPrice, 2);
            $subtotal += $totalPrice;
            $validItems[] = [
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ];
        }

        if (empty($validItems)) {
            $this->json(['error' => 'Validation échouée', 'errors' => ['items' => 'Ajoutez au moins une ligne valide.']], 422);
        }

        $discountAmount = min($discountAmount, $subtotal);
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = round($taxableAmount * ($taxRate / 100), 2);
        $totalAmount = round($taxableAmount + $taxAmount, 2);

        $db = $GLOBALS['db'];
        $db->beginTransaction();
        try {
            $invoiceNumber = generate_invoice_number();
            $stmt = $db->prepare("
                INSERT INTO invoices (invoice_number, patient_id, issue_date, due_date, subtotal, tax_amount, discount_amount, total_amount, paid_amount, balance_due, status, notes, created_by)
                VALUES (:invoice_number, :patient_id, CURDATE(), :due_date, :subtotal, :tax_amount, :discount_amount, :total_amount, 0, :balance_due, 'pending', :notes, :created_by)
            ");
            $stmt->execute([
                ':invoice_number' => $invoiceNumber,
                ':patient_id' => $patientId,
                ':due_date' => $dueDate,
                ':subtotal' => $subtotal,
                ':tax_amount' => $taxAmount,
                ':discount_amount' => $discountAmount,
                ':total_amount' => $totalAmount,
                ':balance_due' => $totalAmount,
                ':notes' => $notes ?: null,
                ':created_by' => $_SESSION['user_id'] ?? null,
            ]);
            $invoiceId = (int) $db->lastInsertId();

            $itemStmt = $db->prepare("
                INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total_price)
                VALUES (:invoice_id, :description, :quantity, :unit_price, :total_price)
            ");
            foreach ($validItems as $item) {
                $itemStmt->execute([
                    ':invoice_id' => $invoiceId,
                    ':description' => $item['description'],
                    ':quantity' => $item['quantity'],
                    ':unit_price' => $item['unit_price'],
                    ':total_price' => $item['total_price'],
                ]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->json(['error' => 'Erreur lors de la création de la facture.'], 500);
        }

        audit_log('create', 'billing', 'invoice', $invoiceId, 'Facture créée : ' . $invoiceNumber . ' (' . format_money($totalAmount) . ')');

        $this->respondSuccess(['id' => $invoiceId], '/invoices/' . $invoiceId);
    }

    public function showInvoice(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("
            SELECT i.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_code, p.address, p.city
            FROM invoices i
            JOIN patients p ON i.patient_id = p.id
            WHERE i.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $itemsStmt = $db->prepare("SELECT * FROM invoice_items WHERE invoice_id = :id");
        $itemsStmt->execute([':id' => $id]);
        $items = $itemsStmt->fetchAll();

        $paymentsStmt = $db->prepare("SELECT * FROM payments WHERE invoice_id = :id ORDER BY payment_date DESC");
        $paymentsStmt->execute([':id' => $id]);
        $payments = $paymentsStmt->fetchAll();

        $this->view('billing.show', [
            'invoice' => $invoice,
            'items' => $items,
            'payments' => $payments,
            'pageTitle' => 'Facture ' . $invoice['invoice_number'],
        ]);
    }

    public function apiStats(): void {
        $this->json([
            'monthly_revenue' => Invoice::monthlyRevenue(),
            'pending_count' => Invoice::count("status IN ('pending', 'partially_paid')"),
            'overdue' => Invoice::overdue(),
        ]);
    }

    /**
     * Enregistre un paiement rapide sur une facture (marque comme payée).
     */
    public function apiPay(int $id): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $invoice = Invoice::find($id);
        if (!$invoice) {
            $this->json(['error' => 'Facture non trouvée'], 404);
        }

        $balance = (float) $invoice['balance_due'];
        if ($balance <= 0) {
            $this->json(['error' => 'Cette facture est déjà réglée'], 400);
        }

        $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : $balance;
        $amount = min($amount, $balance);
        $method = $_POST['payment_method'] ?? 'card';

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("
            INSERT INTO payments (invoice_id, amount, payment_method, payment_date, received_by)
            VALUES (:invoice_id, :amount, :method, CURDATE(), :received_by)
        ");
        $stmt->execute([
            ':invoice_id' => $id,
            ':amount' => $amount,
            ':method' => $method,
            ':received_by' => $_SESSION['user_id'] ?? null,
        ]);

        $newPaid = (float) $invoice['paid_amount'] + $amount;
        $newBalance = max(0, (float) $invoice['total_amount'] - $newPaid);
        $newStatus = $newBalance <= 0 ? 'paid' : 'partially_paid';

        Invoice::update($id, [
            'paid_amount' => $newPaid,
            'balance_due' => $newBalance,
            'status' => $newStatus,
        ]);

        audit_log('update', 'billing', 'invoice', $id, 'Paiement enregistré: ' . format_money($amount) . ' sur ' . $invoice['invoice_number']);

        $this->json(['success' => true, 'status' => $newStatus, 'balance_due' => $newBalance]);
    }

    public function edit(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM invoices WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $invoice = $stmt->fetch();
        if (!$invoice) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $itemsStmt = $db->prepare("SELECT * FROM invoice_items WHERE invoice_id = :id");
        $itemsStmt->execute([':id' => $id]);
        $items = $itemsStmt->fetchAll();

        $patients = Patient::searchPatients('', 1, 200)['data'];

        $this->view('billing.edit', [
            'invoice' => $invoice,
            'items' => $items,
            'patients' => $patients,
            'pageTitle' => 'Modifier la facture ' . $invoice['invoice_number'],
        ]);
    }

    public function update(int $id): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM invoices WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $invoice = $stmt->fetch();
        if (!$invoice) {
            $this->json(['error' => 'Facture non trouvée'], 404);
        }

        // Une facture déjà (partiellement) réglée ne doit plus être
        // modifiée : cela désynchroniserait les montants payés/dus déjà
        // enregistrés dans "payments". Il faut alors passer par un avoir.
        if ((float) $invoice['paid_amount'] > 0) {
            $this->json(['error' => 'Cette facture a déjà des paiements enregistrés et ne peut plus être modifiée.'], 422);
        }

        $patientId = (int) ($_POST['patient_id'] ?? 0);
        $dueDate = $_POST['due_date'] ?? '';
        $discountAmount = (float) ($_POST['discount_amount'] ?? 0);
        $taxRate = (float) ($_POST['tax_rate'] ?? 0);
        $notes = trim(strip_tags($_POST['notes'] ?? ''));
        $items = $_POST['items'] ?? [];

        $errors = [];
        if (!$patientId || !Patient::find($patientId)) $errors['patient_id'] = 'Patient invalide.';
        if (empty($dueDate) || strtotime($dueDate) === false) $errors['due_date'] = 'Date d\'échéance invalide.';
        if (empty($items) || !is_array($items)) $errors['items'] = 'Ajoutez au moins une ligne.';

        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $subtotal = 0;
        $validItems = [];
        foreach ($items as $item) {
            $description = trim(strip_tags($item['description'] ?? ''));
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $unitPrice = max(0, (float) ($item['unit_price'] ?? 0));
            if ($description === '') continue;
            $totalPrice = round($quantity * $unitPrice, 2);
            $subtotal += $totalPrice;
            $validItems[] = compact('description', 'quantity', 'unitPrice', 'totalPrice');
        }

        if (empty($validItems)) {
            $this->json(['error' => 'Validation échouée', 'errors' => ['items' => 'Ajoutez au moins une ligne valide.']], 422);
        }

        $discountAmount = min($discountAmount, $subtotal);
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = round($taxableAmount * ($taxRate / 100), 2);
        $totalAmount = round($taxableAmount + $taxAmount, 2);

        $db->beginTransaction();
        try {
            $updateStmt = $db->prepare("
                UPDATE invoices
                SET patient_id = :patient_id, due_date = :due_date, subtotal = :subtotal,
                    tax_amount = :tax_amount, discount_amount = :discount_amount,
                    total_amount = :total_amount, balance_due = :balance_due, notes = :notes
                WHERE id = :id
            ");
            $updateStmt->execute([
                ':patient_id' => $patientId,
                ':due_date' => $dueDate,
                ':subtotal' => $subtotal,
                ':tax_amount' => $taxAmount,
                ':discount_amount' => $discountAmount,
                ':total_amount' => $totalAmount,
                ':balance_due' => $totalAmount,
                ':notes' => $notes ?: null,
                ':id' => $id,
            ]);

            $db->prepare("DELETE FROM invoice_items WHERE invoice_id = :id")->execute([':id' => $id]);
            $itemStmt = $db->prepare("
                INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total_price)
                VALUES (:invoice_id, :description, :quantity, :unit_price, :total_price)
            ");
            foreach ($validItems as $item) {
                $itemStmt->execute([
                    ':invoice_id' => $id,
                    ':description' => $item['description'],
                    ':quantity' => $item['quantity'],
                    ':unit_price' => $item['unitPrice'],
                    ':total_price' => $item['totalPrice'],
                ]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->json(['error' => 'Erreur lors de la modification de la facture.'], 500);
        }

        audit_log('update', 'billing', 'invoice', $id, 'Facture modifiée : ' . $invoice['invoice_number']);

        $this->respondSuccess([], '/invoices/' . $id);
    }

    public function delete(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM invoices WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $invoice = $stmt->fetch();
        if (!$invoice) {
            $this->json(['error' => 'Facture non trouvée'], 404);
        }

        if ((float) $invoice['paid_amount'] > 0) {
            $this->json(['error' => 'Cette facture a des paiements enregistrés et ne peut pas être supprimée.'], 422);
        }

        $db->beginTransaction();
        try {
            $db->prepare("DELETE FROM invoice_items WHERE invoice_id = :id")->execute([':id' => $id]);
            $db->prepare("DELETE FROM invoices WHERE id = :id")->execute([':id' => $id]);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->json(['error' => 'Erreur lors de la suppression.'], 500);
        }

        audit_log('delete', 'billing', 'invoice', $id, 'Facture supprimée : ' . $invoice['invoice_number']);

        $this->json(['success' => true]);
    }
}
