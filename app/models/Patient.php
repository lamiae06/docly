<?php
namespace App\Models;

class Patient extends Model {
    protected static string $table = 'patients';
    protected static array $fillable = [
        'patient_code', 'first_name', 'last_name', 'date_of_birth', 'gender',
        'phone', 'email', 'address', 'city', 'postal_code',
        'emergency_contact_name', 'emergency_contact_phone',
        'blood_type', 'allergies', 'chronic_diseases',
        'insurance_name', 'insurance_number', 'insurance_expiry',
        'avatar', 'notes', 'is_active'
    ];

    /**
     * Récupère les patients avec pagination et recherche
     */
    public static function searchPatients(string $query = '', int $page = 1, int $perPage = 20): array {
        $where = "deleted_at IS NULL";
        $params = [];

        if (!empty($query)) {
            $where .= " AND (first_name LIKE :q OR last_name LIKE :q OR patient_code LIKE :q OR phone LIKE :q OR email LIKE :q)";
            $params[':q'] = "%$query%";
        }

        return self::paginate($page, $perPage, 'created_at DESC', $where, $params);
    }

    /**
     * Récupère un patient avec toutes ses données liées
     */
    public static function findWithDetails(int $id): ?array {
        $patient = self::find($id);
        if (!$patient) return null;

        $patient['age'] = calculate_age($patient['date_of_birth']);

        // Dossiers médicaux
        $stmt = self::db()->prepare("SELECT * FROM medical_records WHERE patient_id = :id ORDER BY created_at DESC");
        $stmt->execute([':id' => $id]);
        $patient['medical_records'] = $stmt->fetchAll();

        // Rendez-vous
        $stmt = self::db()->prepare("
            SELECT a.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name, d.specialty
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.patient_id = :id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
            LIMIT 20
        ");
        $stmt->execute([':id' => $id]);
        $patient['appointments'] = $stmt->fetchAll();

        // Consultations
        $stmt = self::db()->prepare("
            SELECT c.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name
            FROM consultations c
            JOIN doctors d ON c.doctor_id = d.id
            WHERE c.patient_id = :id
            ORDER BY c.consultation_date DESC
            LIMIT 20
        ");
        $stmt->execute([':id' => $id]);
        $patient['consultations'] = $stmt->fetchAll();

        // Ordonnances
        $stmt = self::db()->prepare("
            SELECT p.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name
            FROM prescriptions p
            JOIN doctors d ON p.doctor_id = d.id
            WHERE p.patient_id = :id
            ORDER BY p.prescription_date DESC
            LIMIT 20
        ");
        $stmt->execute([':id' => $id]);
        $patient['prescriptions'] = $stmt->fetchAll();

        // Analyses
        $stmt = self::db()->prepare("
            SELECT l.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name
            FROM lab_results l
            LEFT JOIN doctors d ON l.doctor_id = d.id
            WHERE l.patient_id = :id
            ORDER BY l.test_date DESC
            LIMIT 20
        ");
        $stmt->execute([':id' => $id]);
        $patient['lab_results'] = $stmt->fetchAll();

        // Documents
        $stmt = self::db()->prepare("SELECT * FROM documents WHERE patient_id = :id ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([':id' => $id]);
        $patient['documents'] = $stmt->fetchAll();

        // Factures
        $stmt = self::db()->prepare("
            SELECT i.*, SUM(p.amount) as total_paid
            FROM invoices i
            LEFT JOIN payments p ON i.id = p.invoice_id
            WHERE i.patient_id = :id
            GROUP BY i.id
            ORDER BY i.issue_date DESC
            LIMIT 20
        ");
        $stmt->execute([':id' => $id]);
        $patient['invoices'] = $stmt->fetchAll();

        return $patient;
    }

    /**
     * Patients récents
     */
    public static function recent(int $limit = 10): array {
        $stmt = self::db()->prepare("
            SELECT p.*, 
                (SELECT MAX(appointment_date) FROM appointments WHERE patient_id = p.id) as last_appointment_date
            FROM patients p
            WHERE p.deleted_at IS NULL AND p.is_active = 1
            ORDER BY p.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Compte les patients actifs
     */
    public static function countActive(): int {
        return self::count("deleted_at IS NULL AND is_active = 1");
    }

    /**
     * Compte les nouveaux patients ce mois
     */
    public static function countNewThisMonth(): int {
        return self::count("deleted_at IS NULL AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    }
}
