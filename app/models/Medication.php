<?php
namespace App\Models;

class Medication extends Model {
    protected static string $table = 'medications';
    protected static array $fillable = [
        'name', 'generic_name', 'category', 'form', 'dosage_strength',
        'manufacturer', 'description', 'side_effects', 'contraindications',
        'stock_quantity', 'stock_alert_level', 'unit_price', 'expiry_date',
        'batch_number', 'is_active'
    ];

    /**
     * Alertes de stock et d'expiration
     */
    public static function alerts(): array {
        $stmt = self::db()->query("
            SELECT *,
                CASE 
                    WHEN stock_quantity <= stock_alert_level THEN 'low_stock'
                    WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH) AND expiry_date > CURDATE() THEN 'expiring_soon'
                    WHEN expiry_date <= CURDATE() THEN 'expired'
                    ELSE 'ok'
                END as alert_type
            FROM medications
            WHERE is_active = 1
              AND (stock_quantity <= stock_alert_level 
                   OR expiry_date <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH))
            ORDER BY 
                CASE 
                    WHEN expiry_date <= CURDATE() THEN 1
                    WHEN stock_quantity <= stock_alert_level THEN 2
                    ELSE 3
                END,
                expiry_date ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Compte les alertes
     */
    public static function alertsCount(): int {
        return self::count("is_active = 1 AND (stock_quantity <= stock_alert_level OR expiry_date <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH))");
    }
}
