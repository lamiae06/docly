<?php
namespace App\Models;

class Appointment extends Model {
    protected static string $table = 'appointments';
    protected static array $fillable = [
        'patient_id', 'doctor_id', 'appointment_date', 'appointment_time',
        'duration_minutes', 'type', 'reason', 'notes', 'status', 'cancelled_reason', 'created_by'
    ];

    /**
     * Rendez-vous du jour avec détails
     */
    public static function today(): array {
        $stmt = self::db()->prepare("
            SELECT a.*, 
                p.first_name as patient_first_name, p.last_name as patient_last_name, 
                p.patient_code, p.phone as patient_phone,
                d.first_name as doctor_first_name, d.last_name as doctor_last_name, d.specialty
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.appointment_date = CURDATE() AND a.deleted_at IS NULL
            ORDER BY a.appointment_time
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Rendez-vous avec filtres
     */
    public static function filter(string $dateFrom = '', string $dateTo = '', int $doctorId = 0, string $status = '', int $page = 1, int $perPage = 20): array {
        $where = "a.deleted_at IS NULL";
        $params = [];

        if ($dateFrom) {
            $where .= " AND a.appointment_date >= :date_from";
            $params[':date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $where .= " AND a.appointment_date <= :date_to";
            $params[':date_to'] = $dateTo;
        }
        if ($doctorId > 0) {
            $where .= " AND a.doctor_id = :doctor_id";
            $params[':doctor_id'] = $doctorId;
        }
        if ($status) {
            $where .= " AND a.status = :status";
            $params[':status'] = $status;
        }

        $offset = ($page - 1) * $perPage;

        $sql = "SELECT a.*, p.first_name as patient_first_name, p.last_name as patient_last_name, 
                       p.patient_code, d.first_name as doctor_first_name, d.last_name as doctor_last_name, d.specialty
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN doctors d ON a.doctor_id = d.id
                WHERE $where
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
                LIMIT :limit OFFSET :offset";

        $stmt = self::db()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $countSql = "SELECT COUNT(*) as count FROM appointments a WHERE $where";
        $countStmt = self::db()->prepare($countSql);
        $countStmt->execute($params);
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
     * Compte les rendez-vous du jour
     */
    public static function countToday(): int {
        return self::count("appointment_date = CURDATE() AND deleted_at IS NULL");
    }

    /**
     * Compte les rendez-vous par statut aujourd'hui
     */
    public static function countTodayByStatus(string $status): int {
        return self::count("appointment_date = CURDATE() AND status = :status AND deleted_at IS NULL", [':status' => $status]);
    }

    /**
     * Rendez-vous à venir dans les 15 minutes
     */
    public static function upcomingSoon(): array {
        $stmt = self::db()->prepare("
            SELECT a.*, p.first_name as patient_first_name, p.last_name as patient_last_name,
                   d.first_name as doctor_first_name, d.last_name as doctor_last_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.appointment_date = CURDATE()
              AND a.status IN ('scheduled', 'confirmed')
              AND TIME(a.appointment_time) BETWEEN TIME(NOW()) AND TIME(DATE_ADD(NOW(), INTERVAL 15 MINUTE))
            ORDER BY a.appointment_time
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
