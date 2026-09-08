<?php
namespace App\Controllers;

use App\Models\Medication;

class MedicationController extends Controller {

    public function index(): void {
        $medications = Medication::all('name ASC');
        $alerts = Medication::alerts();

        $this->view('medications.index', [
            'medications' => $medications,
            'alerts' => $alerts,
            'pageTitle' => 'Médicaments',
        ]);
    }

    public function apiIndex(): void {
        $this->json(['data' => Medication::all('name ASC')]);
    }

    public function apiAlerts(): void {
        $this->json(['data' => Medication::alerts()]);
    }

    public function create(): void {
        $this->view('medications.create', ['pageTitle' => 'Nouveau médicament']);
    }

    public function store(): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $data = $this->input([
            'name' => 'string',
            'generic_name' => 'string',
            'category' => 'string',
            'form' => 'string',
            'dosage_strength' => 'string',
            'manufacturer' => 'string',
            'description' => 'string',
            'stock_quantity' => 'int',
            'stock_alert_level' => 'int',
            'unit_price' => 'float',
            'expiry_date' => 'date',
            'batch_number' => 'string',
        ]);

        $errors = $this->validate($data, [
            'name' => 'required|max:255',
        ]);
        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $validForms = ['tablet', 'capsule', 'syrup', 'injection', 'cream', 'ointment', 'drops', 'inhaler', 'patch', 'suppository', 'other'];
        if (empty($data['form']) || !in_array($data['form'], $validForms, true)) {
            $data['form'] = 'tablet';
        }
        $data['stock_quantity'] = $data['stock_quantity'] ?: 0;
        $data['stock_alert_level'] = $data['stock_alert_level'] ?: 10;
        $data['unit_price'] = $data['unit_price'] ?: 0;
        $data['is_active'] = 1;

        $id = Medication::create($data);

        audit_log('create', 'medications', 'medication', $id, 'Médicament ajouté : ' . $data['name']);

        $this->json(['success' => true, 'id' => $id, 'redirect' => '/medications']);
    }

    /**
     * Ajuste le stock d'un médicament (réappro / correction).
     * Nouvelle fonctionnalité : permet de réapprovisionner directement
     * depuis la liste des médicaments sans passer par une console SQL.
     */
    public function apiAdjustStock(int $id): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $medication = Medication::find($id);
        if (!$medication) {
            $this->json(['error' => 'Médicament non trouvé'], 404);
        }

        $delta = (int) ($_POST['delta'] ?? 0);
        if ($delta === 0) {
            $this->json(['error' => 'Quantité invalide'], 400);
        }

        $newQuantity = max(0, (int) $medication['stock_quantity'] + $delta);
        Medication::update($id, ['stock_quantity' => $newQuantity]);

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("
            INSERT INTO medication_stock_logs (medication_id, quantity_change, reason, reference_type, created_by)
            VALUES (:medication_id, :change, :reason, :type, :created_by)
        ");
        $stmt->execute([
            ':medication_id' => $id,
            ':change' => $delta,
            ':reason' => $delta > 0 ? 'Réapprovisionnement manuel' : 'Ajustement manuel',
            ':type' => $delta > 0 ? 'purchase' : 'adjustment',
            ':created_by' => $_SESSION['user_id'] ?? null,
        ]);

        audit_log('update', 'medications', 'medication', $id, "Stock ajusté de $delta (" . $medication['stock_quantity'] . " -> $newQuantity)");

        $this->json(['success' => true, 'stock_quantity' => $newQuantity]);
    }

    public function edit(int $id): void {
        $medication = Medication::find($id);
        if (!$medication) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }
        $this->view('medications.edit', ['medication' => $medication, 'pageTitle' => 'Modifier médicament']);
    }

    public function update(int $id): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $medication = Medication::find($id);
        if (!$medication) {
            $this->json(['error' => 'Médicament non trouvé'], 404);
        }

        $data = $this->input([
            'name' => 'string',
            'generic_name' => 'string',
            'category' => 'string',
            'form' => 'string',
            'dosage_strength' => 'string',
            'manufacturer' => 'string',
            'description' => 'string',
            'stock_quantity' => 'int',
            'stock_alert_level' => 'int',
            'unit_price' => 'float',
            'expiry_date' => 'date',
            'batch_number' => 'string',
            'is_active' => 'bool',
        ]);

        $errors = $this->validate($data, [
            'name' => 'required|max:255',
        ]);
        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $validForms = ['tablet', 'capsule', 'syrup', 'injection', 'cream', 'ointment', 'drops', 'inhaler', 'patch', 'suppository', 'other'];
        if (empty($data['form']) || !in_array($data['form'], $validForms, true)) {
            $data['form'] = 'tablet';
        }
        $data['stock_quantity'] = $data['stock_quantity'] ?: 0;
        $data['stock_alert_level'] = $data['stock_alert_level'] ?: 10;
        $data['unit_price'] = $data['unit_price'] ?: 0;

        Medication::update($id, $data);
        audit_log('update', 'medications', 'medication', $id, 'Médicament modifié : ' . $data['name']);

        $this->json(['success' => true, 'redirect' => '/medications']);
    }

    public function delete(int $id): void {
        $medication = Medication::find($id);
        if (!$medication) {
            $this->json(['error' => 'Médicament non trouvé'], 404);
        }

        Medication::delete($id);
        audit_log('delete', 'medications', 'medication', $id, 'Médicament supprimé : ' . $medication['name']);

        $this->json(['success' => true]);
    }
}
