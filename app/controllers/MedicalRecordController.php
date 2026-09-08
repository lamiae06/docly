<?php
namespace App\Controllers;

use App\Models\Patient;

class MedicalRecordController extends Controller {
    public function index(): void {
        $db = $GLOBALS['db'];
        $stmt = $db->query("
            SELECT mr.*, p.first_name, p.last_name, p.patient_code
            FROM medical_records mr
            JOIN patients p ON mr.patient_id = p.id
            ORDER BY mr.created_at DESC
            LIMIT 50
        ");
        $this->view('medical_records.index', ['records' => $stmt->fetchAll(), 'pageTitle' => 'Dossiers médicaux']);
    }

    public function create(): void {
        $patients = Patient::searchPatients('', 1, 200)['data'];
        $this->view('medical_records.create', [
            'patients' => $patients,
            'pageTitle' => 'Nouveau dossier médical',
        ]);
    }

    public function store(): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $data = $this->input([
            'patient_id' => 'int',
            'record_type' => 'string',
            'title' => 'string',
            'description' => 'string',
            'diagnosed_date' => 'date',
            'severity' => 'string',
            'status' => 'string',
        ]);

        $errors = $this->validate($data, [
            'patient_id' => 'required',
            'title' => 'required|max:255',
        ]);
        if (!empty($data['patient_id']) && !Patient::find((int) $data['patient_id'])) {
            $errors['patient_id'] = 'Patient invalide.';
        }
        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $validTypes = ['allergy', 'chronic_disease', 'surgery', 'family_history', 'vaccination', 'general'];
        $validSeverities = ['low', 'medium', 'high', 'critical'];
        $validStatuses = ['active', 'resolved', 'ongoing', 'monitored'];

        $data['record_type'] = in_array($data['record_type'], $validTypes, true) ? $data['record_type'] : 'general';
        $data['severity'] = in_array($data['severity'], $validSeverities, true) ? $data['severity'] : 'low';
        $data['status'] = in_array($data['status'], $validStatuses, true) ? $data['status'] : 'active';

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("
            INSERT INTO medical_records (patient_id, record_type, title, description, diagnosed_date, severity, status, created_by)
            VALUES (:patient_id, :record_type, :title, :description, :diagnosed_date, :severity, :status, :created_by)
        ");
        $stmt->execute([
            ':patient_id' => $data['patient_id'],
            ':record_type' => $data['record_type'],
            ':title' => $data['title'],
            ':description' => $data['description'] ?: null,
            ':diagnosed_date' => $data['diagnosed_date'] ?: null,
            ':severity' => $data['severity'],
            ':status' => $data['status'],
            ':created_by' => $_SESSION['user_id'] ?? null,
        ]);
        $id = (int) $db->lastInsertId();

        audit_log('create', 'medical_records', 'medical_record', $id, 'Dossier médical créé : ' . $data['title']);

        $this->json(['success' => true, 'id' => $id, 'redirect' => '/medical-records']);
    }

    public function edit(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM medical_records WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $record = $stmt->fetch();
        if (!$record) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $patients = Patient::searchPatients('', 1, 200)['data'];
        $this->view('medical_records.edit', [
            'record' => $record,
            'patients' => $patients,
            'pageTitle' => 'Modifier le dossier médical',
        ]);
    }

    public function update(int $id): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM medical_records WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $record = $stmt->fetch();
        if (!$record) {
            $this->json(['error' => 'Dossier médical non trouvé'], 404);
        }

        $data = $this->input([
            'patient_id' => 'int',
            'record_type' => 'string',
            'title' => 'string',
            'description' => 'string',
            'diagnosed_date' => 'date',
            'severity' => 'string',
            'status' => 'string',
        ]);

        $errors = $this->validate($data, [
            'patient_id' => 'required',
            'title' => 'required|max:255',
        ]);
        if (!empty($data['patient_id']) && !Patient::find((int) $data['patient_id'])) {
            $errors['patient_id'] = 'Patient invalide.';
        }
        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $validTypes = ['allergy', 'chronic_disease', 'surgery', 'family_history', 'vaccination', 'general'];
        $validSeverities = ['low', 'medium', 'high', 'critical'];
        $validStatuses = ['active', 'resolved', 'ongoing', 'monitored'];

        $data['record_type'] = in_array($data['record_type'], $validTypes, true) ? $data['record_type'] : 'general';
        $data['severity'] = in_array($data['severity'], $validSeverities, true) ? $data['severity'] : 'low';
        $data['status'] = in_array($data['status'], $validStatuses, true) ? $data['status'] : 'active';

        $updateStmt = $db->prepare("
            UPDATE medical_records
            SET patient_id = :patient_id, record_type = :record_type, title = :title,
                description = :description, diagnosed_date = :diagnosed_date,
                severity = :severity, status = :status
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':patient_id' => $data['patient_id'],
            ':record_type' => $data['record_type'],
            ':title' => $data['title'],
            ':description' => $data['description'] ?: null,
            ':diagnosed_date' => $data['diagnosed_date'] ?: null,
            ':severity' => $data['severity'],
            ':status' => $data['status'],
            ':id' => $id,
        ]);

        audit_log('update', 'medical_records', 'medical_record', $id, 'Dossier médical modifié : ' . $data['title']);

        $this->json(['success' => true, 'redirect' => '/medical-records']);
    }

    public function delete(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM medical_records WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $record = $stmt->fetch();
        if (!$record) {
            $this->json(['error' => 'Dossier médical non trouvé'], 404);
        }

        $db->prepare("DELETE FROM medical_records WHERE id = :id")->execute([':id' => $id]);
        audit_log('delete', 'medical_records', 'medical_record', $id, 'Dossier médical supprimé : ' . $record['title']);

        $this->json(['success' => true]);
    }
}
