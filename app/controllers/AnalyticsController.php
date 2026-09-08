<?php
namespace App\Controllers;
class AnalyticsController extends Controller {
    public function index(): void { $this->view('analytics.index', ['pageTitle' => 'Analytics']); }
    public function apiOverview(): void {
        $db = $GLOBALS['db'];
        $stats = [
            'total_patients' => $db->query("SELECT COUNT(*) as c FROM patients WHERE deleted_at IS NULL AND is_active = 1")->fetch()['c'],
            'total_appointments' => $db->query("SELECT COUNT(*) as c FROM appointments WHERE deleted_at IS NULL")->fetch()['c'],
            'total_consultations' => $db->query("SELECT COUNT(*) as c FROM consultations")->fetch()['c'],
            'total_revenue' => $db->query("SELECT COALESCE(SUM(paid_amount), 0) as c FROM invoices WHERE status IN ('paid', 'partially_paid')")->fetch()['c'],
        ];
        $this->json($stats);
    }
    public function apiCharts(): void {
        $db = $GLOBALS['db'];
        $apt = $db->query("SELECT DATE(appointment_date) as date, COUNT(*) as count FROM appointments WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) GROUP BY DATE(appointment_date) ORDER BY date")->fetchAll();
        $pat = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM patients WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month")->fetchAll();
        $rev = $db->query("SELECT DATE_FORMAT(issue_date, '%Y-%m') as month, COALESCE(SUM(paid_amount), 0) as revenue FROM invoices WHERE issue_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND status IN ('paid', 'partially_paid') GROUP BY DATE_FORMAT(issue_date, '%Y-%m') ORDER BY month")->fetchAll();
        $docs = $db->query("SELECT d.last_name, COUNT(*) as count FROM consultations c JOIN doctors d ON c.doctor_id = d.id WHERE DATE(c.consultation_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY c.doctor_id ORDER BY count DESC LIMIT 5")->fetchAll();
        $this->json(['appointments' => $apt, 'patients' => $pat, 'revenue' => $rev, 'doctors' => $docs, 'presence' => ['present' => 85, 'no_show' => 10, 'cancelled' => 5]]);
    }
}
