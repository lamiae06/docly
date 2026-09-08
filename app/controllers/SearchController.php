<?php
namespace App\Controllers;

class SearchController extends Controller {

    /**
     * Recherche globale (patients, médecins, rendez-vous) utilisée par
     * la barre de recherche de l'en-tête.
     */
    public function apiSearch(): void {
        $query = trim($_GET['q'] ?? '');
        if (strlen($query) < 2) {
            $this->json(['data' => []]);
        }

        $db = $GLOBALS['db'];
        $like = "%$query%";
        $results = [];

        $stmt = $db->prepare("
            SELECT id, first_name, last_name, patient_code
            FROM patients
            WHERE deleted_at IS NULL
              AND (first_name LIKE :q1 OR last_name LIKE :q2 OR patient_code LIKE :q3 OR phone LIKE :q4)
            LIMIT 5
        ");
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like]);
        foreach ($stmt->fetchAll() as $p) {
            $results[] = [
                'title' => $p['first_name'] . ' ' . $p['last_name'],
                'type' => 'Patient (' . $p['patient_code'] . ')',
                'link' => '/patients/' . $p['id'],
                'icon' => 'fa-user',
            ];
        }

        $stmt = $db->prepare("
            SELECT id, first_name, last_name, specialty
            FROM doctors
            WHERE deleted_at IS NULL
              AND (first_name LIKE :q1 OR last_name LIKE :q2 OR specialty LIKE :q3)
            LIMIT 5
        ");
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
        foreach ($stmt->fetchAll() as $d) {
            $results[] = [
                'title' => 'Dr. ' . $d['first_name'] . ' ' . $d['last_name'],
                'type' => $d['specialty'],
                'link' => '/doctors/' . $d['id'],
                'icon' => 'fa-user-doctor',
            ];
        }

        $stmt = $db->prepare("
            SELECT a.id, a.appointment_date, a.appointment_time, p.first_name, p.last_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.deleted_at IS NULL AND (p.first_name LIKE :q1 OR p.last_name LIKE :q2)
            ORDER BY a.appointment_date DESC
            LIMIT 5
        ");
        $stmt->execute([':q1' => $like, ':q2' => $like]);
        foreach ($stmt->fetchAll() as $a) {
            $results[] = [
                'title' => 'RDV - ' . $a['first_name'] . ' ' . $a['last_name'],
                'type' => format_date($a['appointment_date']) . ' à ' . format_time($a['appointment_time']),
                'link' => '/appointments/' . $a['id'],
                'icon' => 'fa-calendar-check',
            ];
        }

        $this->json(['data' => $results]);
    }
}
