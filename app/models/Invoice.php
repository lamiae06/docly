<?php
namespace App\Models;

class Invoice extends Model {
    protected static string $table = 'invoices';
    protected static array $fillable = [
        'invoice_number', 'patient_id', 'consultation_id', 'issue_date', 'due_date',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'paid_amount',
        'balance_due', 'status', 'notes', 'created_by'
    ];

    /**
     * Factures avec détails patient
     */
    public static function allWithPatients(int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        $stmt = self::db()->prepare("
            SELECT i.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_code
            FROM invoices i
            JOIN patients p ON i.patient_id = p.id
            ORDER BY i.issue_date DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $countStmt = self::db()->query("SELECT COUNT(*) as count FROM invoices");
        $total = (int) $countStmt->fetch()['count'];

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Revenus du mois
     */
    public static function monthlyRevenue(): float {
        $stmt = self::db()->query("
            SELECT COALESCE(SUM(paid_amount), 0) as revenue
            FROM invoices
            WHERE MONTH(issue_date) = MONTH(CURDATE()) AND YEAR(issue_date) = YEAR(CURDATE())
              AND status IN ('paid', 'partially_paid')
        ");
        return (float) $stmt->fetch()['revenue'];
    }

    /**
     * Factures en retard
     */
    public static function overdue(): array {
        $stmt = self::db()->query("
            SELECT i.*, p.first_name as patient_first_name, p.last_name as patient_last_name
            FROM invoices i
            JOIN patients p ON i.patient_id = p.id
            WHERE i.status IN ('pending', 'partially_paid') AND i.due_date < CURDATE()
            ORDER BY i.due_date ASC
            LIMIT 10
        ");
        return $stmt->fetchAll();
    }
}
