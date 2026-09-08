<?php
namespace App\Controllers;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Medication;
use App\Models\Notification;

class DashboardController extends Controller {

    /**
     * Page du tableau de bord
     */
    public function index(): void {
        $stats = $this->getStats();
        $todayAppointments = Appointment::today();
        $recentPatients = Patient::recent(8);
        $alerts = $this->getAlerts();
        $notifications = Notification::unread($_SESSION['user_id'] ?? 0, 5);

        $this->view('dashboard.index', [
            'stats' => $stats,
            'todayAppointments' => $todayAppointments,
            'recentPatients' => $recentPatients,
            'alerts' => $alerts,
            'notifications' => $notifications,
            'pageTitle' => 'Tableau de bord',
        ]);
    }

    /**
     * Stats API
     */
    public function apiStats(): void {
        $this->json($this->getStats());
    }

    /**
     * Rendez-vous du jour API
     */
    public function apiTodayAppointments(): void {
        $appointments = Appointment::today();
        $this->json(['data' => $appointments]);
    }

    /**
     * Patients récents API
     */
    public function apiRecentPatients(): void {
        $patients = Patient::recent(10);
        $this->json(['data' => $patients]);
    }

    /**
     * Alertes API
     */
    public function apiAlerts(): void {
        $this->json(['data' => $this->getAlerts()]);
    }

    /**
     * Activités récentes API
     */
    public function apiActivities(): void {
        $db = $GLOBALS['db'];
        $stmt = $db->query("
            SELECT * FROM audit_logs 
            ORDER BY created_at DESC 
            LIMIT 20
        ");
        $this->json(['data' => $stmt->fetchAll()]);
    }

    /**
     * Données des graphiques API
     */
    public function apiChartData(): void {
        $db = $GLOBALS['db'];

        // Rendez-vous par jour (7 derniers jours)
        $stmt = $db->query("
            SELECT DATE(appointment_date) as date, COUNT(*) as count
            FROM appointments
            WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(appointment_date)
            ORDER BY date
        ");
        $appointmentsByDay = $stmt->fetchAll();

        // Nouveaux patients par mois (6 derniers mois)
        $stmt = $db->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
            FROM patients
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month
        ");
        $patientsByMonth = $stmt->fetchAll();

        // Revenus par mois (6 derniers mois)
        $stmt = $db->query("
            SELECT DATE_FORMAT(issue_date, '%Y-%m') as month, SUM(paid_amount) as revenue
            FROM invoices
            WHERE issue_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
              AND status IN ('paid', 'partially_paid')
            GROUP BY DATE_FORMAT(issue_date, '%Y-%m')
            ORDER BY month
        ");
        $revenueByMonth = $stmt->fetchAll();

        // Consultations par médecin
        $stmt = $db->query("
            SELECT d.first_name, d.last_name, COUNT(*) as count
            FROM consultations c
            JOIN doctors d ON c.doctor_id = d.id
            WHERE DATE(c.consultation_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY c.doctor_id
            ORDER BY count DESC
        ");
        $consultationsByDoctor = $stmt->fetchAll();

        $this->json([
            'appointments_by_day' => $appointmentsByDay,
            'patients_by_month' => $patientsByMonth,
            'revenue_by_month' => $revenueByMonth,
            'consultations_by_doctor' => $consultationsByDoctor,
        ]);
    }

    private function getStats(): array {
        return [
            'total_patients' => Patient::countActive(),
            'new_patients_this_month' => Patient::countNewThisMonth(),
            'today_appointments' => Appointment::countToday(),
            'today_appointments_confirmed' => Appointment::countTodayByStatus('confirmed'),
            'today_consultations' => Appointment::countTodayByStatus('completed'),
            'monthly_revenue' => Invoice::monthlyRevenue(),
            'pending_invoices' => Invoice::count("status IN ('pending', 'partially_paid')"),
            'medication_alerts' => Medication::alertsCount(),
            'active_doctors' => Doctor::count("is_active = 1 AND deleted_at IS NULL"),
        ];
    }

    private function getAlerts(): array {
        $alerts = [];

        // Rendez-vous dans 15 minutes
        $upcoming = Appointment::upcomingSoon();
        foreach ($upcoming as $apt) {
            $alerts[] = [
                'type' => 'appointment',
                'severity' => 'info',
                'title' => 'Rendez-vous imminent',
                'message' => $apt['patient_first_name'] . ' ' . $apt['patient_last_name'] . ' avec Dr. ' . $apt['doctor_last_name'] . ' à ' . format_time($apt['appointment_time']),
                'link' => '/appointments/' . $apt['id'],
                'time' => $apt['appointment_time'],
            ];
        }

        // Alertes médicaments
        $medAlerts = Medication::alerts();
        foreach (array_slice($medAlerts, 0, 3) as $med) {
            if ($med['alert_type'] === 'expired') {
                $alerts[] = [
                    'type' => 'medication',
                    'severity' => 'critical',
                    'title' => 'Médicament expiré',
                    'message' => $med['name'] . ' (Lot: ' . $med['batch_number'] . ') a expiré le ' . format_date($med['expiry_date']),
                    'link' => '/medications',
                ];
            } elseif ($med['alert_type'] === 'low_stock') {
                $alerts[] = [
                    'type' => 'medication',
                    'severity' => 'warning',
                    'title' => 'Stock faible',
                    'message' => $med['name'] . ' : ' . $med['stock_quantity'] . ' unités restantes (seuil: ' . $med['stock_alert_level'] . ')',
                    'link' => '/medications',
                ];
            } elseif ($med['alert_type'] === 'expiring_soon') {
                $alerts[] = [
                    'type' => 'medication',
                    'severity' => 'warning',
                    'title' => 'Expiration proche',
                    'message' => $med['name'] . ' expire le ' . format_date($med['expiry_date']),
                    'link' => '/medications',
                ];
            }
        }

        // Factures en retard
        $overdue = Invoice::overdue();
        foreach (array_slice($overdue, 0, 2) as $inv) {
            $alerts[] = [
                'type' => 'billing',
                'severity' => 'warning',
                'title' => 'Paiement en retard',
                'message' => 'Facture ' . $inv['invoice_number'] . ' - ' . $inv['patient_first_name'] . ' ' . $inv['patient_last_name'] . ' : ' . format_money($inv['balance_due']) . ' en retard',
                'link' => '/billing',
            ];
        }

        return $alerts;
    }
}
