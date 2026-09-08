<?php
namespace App\Controllers;

use App\Models\Doctor;

class DoctorController extends Controller {

    public function index(): void {
        $doctors = Doctor::allWithStats();
        $this->view('doctors.index', [
            'doctors' => $doctors,
            'pageTitle' => 'Médecins',
        ]);
    }

    public function show(int $id): void {
        $doctor = Doctor::find($id);
        if (!$doctor) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $stats = Doctor::stats($id);
        $schedule = Doctor::schedule($id);

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("
            SELECT a.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_code
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.doctor_id = :id AND a.deleted_at IS NULL
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
            LIMIT 15
        ");
        $stmt->execute([':id' => $id]);
        $recentAppointments = $stmt->fetchAll();

        $this->view('doctors.show', [
            'doctor' => $doctor,
            'stats' => $stats,
            'schedule' => $schedule,
            'recentAppointments' => $recentAppointments,
            'pageTitle' => 'Dr. ' . $doctor['first_name'] . ' ' . $doctor['last_name'],
        ]);
    }

    public function apiIndex(): void {
        $this->json(['data' => Doctor::allWithStats()]);
    }

    public function create(): void {
        $this->view('doctors.create', ['pageTitle' => 'Nouveau médecin']);
    }

    public function store(): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $data = $this->input([
            'first_name' => 'string',
            'last_name' => 'string',
            'specialty' => 'string',
            'sub_specialty' => 'string',
            'license_number' => 'string',
            'phone' => 'string',
            'email' => 'email',
            'biography' => 'string',
            'consultation_fee' => 'float',
        ]);

        $errors = $this->validate($data, [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'specialty' => 'required|max:100',
            'license_number' => 'required|max:100',
        ]);
        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $data['consultation_fee'] = $data['consultation_fee'] ?: 0;
        $data['is_active'] = 1;

        try {
            $id = Doctor::create($data);
        } catch (\PDOException $e) {
            $this->json(['error' => 'Ce numéro de licence est déjà utilisé.'], 422);
        }

        audit_log('create', 'doctors', 'doctor', $id, 'Médecin créé : Dr. ' . $data['first_name'] . ' ' . $data['last_name']);

        $this->json(['success' => true, 'id' => $id, 'redirect' => '/doctors/' . $id]);
    }

    public function apiStats(int $id): void {
        $this->json(Doctor::stats($id));
    }

    public function edit(int $id): void {
        $doctor = Doctor::find($id);
        if (!$doctor) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }
        $this->view('doctors.edit', ['doctor' => $doctor, 'pageTitle' => 'Modifier médecin']);
    }

    public function update(int $id): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $doctor = Doctor::find($id);
        if (!$doctor) {
            $this->json(['error' => 'Médecin non trouvé'], 404);
        }

        $data = $this->input([
            'first_name' => 'string',
            'last_name' => 'string',
            'specialty' => 'string',
            'sub_specialty' => 'string',
            'license_number' => 'string',
            'phone' => 'string',
            'email' => 'email',
            'biography' => 'string',
            'consultation_fee' => 'float',
            'is_active' => 'bool',
        ]);

        $errors = $this->validate($data, [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'specialty' => 'required|max:100',
            'license_number' => 'required|max:100',
        ]);
        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        try {
            Doctor::update($id, $data);
        } catch (\PDOException $e) {
            $this->json(['error' => 'Ce numéro de licence est déjà utilisé.'], 422);
        }

        audit_log('update', 'doctors', 'doctor', $id, 'Médecin modifié : Dr. ' . $data['first_name'] . ' ' . $data['last_name']);

        $this->json(['success' => true, 'redirect' => '/doctors/' . $id]);
    }

    public function delete(int $id): void {
        $doctor = Doctor::find($id);
        if (!$doctor) {
            $this->json(['error' => 'Médecin non trouvé'], 404);
        }

        Doctor::delete($id);
        audit_log('delete', 'doctors', 'doctor', $id, 'Médecin supprimé : Dr. ' . $doctor['first_name'] . ' ' . $doctor['last_name']);

        $this->json(['success' => true]);
    }
}
