<?php
namespace App\Controllers;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Medication;

class PrescriptionController extends Controller {

    public function index(): void {
        $db = $GLOBALS['db'];
        $stmt = $db->query("
            SELECT p.*, pt.first_name as patient_first_name, pt.last_name as patient_last_name,
                   d.first_name as doctor_first_name, d.last_name as doctor_last_name
            FROM prescriptions p
            JOIN patients pt ON p.patient_id = pt.id
            JOIN doctors d ON p.doctor_id = d.id
            ORDER BY p.prescription_date DESC
            LIMIT 50
        ");
        $this->view('prescriptions.index', ['prescriptions' => $stmt->fetchAll(), 'pageTitle' => 'Ordonnances']);
    }

    public function create(): void {
        $patients = Patient::searchPatients('', 1, 200)['data'];
        $doctors = Doctor::allWithStats();
        $medications = Medication::all('name ASC');

        $this->view('prescriptions.create', [
            'patients' => $patients,
            'doctors' => $doctors,
            'medications' => $medications,
            'pageTitle' => 'Nouvelle ordonnance',
        ]);
    }

    public function store(): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $patientId = (int) ($_POST['patient_id'] ?? 0);
        $doctorId = (int) ($_POST['doctor_id'] ?? 0);
        $notes = trim(strip_tags($_POST['notes'] ?? ''));
        $items = $_POST['items'] ?? [];

        $errors = [];
        if (!$patientId || !Patient::find($patientId)) $errors['patient_id'] = 'Patient invalide.';
        if (!$doctorId || !Doctor::find($doctorId)) $errors['doctor_id'] = 'Médecin invalide.';
        if (empty($items) || !is_array($items)) $errors['items'] = 'Ajoutez au moins un médicament.';

        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $db = $GLOBALS['db'];
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO prescriptions (patient_id, doctor_id, prescription_date, notes, status)
                VALUES (:patient_id, :doctor_id, CURDATE(), :notes, 'issued')
            ");
            $stmt->execute([
                ':patient_id' => $patientId,
                ':doctor_id' => $doctorId,
                ':notes' => $notes ?: null,
            ]);
            $prescriptionId = (int) $db->lastInsertId();

            $itemStmt = $db->prepare("
                INSERT INTO prescription_items (prescription_id, medication_id, medication_name, dosage, frequency, duration, instructions, quantity)
                VALUES (:prescription_id, :medication_id, :medication_name, :dosage, :frequency, :duration, :instructions, :quantity)
            ");
            foreach ($items as $item) {
                $medicationName = trim(strip_tags($item['medication_name'] ?? ''));
                if ($medicationName === '') continue;
                $itemStmt->execute([
                    ':prescription_id' => $prescriptionId,
                    ':medication_id' => !empty($item['medication_id']) ? (int) $item['medication_id'] : null,
                    ':medication_name' => $medicationName,
                    ':dosage' => trim(strip_tags($item['dosage'] ?? '')) ?: '-',
                    ':frequency' => trim(strip_tags($item['frequency'] ?? '')) ?: '-',
                    ':duration' => trim(strip_tags($item['duration'] ?? '')) ?: null,
                    ':instructions' => trim(strip_tags($item['instructions'] ?? '')) ?: null,
                    ':quantity' => (int) ($item['quantity'] ?? 1) ?: 1,
                ]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->json(['error' => 'Erreur lors de la création de l\'ordonnance.'], 500);
        }

        audit_log('create', 'prescriptions', 'prescription', $prescriptionId, 'Ordonnance créée');

        $this->json(['success' => true, 'id' => $prescriptionId, 'redirect' => '/prescriptions']);
    }

    public function edit(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM prescriptions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $prescription = $stmt->fetch();
        if (!$prescription) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $itemsStmt = $db->prepare("SELECT * FROM prescription_items WHERE prescription_id = :id");
        $itemsStmt->execute([':id' => $id]);
        $items = $itemsStmt->fetchAll();

        $patients = Patient::searchPatients('', 1, 200)['data'];
        $doctors = Doctor::allWithStats();
        $medications = Medication::all('name ASC');

        $this->view('prescriptions.edit', [
            'prescription' => $prescription,
            'items' => $items,
            'patients' => $patients,
            'doctors' => $doctors,
            'medications' => $medications,
            'pageTitle' => 'Modifier ordonnance',
        ]);
    }

    public function update(int $id): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM prescriptions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $prescription = $stmt->fetch();
        if (!$prescription) {
            $this->json(['error' => 'Ordonnance non trouvée'], 404);
        }

        $patientId = (int) ($_POST['patient_id'] ?? 0);
        $doctorId = (int) ($_POST['doctor_id'] ?? 0);
        $notes = trim(strip_tags($_POST['notes'] ?? ''));
        $items = $_POST['items'] ?? [];

        $errors = [];
        if (!$patientId || !Patient::find($patientId)) $errors['patient_id'] = 'Patient invalide.';
        if (!$doctorId || !Doctor::find($doctorId)) $errors['doctor_id'] = 'Médecin invalide.';
        if (empty($items) || !is_array($items)) $errors['items'] = 'Ajoutez au moins un médicament.';

        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $db->beginTransaction();
        try {
            $updateStmt = $db->prepare("UPDATE prescriptions SET patient_id = :patient_id, doctor_id = :doctor_id, notes = :notes WHERE id = :id");
            $updateStmt->execute([
                ':patient_id' => $patientId,
                ':doctor_id' => $doctorId,
                ':notes' => $notes ?: null,
                ':id' => $id,
            ]);

            // Remplace intégralement la liste de médicaments (plus simple et
            // plus fiable que de tenter de faire correspondre lignes
            // ajoutées/supprimées/modifiées une par une).
            $db->prepare("DELETE FROM prescription_items WHERE prescription_id = :id")->execute([':id' => $id]);

            $itemStmt = $db->prepare("
                INSERT INTO prescription_items (prescription_id, medication_id, medication_name, dosage, frequency, duration, instructions, quantity)
                VALUES (:prescription_id, :medication_id, :medication_name, :dosage, :frequency, :duration, :instructions, :quantity)
            ");
            foreach ($items as $item) {
                $medicationName = trim(strip_tags($item['medication_name'] ?? ''));
                if ($medicationName === '') continue;
                $itemStmt->execute([
                    ':prescription_id' => $id,
                    ':medication_id' => !empty($item['medication_id']) ? (int) $item['medication_id'] : null,
                    ':medication_name' => $medicationName,
                    ':dosage' => trim(strip_tags($item['dosage'] ?? '')) ?: '-',
                    ':frequency' => trim(strip_tags($item['frequency'] ?? '')) ?: '-',
                    ':duration' => trim(strip_tags($item['duration'] ?? '')) ?: null,
                    ':instructions' => trim(strip_tags($item['instructions'] ?? '')) ?: null,
                    ':quantity' => (int) ($item['quantity'] ?? 1) ?: 1,
                ]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->json(['error' => 'Erreur lors de la modification de l\'ordonnance.'], 500);
        }

        audit_log('update', 'prescriptions', 'prescription', $id, 'Ordonnance modifiée');

        $this->json(['success' => true, 'redirect' => '/prescriptions']);
    }

    public function delete(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM prescriptions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $prescription = $stmt->fetch();
        if (!$prescription) {
            $this->json(['error' => 'Ordonnance non trouvée'], 404);
        }

        $db->beginTransaction();
        try {
            $db->prepare("DELETE FROM prescription_items WHERE prescription_id = :id")->execute([':id' => $id]);
            $db->prepare("DELETE FROM prescriptions WHERE id = :id")->execute([':id' => $id]);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->json(['error' => 'Erreur lors de la suppression.'], 500);
        }

        audit_log('delete', 'prescriptions', 'prescription', $id, 'Ordonnance supprimée');

        $this->json(['success' => true]);
    }
}
