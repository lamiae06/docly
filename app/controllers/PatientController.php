<?php
namespace App\Controllers;

use App\Models\Patient;

class PatientController extends Controller {

    public function index(): void {
        $query = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $patients = Patient::searchPatients($query, $page, 20);

        $this->view('patients.index', [
            'patients' => $patients,
            'query' => $query,
            'pageTitle' => 'Patients',
        ]);
    }

    public function show(int $id): void {
        $patient = Patient::findWithDetails($id);
        if (!$patient) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $this->view('patients.show', [
            'patient' => $patient,
            'pageTitle' => $patient['first_name'] . ' ' . $patient['last_name'],
        ]);
    }

    public function create(): void {
        $this->view('patients.create', ['pageTitle' => 'Nouveau patient']);
    }

    public function store(): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $data = $this->input([
            'first_name' => 'string',
            'last_name' => 'string',
            'date_of_birth' => 'date',
            'gender' => 'string',
            'phone' => 'string',
            'email' => 'email',
            'address' => 'string',
            'city' => 'string',
            'postal_code' => 'string',
            'emergency_contact_name' => 'string',
            'emergency_contact_phone' => 'string',
            'blood_type' => 'string',
            'allergies' => 'string',
            'chronic_diseases' => 'string',
            'insurance_name' => 'string',
            'insurance_number' => 'string',
            'notes' => 'string',
        ]);

        $errors = $this->validate($data, [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'date_of_birth' => 'required',
            'gender' => 'required',
            'phone' => 'required|max:20',
        ]);

        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $data['patient_code'] = generate_patient_code();
        $data['is_active'] = 1;

        $id = Patient::create($data);
        audit_log('create', 'patients', 'patient', $id, 'Patient créé: ' . $data['first_name'] . ' ' . $data['last_name']);

        $this->json(['success' => true, 'id' => $id, 'redirect' => '/patients/' . $id]);
    }

    public function edit(int $id): void {
        $patient = Patient::find($id);
        if (!$patient) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }
        $this->view('patients.edit', ['patient' => $patient, 'pageTitle' => 'Modifier patient']);
    }

    public function update(int $id): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $patient = Patient::find($id);
        if (!$patient) {
            $this->json(['error' => 'Patient non trouvé'], 404);
        }

        $data = $this->input([
            'first_name' => 'string',
            'last_name' => 'string',
            'date_of_birth' => 'date',
            'gender' => 'string',
            'phone' => 'string',
            'email' => 'email',
            'address' => 'string',
            'city' => 'string',
            'postal_code' => 'string',
            'emergency_contact_name' => 'string',
            'emergency_contact_phone' => 'string',
            'blood_type' => 'string',
            'allergies' => 'string',
            'chronic_diseases' => 'string',
            'insurance_name' => 'string',
            'insurance_number' => 'string',
            'notes' => 'string',
            'is_active' => 'bool',
        ]);

        Patient::update($id, $data);
        audit_log('update', 'patients', 'patient', $id, 'Patient mis à jour');

        $this->json(['success' => true]);
    }

    public function delete(int $id): void {
        Patient::delete($id);
        audit_log('delete', 'patients', 'patient', $id, 'Patient supprimé');
        $this->json(['success' => true]);
    }

    // API Methods
    public function apiIndex(): void {
        $query = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $patients = Patient::searchPatients($query, $page, 20);
        $this->json($patients);
    }

    public function apiShow(int $id): void {
        $patient = Patient::findWithDetails($id);
        if (!$patient) {
            $this->json(['error' => 'Patient non trouvé'], 404);
        }
        $this->json($patient);
    }

    public function apiMedicalHistory(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM medical_records WHERE patient_id = :id ORDER BY created_at DESC");
        $stmt->execute([':id' => $id]);
        $this->json(['data' => $stmt->fetchAll()]);
    }

    public function apiAppointments(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("
            SELECT a.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name, d.specialty
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.patient_id = :id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ");
        $stmt->execute([':id' => $id]);
        $this->json(['data' => $stmt->fetchAll()]);
    }

    public function apiConsultations(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("
            SELECT c.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name
            FROM consultations c
            JOIN doctors d ON c.doctor_id = d.id
            WHERE c.patient_id = :id
            ORDER BY c.consultation_date DESC
        ");
        $stmt->execute([':id' => $id]);
        $this->json(['data' => $stmt->fetchAll()]);
    }

    public function apiPrescriptions(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("
            SELECT p.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name
            FROM prescriptions p
            JOIN doctors d ON p.doctor_id = d.id
            WHERE p.patient_id = :id
            ORDER BY p.prescription_date DESC
        ");
        $stmt->execute([':id' => $id]);
        $this->json(['data' => $stmt->fetchAll()]);
    }

    public function apiLabResults(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("
            SELECT l.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name
            FROM lab_results l
            LEFT JOIN doctors d ON l.doctor_id = d.id
            WHERE l.patient_id = :id
            ORDER BY l.test_date DESC
        ");
        $stmt->execute([':id' => $id]);
        $this->json(['data' => $stmt->fetchAll()]);
    }

    public function apiDocuments(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM documents WHERE patient_id = :id ORDER BY created_at DESC");
        $stmt->execute([':id' => $id]);
        $this->json(['data' => $stmt->fetchAll()]);
    }

    public function apiBilling(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("
            SELECT i.*, SUM(p.amount) as total_paid
            FROM invoices i
            LEFT JOIN payments p ON i.id = p.invoice_id
            WHERE i.patient_id = :id
            GROUP BY i.id
            ORDER BY i.issue_date DESC
        ");
        $stmt->execute([':id' => $id]);
        $this->json(['data' => $stmt->fetchAll()]);
    }
}
