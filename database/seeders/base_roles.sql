-- ============================================================
-- Docly - Rôles et permissions de base
-- Exécuté automatiquement sur la base d'un NOUVEAU cabinet lors
-- de sa création (contrairement à demo_data.sql, ce fichier ne
-- contient aucune donnée médicale factice).
-- ============================================================

INSERT IGNORE INTO `permissions` (`name`, `slug`, `description`) VALUES
('Voir le tableau de bord', 'dashboard.view', 'Accès au tableau de bord'),
('Gérer les patients', 'patients.manage', 'Créer, modifier, supprimer des patients'),
('Voir les patients', 'patients.view', 'Voir la liste des patients'),
('Gérer les rendez-vous', 'appointments.manage', 'Gérer les rendez-vous'),
('Voir les rendez-vous', 'appointments.view', 'Voir les rendez-vous'),
('Gérer les consultations', 'consultations.manage', 'Gérer les consultations'),
('Voir les consultations', 'consultations.view', 'Voir les consultations'),
('Gérer les médecins', 'doctors.manage', 'Gérer les médecins'),
('Voir les médecins', 'doctors.view', 'Voir les médecins'),
('Gérer les dossiers médicaux', 'medical_records.manage', 'Gérer les dossiers médicaux'),
('Voir les dossiers médicaux', 'medical_records.view', 'Voir les dossiers médicaux'),
('Gérer les ordonnances', 'prescriptions.manage', 'Gérer les ordonnances'),
('Voir les ordonnances', 'prescriptions.view', 'Voir les ordonnances'),
('Gérer les médicaments', 'medications.manage', 'Gérer les médicaments'),
('Voir les médicaments', 'medications.view', 'Voir les médicaments'),
('Gérer les analyses', 'lab_results.manage', 'Gérer les résultats de laboratoire'),
('Voir les analyses', 'lab_results.view', 'Voir les résultats de laboratoire'),
('Gérer la facturation', 'billing.manage', 'Gérer la facturation'),
('Voir la facturation', 'billing.view', 'Voir la facturation'),
('Gérer les documents', 'documents.manage', 'Gérer les documents'),
('Voir les documents', 'documents.view', 'Voir les documents'),
('Gérer les utilisateurs', 'users.manage', 'Gérer les utilisateurs'),
('Voir les utilisateurs', 'users.view', 'Voir les utilisateurs'),
('Gérer les paramètres', 'settings.manage', 'Gérer les paramètres'),
('Voir les analytics', 'analytics.view', 'Voir les analytics'),
('Voir les logs d''audit', 'audit.view', 'Voir les logs d''audit'),
('Gérer les notifications', 'notifications.manage', 'Gérer les notifications');

INSERT IGNORE INTO `roles` (`name`, `slug`, `description`, `color`) VALUES
('Administrateur', 'admin', 'Accès complet à l''application', '#ef4444'),
('Médecin', 'doctor', 'Médecin traitant', '#3b82f6'),
('Réceptionniste', 'receptionist', 'Gestion des rendez-vous et patients', '#10b981'),
('Infirmier(e)', 'nurse', 'Assistance médicale', '#f59e0b'),
('Comptable', 'accountant', 'Gestion financière', '#8b5cf6');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT (SELECT id FROM roles WHERE slug = 'admin'), id FROM permissions;

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT (SELECT id FROM roles WHERE slug = 'doctor'), id FROM permissions
WHERE slug IN (
    'dashboard.view', 'patients.view', 'appointments.manage', 'appointments.view',
    'consultations.manage', 'consultations.view', 'doctors.manage', 'doctors.view',
    'medical_records.manage', 'medical_records.view', 'prescriptions.manage', 'prescriptions.view',
    'medications.manage', 'medications.view', 'lab_results.manage', 'lab_results.view',
    'billing.manage', 'billing.view', 'documents.manage', 'documents.view',
    'settings.manage', 'analytics.view'
);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT (SELECT id FROM roles WHERE slug = 'receptionist'), id FROM permissions
WHERE slug IN (
    'dashboard.view', 'patients.manage', 'patients.view', 'appointments.manage', 'appointments.view',
    'doctors.manage', 'doctors.view', 'documents.manage', 'documents.view', 'notifications.manage'
);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT (SELECT id FROM roles WHERE slug = 'nurse'), id FROM permissions
WHERE slug IN (
    'dashboard.view', 'patients.view', 'appointments.view', 'consultations.view', 'doctors.view',
    'medical_records.manage', 'medical_records.view', 'medications.manage', 'medications.view',
    'lab_results.manage', 'lab_results.view', 'notifications.manage'
);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT (SELECT id FROM roles WHERE slug = 'accountant'), id FROM permissions
WHERE slug IN (
    'dashboard.view', 'patients.view', 'appointments.view', 'doctors.view',
    'billing.manage', 'billing.view', 'documents.manage', 'documents.view',
    'settings.manage', 'notifications.manage'
);

-- Paramètres de clinique par défaut (le nom réel est mis à jour à l'inscription)
INSERT IGNORE INTO `clinic_settings` (`key`, `value`, `type`, `group`) VALUES
('clinic_name', 'Mon Cabinet', 'string', 'general'),
('clinic_address', '', 'string', 'general'),
('clinic_phone', '', 'string', 'general'),
('clinic_email', '', 'string', 'general'),
('clinic_website', '', 'string', 'general'),
('tax_rate', '20', 'integer', 'billing'),
('currency', 'EUR', 'string', 'billing'),
('appointment_default_duration', '30', 'integer', 'appointments'),
('working_hours_start', '08:00', 'string', 'appointments'),
('working_hours_end', '18:00', 'string', 'appointments'),
('theme_default', 'system', 'string', 'appearance');
