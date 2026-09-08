<?php
namespace App\Models;

class Doctor extends Model {
    protected static string $table = 'doctors';
    protected static array $fillable = [
        'user_id', 'first_name', 'last_name', 'specialty', 'sub_specialty',
        'license_number', 'phone', 'email', 'biography', 'consultation_fee', 'avatar', 'is_active'
    ];

    /**
     * Médecins actifs avec stats
     */
    public static function allWithStats(): array {
        $stmt = self::db()->query("
            SELECT d.*,
                (SELECT COUNT(*) FROM appointments WHERE doctor_id = d.id AND appointment_date = CURDATE()) as today_appointments,
                (SELECT COUNT(*) FROM consultations WHERE doctor_id = d.id AND DATE(consultation_date) = CURDATE()) as today_consultations,
                (SELECT COUNT(DISTINCT patient_id) FROM appointments WHERE doctor_id = d.id AND appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as monthly_patients
            FROM doctors d
            WHERE d.deleted_at IS NULL AND d.is_active = 1
            ORDER BY d.last_name
        ");
        return $stmt->fetchAll();
    }

    /**
     * Stats d'un médecin
     */
    public static function stats(int $id): array {
        $stmt = self::db()->prepare("
            SELECT 
                (SELECT COUNT(*) FROM appointments WHERE doctor_id = :id1) as total_appointments,
                (SELECT COUNT(*) FROM consultations WHERE doctor_id = :id2) as total_consultations,
                (SELECT COUNT(DISTINCT patient_id) FROM appointments WHERE doctor_id = :id3) as total_patients,
                (SELECT COUNT(*) FROM appointments WHERE doctor_id = :id4 AND appointment_date = CURDATE()) as today_appointments,
                (SELECT SUM(i.total_amount) FROM invoices i JOIN consultations c ON i.consultation_id = c.id WHERE c.doctor_id = :id5 AND i.status = 'paid') as total_revenue
        ");
        $stmt->execute([':id1' => $id, ':id2' => $id, ':id3' => $id, ':id4' => $id, ':id5' => $id]);
        return $stmt->fetch();
    }

    /**
     * Planning du médecin
     */
    public static function schedule(int $id): array {
        $stmt = self::db()->prepare("SELECT * FROM doctor_schedules WHERE doctor_id = :id ORDER BY day_of_week, start_time");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll();
    }
}
