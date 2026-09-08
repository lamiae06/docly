<?php
namespace App\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;

class AppointmentController extends Controller {

    public function index(): void {
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d', strtotime('+7 days'));
        $doctorId = (int)($_GET['doctor_id'] ?? 0);
        $status = $_GET['status'] ?? '';
        $page = (int)($_GET['page'] ?? 1);

        $appointments = Appointment::filter($dateFrom, $dateTo, $doctorId, $status, $page, 20);
        $doctors = Doctor::allWithStats();

        $this->view('appointments.index', [
            'appointments' => $appointments,
            'doctors' => $doctors,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'doctorId' => $doctorId,
            'status' => $status,
            'pageTitle' => 'Rendez-vous',
        ]);
    }

    public function show(int $id): void {
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("
            SELECT a.*, 
                p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_code, p.phone as patient_phone,
                d.first_name as doctor_first_name, d.last_name as doctor_last_name, d.specialty
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $appointment = $stmt->fetch();

        if (!$appointment) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $this->view('appointments.show', [
            'appointment' => $appointment,
            'pageTitle' => 'Rendez-vous #' . $id,
        ]);
    }

    public function create(): void {
        $doctors = Doctor::allWithStats();
        $patients = Patient::searchPatients('', 1, 100)['data'];

        $this->view('appointments.create', [
            'doctors' => $doctors,
            'patients' => $patients,
            'pageTitle' => 'Nouveau rendez-vous',
        ]);
    }

    public function store(): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $data = $this->input([
            'patient_id' => 'int',
            'doctor_id' => 'int',
            'appointment_date' => 'date',
            'appointment_time' => 'string',
            'duration_minutes' => 'int',
            'type' => 'string',
            'reason' => 'string',
            'notes' => 'string',
        ]);

        $errors = $this->validate($data, [
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'appointment_date' => 'required',
            'appointment_time' => 'required',
        ]);

        if (empty($data['patient_id']) || !Patient::find((int) $data['patient_id'])) {
            $errors['patient_id'] = 'Patient invalide.';
        }
        if (empty($data['doctor_id']) || !Doctor::find((int) $data['doctor_id'])) {
            $errors['doctor_id'] = 'Médecin invalide.';
        }

        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $data['duration_minutes'] = $data['duration_minutes'] ?: 30;
        $data['type'] = $data['type'] ?: 'consultation';
        $data['status'] = 'scheduled';
        $data['created_by'] = $_SESSION['user_id'] ?? null;

        $id = Appointment::create($data);

        create_notification(
            (int) ($_SESSION['user_id'] ?? 0),
            'appointment',
            'Rendez-vous créé',
            'Nouveau rendez-vous le ' . format_date($data['appointment_date']) . ' à ' . format_time($data['appointment_time']),
            '/appointments/' . $id
        );

        audit_log('create', 'appointments', 'appointment', $id, 'Rendez-vous créé');

        $this->respondSuccess(['id' => $id], '/appointments/' . $id);
    }

    public function apiIndex(): void {
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $doctorId = (int)($_GET['doctor_id'] ?? 0);
        $status = $_GET['status'] ?? '';
        $page = (int)($_GET['page'] ?? 1);

        $appointments = Appointment::filter($dateFrom, $dateTo, $doctorId, $status, $page, 50);
        $this->json($appointments);
    }

    public function apiUpdateStatus(int $id): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $status = $_POST['status'] ?? '';
        $allowed = ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'];

        if (!in_array($status, $allowed)) {
            $this->json(['error' => 'Statut invalide'], 400);
        }

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("UPDATE appointments SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);

        audit_log('update', 'appointments', 'appointment', $id, 'Statut changé en: ' . $status);

        $this->json(['success' => true]);
    }

    public function edit(int $id): void {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }
        $doctors = Doctor::allWithStats();
        $patients = Patient::searchPatients('', 1, 200)['data'];

        $this->view('appointments.edit', [
            'appointment' => $appointment,
            'doctors' => $doctors,
            'patients' => $patients,
            'pageTitle' => 'Modifier rendez-vous',
        ]);
    }

    public function update(int $id): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $appointment = Appointment::find($id);
        if (!$appointment) {
            $this->json(['error' => 'Rendez-vous non trouvé'], 404);
        }

        $data = $this->input([
            'patient_id' => 'int',
            'doctor_id' => 'int',
            'appointment_date' => 'date',
            'appointment_time' => 'string',
            'duration_minutes' => 'int',
            'type' => 'string',
            'reason' => 'string',
            'notes' => 'string',
            'status' => 'string',
        ]);

        $errors = $this->validate($data, [
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'appointment_date' => 'required',
            'appointment_time' => 'required',
        ]);
        if (empty($data['patient_id']) || !Patient::find((int) $data['patient_id'])) {
            $errors['patient_id'] = 'Patient invalide.';
        }
        if (empty($data['doctor_id']) || !Doctor::find((int) $data['doctor_id'])) {
            $errors['doctor_id'] = 'Médecin invalide.';
        }
        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $allowedStatus = ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'];
        if (empty($data['status']) || !in_array($data['status'], $allowedStatus, true)) {
            $data['status'] = $appointment['status'];
        }
        $data['duration_minutes'] = $data['duration_minutes'] ?: 30;

        Appointment::update($id, $data);
        audit_log('update', 'appointments', 'appointment', $id, 'Rendez-vous modifié');

        $this->respondSuccess([], '/appointments/' . $id);
    }

    public function delete(int $id): void {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            $this->json(['error' => 'Rendez-vous non trouvé'], 404);
        }

        Appointment::delete($id);
        audit_log('delete', 'appointments', 'appointment', $id, 'Rendez-vous supprimé');

        $this->json(['success' => true]);
    }
}
