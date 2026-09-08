<?php
/**
 * Docly - Routes API
 */

return [
    // Auth API
    'api/auth/login' => ['controller' => 'AuthController', 'method' => 'apiLogin'],
    'api/auth/logout' => ['controller' => 'AuthController', 'method' => 'logout', 'middleware' => 'AuthMiddleware'],
    'api/auth/me' => ['controller' => 'AuthController', 'method' => 'me', 'middleware' => 'AuthMiddleware'],

    // Cabinet registration API
    'api/cabinets/register' => ['controller' => 'CabinetController', 'method' => 'apiRegister'],

    // Platform (super-admin) API
    'api/platform/login' => ['controller' => 'PlatformController', 'method' => 'apiLogin'],
    'api/platform/cabinets/{id}/toggle' => ['controller' => 'PlatformController', 'method' => 'apiToggleCabinet', 'middleware' => 'PlatformAuthMiddleware'],

    // Dashboard API
    'api/dashboard/stats' => ['controller' => 'DashboardController', 'method' => 'apiStats', 'middleware' => 'AuthMiddleware'],
    'api/dashboard/today-appointments' => ['controller' => 'DashboardController', 'method' => 'apiTodayAppointments', 'middleware' => 'AuthMiddleware'],
    'api/dashboard/recent-patients' => ['controller' => 'DashboardController', 'method' => 'apiRecentPatients', 'middleware' => 'AuthMiddleware'],
    'api/dashboard/alerts' => ['controller' => 'DashboardController', 'method' => 'apiAlerts', 'middleware' => 'AuthMiddleware'],
    'api/dashboard/activities' => ['controller' => 'DashboardController', 'method' => 'apiActivities', 'middleware' => 'AuthMiddleware'],
    'api/dashboard/chart-data' => ['controller' => 'DashboardController', 'method' => 'apiChartData', 'middleware' => 'AuthMiddleware'],

    // Patients API
    'api/patients' => ['controller' => 'PatientController', 'method' => 'apiIndex', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.view'],
    'api/patients/{id}' => ['controller' => 'PatientController', 'method' => 'apiShow', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.view'],
    'api/patients/{id}/medical-history' => ['controller' => 'PatientController', 'method' => 'apiMedicalHistory', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.view'],
    'api/patients/{id}/appointments' => ['controller' => 'PatientController', 'method' => 'apiAppointments', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.view'],
    'api/patients/{id}/consultations' => ['controller' => 'PatientController', 'method' => 'apiConsultations', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.view'],
    'api/patients/{id}/prescriptions' => ['controller' => 'PatientController', 'method' => 'apiPrescriptions', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.view'],
    'api/patients/{id}/lab-results' => ['controller' => 'PatientController', 'method' => 'apiLabResults', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.view'],
    'api/patients/{id}/documents' => ['controller' => 'PatientController', 'method' => 'apiDocuments', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.view'],
    'api/patients/{id}/billing' => ['controller' => 'PatientController', 'method' => 'apiBilling', 'middleware' => 'AuthMiddleware', 'permission' => 'patients.view'],

    // Appointments API
    'api/appointments' => ['controller' => 'AppointmentController', 'method' => 'apiIndex', 'middleware' => 'AuthMiddleware', 'permission' => 'appointments.view'],
    'api/appointments/{id}/status' => ['controller' => 'AppointmentController', 'method' => 'apiUpdateStatus', 'middleware' => 'AuthMiddleware', 'permission' => 'appointments.manage'],

    // Doctors API
    'api/doctors' => ['controller' => 'DoctorController', 'method' => 'apiIndex', 'middleware' => 'AuthMiddleware', 'permission' => 'doctors.view'],
    'api/doctors/{id}/stats' => ['controller' => 'DoctorController', 'method' => 'apiStats', 'middleware' => 'AuthMiddleware', 'permission' => 'doctors.view'],

    // Medications API
    'api/medications' => ['controller' => 'MedicationController', 'method' => 'apiIndex', 'middleware' => 'AuthMiddleware', 'permission' => 'medications.view'],
    'api/medications/alerts' => ['controller' => 'MedicationController', 'method' => 'apiAlerts', 'middleware' => 'AuthMiddleware', 'permission' => 'medications.view'],
    'api/medications/{id}/stock' => ['controller' => 'MedicationController', 'method' => 'apiAdjustStock', 'middleware' => 'AuthMiddleware', 'permission' => 'medications.manage'],

    // Lab Results API
    'api/lab-results' => ['controller' => 'LabResultController', 'method' => 'apiIndex', 'middleware' => 'AuthMiddleware', 'permission' => 'lab_results.view'],

    // Billing API
    'api/billing/stats' => ['controller' => 'BillingController', 'method' => 'apiStats', 'middleware' => 'AuthMiddleware', 'permission' => 'billing.view'],
    'api/billing/{id}/pay' => ['controller' => 'BillingController', 'method' => 'apiPay', 'middleware' => 'AuthMiddleware', 'permission' => 'billing.manage'],

    // Notifications API (propres à chaque utilisateur, pas de permission dédiée)
    'api/notifications' => ['controller' => 'NotificationController', 'method' => 'apiIndex', 'middleware' => 'AuthMiddleware'],
    'api/notifications/{id}/read' => ['controller' => 'NotificationController', 'method' => 'apiMarkRead', 'middleware' => 'AuthMiddleware'],
    'api/notifications/mark-all-read' => ['controller' => 'NotificationController', 'method' => 'apiMarkAllRead', 'middleware' => 'AuthMiddleware'],
    'api/notifications/unread-count' => ['controller' => 'NotificationController', 'method' => 'apiUnreadCount', 'middleware' => 'AuthMiddleware'],

    // Search API
    'api/search' => ['controller' => 'SearchController', 'method' => 'apiSearch', 'middleware' => 'AuthMiddleware'],

    // Analytics API
    'api/analytics/overview' => ['controller' => 'AnalyticsController', 'method' => 'apiOverview', 'middleware' => 'AuthMiddleware', 'permission' => 'analytics.view'],
    'api/analytics/charts' => ['controller' => 'AnalyticsController', 'method' => 'apiCharts', 'middleware' => 'AuthMiddleware', 'permission' => 'analytics.view'],

    // Settings API (la gestion des utilisateurs est en plus protégée par
    // requireAdmin() dans le contrôleur, ceinture et bretelles)
    'api/settings/profile' => ['controller' => 'SettingsController', 'method' => 'apiUpdateProfile', 'middleware' => 'AuthMiddleware'],
    'api/settings/security' => ['controller' => 'SettingsController', 'method' => 'apiUpdatePassword', 'middleware' => 'AuthMiddleware'],
    'api/settings/clinic' => ['controller' => 'SettingsController', 'method' => 'apiUpdateClinic', 'middleware' => 'AuthMiddleware', 'permission' => 'settings.manage'],
    'api/settings/users' => ['controller' => 'SettingsController', 'method' => 'apiCreateUser', 'middleware' => 'AuthMiddleware', 'permission' => 'settings.manage'],
    'api/settings/users/{id}/toggle' => ['controller' => 'SettingsController', 'method' => 'apiToggleUser', 'middleware' => 'AuthMiddleware', 'permission' => 'settings.manage'],
    'api/settings/language' => ['controller' => 'SettingsController', 'method' => 'apiSetLanguage', 'middleware' => 'AuthMiddleware'],
    // Réservé aux administrateurs (voir SettingsController::requireAdmin, appelé dans la méthode)
    'api/settings/roles/{id}/permissions' => ['controller' => 'SettingsController', 'method' => 'apiUpdateRolePermissions', 'middleware' => 'AuthMiddleware', 'permission' => 'settings.manage'],

    // ---------------------------------------------------------------
    // Modifier / Supprimer génériques, module par module.
    // Ajoutés pour couvrir les modules qui n'avaient jusqu'ici que
    // "créer" (store) : on pouvait ajouter une ligne mais jamais la
    // corriger ni la retirer sans passer par la base de données.
    // ---------------------------------------------------------------
    'doctors/{id}/edit' => ['controller' => 'DoctorController', 'method' => 'edit', 'middleware' => 'AuthMiddleware', 'permission' => 'doctors.manage'],
    'doctors/{id}/update' => ['controller' => 'DoctorController', 'method' => 'update', 'middleware' => 'AuthMiddleware', 'permission' => 'doctors.manage'],
    'doctors/{id}/delete' => ['controller' => 'DoctorController', 'method' => 'delete', 'middleware' => 'AuthMiddleware', 'permission' => 'doctors.manage'],

    'appointments/{id}/edit' => ['controller' => 'AppointmentController', 'method' => 'edit', 'middleware' => 'AuthMiddleware', 'permission' => 'appointments.manage'],
    'appointments/{id}/update' => ['controller' => 'AppointmentController', 'method' => 'update', 'middleware' => 'AuthMiddleware', 'permission' => 'appointments.manage'],
    'appointments/{id}/delete' => ['controller' => 'AppointmentController', 'method' => 'delete', 'middleware' => 'AuthMiddleware', 'permission' => 'appointments.manage'],

    'medical-records/{id}/edit' => ['controller' => 'MedicalRecordController', 'method' => 'edit', 'middleware' => 'AuthMiddleware', 'permission' => 'medical_records.manage'],
    'medical-records/{id}/update' => ['controller' => 'MedicalRecordController', 'method' => 'update', 'middleware' => 'AuthMiddleware', 'permission' => 'medical_records.manage'],
    'medical-records/{id}/delete' => ['controller' => 'MedicalRecordController', 'method' => 'delete', 'middleware' => 'AuthMiddleware', 'permission' => 'medical_records.manage'],

    'consultations/{id}/edit' => ['controller' => 'ConsultationController', 'method' => 'edit', 'middleware' => 'AuthMiddleware', 'permission' => 'consultations.manage'],
    'consultations/{id}/update' => ['controller' => 'ConsultationController', 'method' => 'update', 'middleware' => 'AuthMiddleware', 'permission' => 'consultations.manage'],
    'consultations/{id}/delete' => ['controller' => 'ConsultationController', 'method' => 'delete', 'middleware' => 'AuthMiddleware', 'permission' => 'consultations.manage'],

    'prescriptions/{id}/edit' => ['controller' => 'PrescriptionController', 'method' => 'edit', 'middleware' => 'AuthMiddleware', 'permission' => 'prescriptions.manage'],
    'prescriptions/{id}/update' => ['controller' => 'PrescriptionController', 'method' => 'update', 'middleware' => 'AuthMiddleware', 'permission' => 'prescriptions.manage'],
    'prescriptions/{id}/delete' => ['controller' => 'PrescriptionController', 'method' => 'delete', 'middleware' => 'AuthMiddleware', 'permission' => 'prescriptions.manage'],

    'medications/{id}/edit' => ['controller' => 'MedicationController', 'method' => 'edit', 'middleware' => 'AuthMiddleware', 'permission' => 'medications.manage'],
    'medications/{id}/update' => ['controller' => 'MedicationController', 'method' => 'update', 'middleware' => 'AuthMiddleware', 'permission' => 'medications.manage'],
    'medications/{id}/delete' => ['controller' => 'MedicationController', 'method' => 'delete', 'middleware' => 'AuthMiddleware', 'permission' => 'medications.manage'],

    'lab-results/{id}/edit' => ['controller' => 'LabResultController', 'method' => 'edit', 'middleware' => 'AuthMiddleware', 'permission' => 'lab_results.manage'],
    'lab-results/{id}/update' => ['controller' => 'LabResultController', 'method' => 'update', 'middleware' => 'AuthMiddleware', 'permission' => 'lab_results.manage'],
    'lab-results/{id}/delete' => ['controller' => 'LabResultController', 'method' => 'delete', 'middleware' => 'AuthMiddleware', 'permission' => 'lab_results.manage'],

    'documents/{id}/edit' => ['controller' => 'DocumentController', 'method' => 'edit', 'middleware' => 'AuthMiddleware', 'permission' => 'documents.manage'],
    'documents/{id}/update' => ['controller' => 'DocumentController', 'method' => 'update', 'middleware' => 'AuthMiddleware', 'permission' => 'documents.manage'],
    'documents/{id}/delete' => ['controller' => 'DocumentController', 'method' => 'delete', 'middleware' => 'AuthMiddleware', 'permission' => 'documents.manage'],

    'billing/{id}/edit' => ['controller' => 'BillingController', 'method' => 'edit', 'middleware' => 'AuthMiddleware', 'permission' => 'billing.manage'],
    'billing/{id}/update' => ['controller' => 'BillingController', 'method' => 'update', 'middleware' => 'AuthMiddleware', 'permission' => 'billing.manage'],
    'billing/{id}/delete' => ['controller' => 'BillingController', 'method' => 'delete', 'middleware' => 'AuthMiddleware', 'permission' => 'billing.manage'],
];
