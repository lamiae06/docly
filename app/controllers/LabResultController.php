<?php
namespace App\Controllers;

use App\Models\Patient;
use App\Models\Doctor;

class LabResultController extends Controller {

    public function index(): void {
        $db = $GLOBALS['db'];
        $stmt = $db->query("
            SELECT l.*, p.first_name as patient_first_name, p.last_name as patient_last_name,
                   d.first_name as doctor_first_name, d.last_name as doctor_last_name
            FROM lab_results l
            JOIN patients p ON l.patient_id = p.id
            LEFT JOIN doctors d ON l.doctor_id = d.id
            ORDER BY l.test_date DESC
            LIMIT 50
        ");
        $this->view('lab_results.index', ['results' => $stmt->fetchAll(), 'pageTitle' => 'Analyses']);
    }

    public function create(): void {
        $patients = Patient::searchPatients('', 1, 200)['data'];
        $doctors = Doctor::allWithStats();

        $this->view('lab_results.create', [
            'patients' => $patients,
            'doctors' => $doctors,
            'pageTitle' => 'Nouvelle analyse',
        ]);
    }

    public function store(): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $data = $this->input([
            'patient_id' => 'int',
            'doctor_id' => 'int',
            'test_name' => 'string',
            'test_category' => 'string',
            'laboratory' => 'string',
            'test_date' => 'date',
            'result_value' => 'string',
            'unit' => 'string',
            'reference_range' => 'string',
            'interpretation' => 'string',
            'notes' => 'string',
        ]);

        $errors = $this->validate($data, [
            'patient_id' => 'required',
            'test_name' => 'required|max:255',
            'test_date' => 'required',
        ]);
        if (!empty($data['patient_id']) && !Patient::find((int) $data['patient_id'])) {
            $errors['patient_id'] = 'Patient invalide.';
        }
        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $data['doctor_id'] = $data['doctor_id'] ?: null;
        $data['status'] = !empty($data['result_value']) ? 'completed' : 'ordered';
        $data['created_by'] = $_SESSION['user_id'] ?? null;

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("
            INSERT INTO lab_results (patient_id, doctor_id, test_name, test_category, laboratory, test_date, result_value, unit, reference_range, interpretation, notes, status, created_by)
            VALUES (:patient_id, :doctor_id, :test_name, :test_category, :laboratory, :test_date, :result_value, :unit, :reference_range, :interpretation, :notes, :status, :created_by)
        ");
        $stmt->execute([
            ':patient_id' => $data['patient_id'],
            ':doctor_id' => $data['doctor_id'],
            ':test_name' => $data['test_name'],
            ':test_category' => $data['test_category'] ?: null,
            ':laboratory' => $data['laboratory'] ?: null,
            ':test_date' => $data['test_date'],
            ':result_value' => $data['result_value'] ?: null,
            ':unit' => $data['unit'] ?: null,
            ':reference_range' => $data['reference_range'] ?: null,
            ':interpretation' => $data['interpretation'] ?: null,
            ':notes' => $data['notes'] ?: null,
            ':status' => $data['status'],
            ':created_by' => $data['created_by'],
        ]);
        $id = (int) $db->lastInsertId();

        audit_log('create', 'lab_results', 'lab_result', $id, 'Analyse créée : ' . $data['test_name']);

        $this->json(['success' => true, 'id' => $id, 'redirect' => '/lab-results']);
    }

    public function apiIndex(): void {
        $db = $GLOBALS['db'];
        $stmt = $db->query("
            SELECT l.*, p.first_name as patient_first_name, p.last_name as patient_last_name
            FROM lab_results l
            JOIN patients p ON l.patient_id = p.id
            ORDER BY l.test_date DESC
            LIMIT 50
        ");
        $this->json(['data' => $stmt->fetchAll()]);
    }

    public function edit(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM lab_results WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        if (!$result) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $patients = Patient::searchPatients('', 1, 200)['data'];
        $doctors = Doctor::allWithStats();

        $this->view('lab_results.edit', [
            'result' => $result,
            'patients' => $patients,
            'doctors' => $doctors,
            'pageTitle' => "Modifier l'analyse",
        ]);
    }

    public function update(int $id): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM lab_results WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        if (!$result) {
            $this->json(['error' => 'Analyse non trouvée'], 404);
        }

        $data = $this->input([
            'patient_id' => 'int',
            'doctor_id' => 'int',
            'test_name' => 'string',
            'test_category' => 'string',
            'laboratory' => 'string',
            'test_date' => 'date',
            'result_value' => 'string',
            'unit' => 'string',
            'reference_range' => 'string',
            'interpretation' => 'string',
            'notes' => 'string',
        ]);

        $errors = $this->validate($data, [
            'patient_id' => 'required',
            'test_name' => 'required|max:255',
            'test_date' => 'required',
        ]);
        if (!empty($data['patient_id']) && !Patient::find((int) $data['patient_id'])) {
            $errors['patient_id'] = 'Patient invalide.';
        }
        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $data['doctor_id'] = $data['doctor_id'] ?: null;
        $data['status'] = !empty($data['result_value']) ? 'completed' : 'ordered';

        $updateStmt = $db->prepare("
            UPDATE lab_results
            SET patient_id = :patient_id, doctor_id = :doctor_id, test_name = :test_name,
                test_category = :test_category, laboratory = :laboratory, test_date = :test_date,
                result_value = :result_value, unit = :unit, reference_range = :reference_range,
                interpretation = :interpretation, notes = :notes, status = :status
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':patient_id' => $data['patient_id'],
            ':doctor_id' => $data['doctor_id'],
            ':test_name' => $data['test_name'],
            ':test_category' => $data['test_category'] ?: null,
            ':laboratory' => $data['laboratory'] ?: null,
            ':test_date' => $data['test_date'],
            ':result_value' => $data['result_value'] ?: null,
            ':unit' => $data['unit'] ?: null,
            ':reference_range' => $data['reference_range'] ?: null,
            ':interpretation' => $data['interpretation'] ?: null,
            ':notes' => $data['notes'] ?: null,
            ':status' => $data['status'],
            ':id' => $id,
        ]);

        audit_log('update', 'lab_results', 'lab_result', $id, 'Analyse modifiée : ' . $data['test_name']);

        $this->json(['success' => true, 'redirect' => '/lab-results']);
    }

    public function delete(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM lab_results WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        if (!$result) {
            $this->json(['error' => 'Analyse non trouvée'], 404);
        }

        $db->prepare("DELETE FROM lab_results WHERE id = :id")->execute([':id' => $id]);
        audit_log('delete', 'lab_results', 'lab_result', $id, 'Analyse supprimée : ' . $result['test_name']);

        $this->json(['success' => true]);
    }
}
