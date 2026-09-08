<?php
/**
 * Docly - Routes Web
 *
 * La clé optionnelle "permission" est vérifiée par le contrôleur frontal
 * (public/index.php) via can() : sans elle, n'importe quel utilisateur
 * connecté pouvait accéder à n'importe quelle URL en la tapant directement,
 * même si le lien correspondant était masqué dans le menu. Cette vérification
 * complète (et ne remplace pas) le contrôle "admin uniquement" déjà présent
 * dans AuthMiddleware pour /settings/users, /settings/roles et /settings/audit-logs.
 */

return [
    // Auth (cabinet)
    '' => ['controller' => 'AuthController', 'method' => 'showLogin'],
    'login' => ['controller' => 'AuthController', 'method' => 'showLogin'],
    'logout' => ['controller' => 'AuthController', 'method' => 'logout', 'middleware' => 'AuthMiddleware'],

    // Inscription d'un nouveau cabinet (multi-tenant : provisionne une base dédiée)
    'register-cabinet' => ['controller' => 'CabinetController', 'method' => 'showRegister'],

    // Espace super-admin de la plateforme (gestion des cabinets)
    'platform/login' => ['controller' => 'PlatformController', 'method' => 'showLogin'],
    'platform/logout' => ['controller' => 'PlatformController', 'method' => 'logout', 'middleware' => 'PlatformAuthMiddleware'],
    'platform/dashboard' => ['controller' => 'PlatformController', 'method' => 'dashboard', 'middleware' => 'PlatformAuthMiddleware'],

    // Dashboard
    'dashboard' => ['controller' => 'DashboardController', 'method' => 'index', 'middleware' => 'AuthMiddleware', 'permission' => 'dashboard.view'],

    // Patients
    'patients' => ['controller' => 'PatientController', 'method' => 'index', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.view'],
    'patients/create' => ['controller' => 'PatientController', 'method' => 'create', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.manage'],
    'patients/store' => ['controller' => 'PatientController', 'method' => 'store', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.manage'],
    'patients/{id}' => ['controller' => 'PatientController', 'method' => 'show', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.view'],
    'patients/{id}/edit' => ['controller' => 'PatientController', 'method' => 'edit', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.manage'],
    'patients/{id}/update' => ['controller' => 'PatientController', 'method' => 'update', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.manage'],
    'patients/{id}/delete' => ['controller' => 'PatientController', 'method' => 'delete', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.manage'],

    // Appointments
    'appointments' => ['controller' => 'AppointmentController', 'method' => 'index', 'middleware' => 'AuthMiddleware', 'permission' => 'appointments.view'],
    'appointments/create' => ['controller' => 'AppointmentController', 'method' => 'create', 'middleware' => 'AuthMiddleware', 'permission' => 'appointments.manage'],
    'appointments/store' => ['controller' => 'AppointmentController', 'method' => 'store', 'middleware' => 'AuthMiddleware', 'permission' => 'appointments.manage'],
    'appointments/{id}' => ['controller' => 'AppointmentController', 'method' => 'show', 'middleware' => 'AuthMiddleware', 'permission' => 'appointments.view'],

    // Doctors
    'doctors' => ['controller' => 'DoctorController', 'method' => 'index', 'middleware' => 'AuthMiddleware', 'permission' => 'doctors.view'],
    'doctors/create' => ['controller' => 'DoctorController', 'method' => 'create', 'middleware' => 'AuthMiddleware', 'permission' => 'doctors.manage'],
    'doctors/store' => ['controller' => 'DoctorController', 'method' => 'store', 'middleware' => 'AuthMiddleware', 'permission' => 'doctors.manage'],
    'doctors/{id}' => ['controller' => 'DoctorController', 'method' => 'show', 'middleware' => 'AuthMiddleware', 'permission' => 'doctors.view'],

    // Medical Records
    'medical-records' => ['controller' => 'MedicalRecordController', 'method' => 'index', 'middleware' => 'AuthMiddleware', 'permission' => 'medical_records.view'],
    'medical-records/create' => ['controller' => 'MedicalRecordController', 'method' => 'create', 'middleware' => 'AuthMiddleware', 'permission' => 'medical_records.manage'],
    'medical-records/store' => ['controller' => 'MedicalRecordController', 'method' => 'store', 'middleware' => 'AuthMiddleware', 'permission' => 'medical_records.manage'],

    // Consultations
    'consultations' => ['controller' => 'ConsultationController', 'method' => 'index', 'middleware' => 'AuthMiddleware', 'permission' => 'consultations.view'],
    'consultations/create' => ['controller' => 'ConsultationController', 'method' => 'create', 'middleware' => 'AuthMiddleware', 'permission' => 'consultations.manage'],
    'consultations/store' => ['controller' => 'ConsultationController', 'method' => 'store', 'middleware' => 'AuthMiddleware', 'permission' => 'consultations.manage'],

    // Prescriptions
    'prescriptions' => ['controller' => 'PrescriptionController', 'method' => 'index', 'middleware' => 'AuthMiddleware', 'permission' => 'prescriptions.view'],
    'prescriptions/create' => ['controller' => 'PrescriptionController', 'method' => 'create', 'middleware' => 'AuthMiddleware', 'permission' => 'prescriptions.manage'],
    'prescriptions/store' => ['controller' => 'PrescriptionController', 'method' => 'store', 'middleware' => 'AuthMiddleware', 'permission' => 'prescriptions.manage'],

    // Medications
    'medications' => ['controller' => 'MedicationController', 'method' => 'index', 'middleware' => 'AuthMiddleware', 'permission' => 'medications.view'],
    'medications/create' => ['controller' => 'MedicationController', 'method' => 'create', 'middleware' => 'AuthMiddleware', 'permission' => 'medications.manage'],
    'medications/store' => ['controller' => 'MedicationController', 'method' => 'store', 'middleware' => 'AuthMiddleware', 'permission' => 'medications.manage'],

    // Lab Results
    'lab-results' => ['controller' => 'LabResultController', 'method' => 'index', 'middleware' => 'AuthMiddleware', 'permission' => 'lab_results.view'],
    'lab-results/create' => ['controller' => 'LabResultController', 'method' => 'create', 'middleware' => 'AuthMiddleware', 'permission' => 'lab_results.manage'],
    'lab-results/store' => ['controller' => 'LabResultController', 'method' => 'store', 'middleware' => 'AuthMiddleware', 'permission' => 'lab_results.manage'],

    // Billing
    'billing' => ['controller' => 'BillingController', 'method' => 'index', 'middleware' => 'AuthMiddleware', 'permission' => 'billing.view'],
    'billing/create' => ['controller' => 'BillingController', 'method' => 'create', 'middleware' => 'AuthMiddleware', 'permission' => 'billing.manage'],
    'billing/store' => ['controller' => 'BillingController', 'method' => 'store', 'middleware' => 'AuthMiddleware', 'permission' => 'billing.manage'],
    'invoices/{id}' => ['controller' => 'BillingController', 'method' => 'showInvoice', 'middleware' => 'AuthMiddleware', 'permission' => 'billing.view'],

    // Documents
    'documents' => ['controller' => 'DocumentController', 'method' => 'index', 'middleware' => 'AuthMiddleware', 'permission' => 'documents.view'],
    'documents/create' => ['controller' => 'DocumentController', 'method' => 'create', 'middleware' => 'AuthMiddleware', 'permission' => 'documents.manage'],
    'documents/store' => ['controller' => 'DocumentController', 'method' => 'store', 'middleware' => 'AuthMiddleware', 'permission' => 'documents.manage'],

    // Notifications (chaque utilisateur voit les siennes, pas de permission dédiée)
    'notifications' => ['controller' => 'NotificationController', 'method' => 'index', 'middleware' => 'AuthMiddleware'],

    // Analytics
    'analytics' => ['controller' => 'AnalyticsController', 'method' => 'index', 'middleware' => 'AuthMiddleware', 'permission' => 'analytics.view'],

    // Settings
    'settings' => ['controller' => 'SettingsController', 'method' => 'index', 'middleware' => 'AuthMiddleware'],
    'settings/profile' => ['controller' => 'SettingsController', 'method' => 'profile', 'middleware' => 'AuthMiddleware'],
    'settings/security' => ['controller' => 'SettingsController', 'method' => 'security', 'middleware' => 'AuthMiddleware'],
    'settings/clinic' => ['controller' => 'SettingsController', 'method' => 'clinic', 'middleware' => 'AuthMiddleware', 'permission' => 'settings.manage'],
    'settings/users' => ['controller' => 'SettingsController', 'method' => 'users', 'middleware' => 'AuthMiddleware', 'permission' => 'settings.manage'],
    'settings/roles' => ['controller' => 'SettingsController', 'method' => 'roles', 'middleware' => 'AuthMiddleware', 'permission' => 'settings.manage'],
    'settings/roles/{id}' => ['controller' => 'SettingsController', 'method' => 'roleShow', 'middleware' => 'AuthMiddleware', 'permission' => 'settings.manage'],
    'settings/audit-logs' => ['controller' => 'SettingsController', 'method' => 'auditLogs', 'middleware' => 'AuthMiddleware', 'permission' => 'settings.manage'],
];
