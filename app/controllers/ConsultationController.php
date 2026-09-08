<?php
namespace App\Controllers;

use App\Models\Patient;
use App\Models\Doctor;

class ConsultationController extends Controller {

    public function index(): void {
        $db = $GLOBALS['db'];
        $stmt = $db->query("
            SELECT c.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_code,
                   d.first_name as doctor_first_name, d.last_name as doctor_last_name
            FROM consultations c
            JOIN patients p ON c.patient_id = p.id
            JOIN doctors d ON c.doctor_id = d.id
            ORDER BY c.consultation_date DESC
            LIMIT 50
        ");
        $this->view('consultations.index', ['consultations' => $stmt->fetchAll(), 'pageTitle' => 'Consultations']);
    }

    public function create(): void {
        $patients = Patient::searchPatients('', 1, 200)['data'];
        $doctors = Doctor::allWithStats();

        $this->view('consultations.create', [
            'patients' => $patients,
            'doctors' => $doctors,
            'pageTitle' => 'Nouvelle consultation',
        ]);
    }

    public function store(): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $data = $this->input([
            'patient_id' => 'int',
            'doctor_id' => 'int',
            'appointment_id' => 'int',
            'chief_complaint' => 'string',
            'symptoms' => 'string',
            'diagnosis' => 'string',
            'diagnosis_icd10' => 'string',
            'treatment_plan' => 'string',
            'notes' => 'string',
            'follow_up_date' => 'date',
        ]);

        $errors = $this->validate($data, [
            'patient_id' => 'required',
            'doctor_id' => 'required',
        ]);
        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("
            INSERT INTO consultations (patient_id, doctor_id, appointment_id, consultation_date, chief_complaint, symptoms, diagnosis, diagnosis_icd10, treatment_plan, notes, follow_up_date, status)
            VALUES (:patient_id, :doctor_id, :appointment_id, NOW(), :chief_complaint, :symptoms, :diagnosis, :diagnosis_icd10, :treatment_plan, :notes, :follow_up_date, 'completed')
        ");
        $stmt->execute([
            ':patient_id' => $data['patient_id'],
            ':doctor_id' => $data['doctor_id'],
            ':appointment_id' => $data['appointment_id'] ?: null,
            ':chief_complaint' => $data['chief_complaint'] ?: null,
            ':symptoms' => $data['symptoms'] ?: null,
            ':diagnosis' => $data['diagnosis'] ?: null,
            ':diagnosis_icd10' => $data['diagnosis_icd10'] ?: null,
            ':treatment_plan' => $data['treatment_plan'] ?: null,
            ':notes' => $data['notes'] ?: null,
            ':follow_up_date' => $data['follow_up_date'] ?: null,
        ]);
        $id = (int) $db->lastInsertId();

        if (!empty($data['appointment_id'])) {
            $upd = $db->prepare("UPDATE appointments SET status = 'completed' WHERE id = :id");
            $upd->execute([':id' => $data['appointment_id']]);
        }

        audit_log('create', 'consultations', 'consultation', $id, 'Consultation créée');

        $this->json(['success' => true, 'id' => $id, 'redirect' => '/consultations']);
    }

    public function edit(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM consultations WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $consultation = $stmt->fetch();
        if (!$consultation) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $patients = Patient::searchPatients('', 1, 200)['data'];
        $doctors = Doctor::allWithStats();

        $this->view('consultations.edit', [
            'consultation' => $consultation,
            'patients' => $patients,
            'doctors' => $doctors,
            'pageTitle' => 'Modifier la consultation',
        ]);
    }

    public function update(int $id): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM consultations WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $consultation = $stmt->fetch();
        if (!$consultation) {
            $this->json(['error' => 'Consultation non trouvée'], 404);
        }

        $data = $this->input([
            'patient_id' => 'int',
            'doctor_id' => 'int',
            'chief_complaint' => 'string',
            'symptoms' => 'string',
            'diagnosis' => 'string',
            'diagnosis_icd10' => 'string',
            'treatment_plan' => 'string',
            'notes' => 'string',
            'follow_up_date' => 'date',
        ]);

        $errors = $this->validate($data, [
            'patient_id' => 'required',
            'doctor_id' => 'required',
        ]);
        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $updateStmt = $db->prepare("
            UPDATE consultations
            SET patient_id = :patient_id, doctor_id = :doctor_id, chief_complaint = :chief_complaint,
                symptoms = :symptoms, diagnosis = :diagnosis, diagnosis_icd10 = :diagnosis_icd10,
                treatment_plan = :treatment_plan, notes = :notes, follow_up_date = :follow_up_date
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':patient_id' => $data['patient_id'],
            ':doctor_id' => $data['doctor_id'],
            ':chief_complaint' => $data['chief_complaint'] ?: null,
            ':symptoms' => $data['symptoms'] ?: null,
            ':diagnosis' => $data['diagnosis'] ?: null,
            ':diagnosis_icd10' => $data['diagnosis_icd10'] ?: null,
            ':treatment_plan' => $data['treatment_plan'] ?: null,
            ':notes' => $data['notes'] ?: null,
            ':follow_up_date' => $data['follow_up_date'] ?: null,
            ':id' => $id,
        ]);

        audit_log('update', 'consultations', 'consultation', $id, 'Consultation modifiée');

        $this->json(['success' => true, 'redirect' => '/consultations']);
    }

    public function delete(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM consultations WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $consultation = $stmt->fetch();
        if (!$consultation) {
            $this->json(['error' => 'Consultation non trouvée'], 404);
        }

        $db->prepare("DELETE FROM consultations WHERE id = :id")->execute([':id' => $id]);
        audit_log('delete', 'consultations', 'consultation', $id, 'Consultation supprimée');

        $this->json(['success' => true]);
    }
}
