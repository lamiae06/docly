-- ============================================================
-- Docly - Données de Démonstration
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. PERMISSIONS
-- ============================================================

INSERT INTO `permissions` (`name`, `slug`, `description`) VALUES
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

-- ============================================================
-- 2. ROLES
-- ============================================================

INSERT INTO `roles` (`name`, `slug`, `description`, `color`) VALUES
('Administrateur', 'admin', 'Accès complet à l''application', '#ef4444'),
('Médecin', 'doctor', 'Médecin traitant', '#3b82f6'),
('Réceptionniste', 'receptionist', 'Gestion des rendez-vous et patients', '#10b981'),
('Infirmier(e)', 'nurse', 'Assistance médicale', '#f59e0b'),
('Comptable', 'accountant', 'Gestion financière', '#8b5cf6');

-- ============================================================
-- 3. ROLE_PERMISSIONS
-- ------------------------------------------------------------
-- NOTE : la version précédente référençait les permissions par
-- des identifiants numériques codés en dur (ex: (2, 26)), ce qui
-- est très fragile : le moindre ajout/suppression de ligne dans
-- la table `permissions` décale tous les IDs et associe les
-- mauvaises permissions aux mauvais rôles. On utilise ici des
-- sous-requêtes basées sur les "slug", stables et lisibles.
-- ============================================================

-- Administrateur : toutes les permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT (SELECT id FROM roles WHERE slug = 'admin'), id FROM permissions;

-- Médecin
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT (SELECT id FROM roles WHERE slug = 'doctor'), id FROM permissions
WHERE slug IN (
    'dashboard.view', 'patients.view', 'appointments.manage', 'appointments.view',
    'consultations.manage', 'consultations.view', 'doctors.manage', 'doctors.view',
    'medical_records.manage', 'medical_records.view', 'prescriptions.manage', 'prescriptions.view',
    'medications.manage', 'medications.view', 'lab_results.manage', 'lab_results.view',
    'billing.manage', 'billing.view', 'documents.manage', 'documents.view',
    'settings.manage', 'analytics.view'
);

-- Réceptionniste
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT (SELECT id FROM roles WHERE slug = 'receptionist'), id FROM permissions
WHERE slug IN (
    'dashboard.view', 'patients.manage', 'patients.view', 'appointments.manage', 'appointments.view',
    'doctors.manage', 'doctors.view', 'documents.manage', 'documents.view', 'notifications.manage'
);

-- Infirmier(e)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT (SELECT id FROM roles WHERE slug = 'nurse'), id FROM permissions
WHERE slug IN (
    'dashboard.view', 'patients.view', 'appointments.view', 'consultations.view', 'doctors.view',
    'medical_records.manage', 'medical_records.view', 'medications.manage', 'medications.view',
    'lab_results.manage', 'lab_results.view', 'notifications.manage'
);

-- Comptable
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT (SELECT id FROM roles WHERE slug = 'accountant'), id FROM permissions
WHERE slug IN (
    'dashboard.view', 'patients.view', 'appointments.view', 'doctors.view',
    'billing.manage', 'billing.view', 'documents.manage', 'documents.view',
    'settings.manage', 'notifications.manage'
);

-- ============================================================
-- 4. USERS
-- ============================================================

INSERT INTO `users` (`role_id`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `is_active`, `last_login`, `theme`) VALUES
(1, 'admin@docly.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alexandre', 'Martin', '0612345678', 1, NOW(), 'system'),
(2, 'dr.dupont@docly.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marie', 'Dupont', '0623456789', 1, NOW(), 'system'),
(2, 'dr.bernard@docly.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pierre', 'Bernard', '0634567890', 1, NOW(), 'system'),
(2, 'dr.petit@docly.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sophie', 'Petit', '0645678901', 1, NOW(), 'system'),
(2, 'dr.robert@docly.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jean', 'Robert', '0656789012', 1, NOW(), 'system'),
(2, 'dr.richard@docly.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Claire', 'Richard', '0667890123', 1, NOW(), 'system'),
(3, 'reception@docly.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lucas', 'Moreau', '0678901234', 1, NOW(), 'system'),
(4, 'nurse@docly.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emma', 'Laurent', '0689012345', 1, NOW(), 'system'),
(5, 'accounting@docly.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Thomas', 'Simon', '0690123456', 1, NOW(), 'system');

-- ============================================================
-- 5. CLINIC SETTINGS
-- ============================================================

INSERT INTO `clinic_settings` (`key`, `value`, `type`, `group`) VALUES
('clinic_name', 'Docly Clinic', 'string', 'general'),
('clinic_address', '123 Avenue de la Santé, 75001 Paris', 'string', 'general'),
('clinic_phone', '01 23 45 67 89', 'string', 'general'),
('clinic_email', 'contact@docly.local', 'string', 'general'),
('clinic_website', 'www.docly.local', 'string', 'general'),
('tax_rate', '20', 'integer', 'billing'),
('currency', 'EUR', 'string', 'billing'),
('appointment_default_duration', '30', 'integer', 'appointments'),
('working_hours_start', '08:00', 'string', 'appointments'),
('working_hours_end', '18:00', 'string', 'appointments'),
('theme_default', 'system', 'string', 'appearance');

-- ============================================================
-- 6. DOCTORS
-- ============================================================

INSERT INTO `doctors` (`user_id`, `first_name`, `last_name`, `specialty`, `sub_specialty`, `license_number`, `phone`, `email`, `biography`, `consultation_fee`, `is_active`) VALUES
(2, 'Marie', 'Dupont', 'Médecine Générale', 'Médecine préventive', 'MG-2015-0042', '0623456789', 'dr.dupont@docly.local', 'Dr. Dupont est médecin généraliste avec 8 ans d''expérience. Elle se spécialise dans la médecine préventive et le suivi des patients chroniques.', 50.00, 1),
(3, 'Pierre', 'Bernard', 'Cardiologie', 'Rythmologie', 'CAR-2012-0018', '0634567890', 'dr.bernard@docly.local', 'Cardiologue spécialisé en rythmologie interventionnelle. Plus de 10 ans d''expérience dans le traitement des arythmies.', 80.00, 1),
(4, 'Sophie', 'Petit', 'Pédiatrie', 'Néonatologie', 'PED-2018-0025', '0645678901', 'dr.petit@docly.local', 'Pédiatre passionnée par la santé infantile. Spécialisée en néonatologie et développement de l''enfant.', 55.00, 1),
(5, 'Jean', 'Robert', 'Dermatologie', 'Dermatologie esthétique', 'DER-2014-0031', '0656789012', 'dr.robert@docly.local', 'Dermatologue reconnu pour son expertise en dermatologie médicale et esthétique.', 70.00, 1),
(6, 'Claire', 'Richard', 'Gynécologie', 'Gynécologie obstétrique', 'GYN-2016-0029', '0667890123', 'dr.richard@docly.local', 'Gynécologue-obstétricienne dédiée à la santé des femmes à tous les âges de la vie.', 65.00, 1);

-- ============================================================
-- 7. DOCTOR SCHEDULES
-- ============================================================

INSERT INTO `doctor_schedules` (`doctor_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `max_appointments`) VALUES
(1, 1, '08:00:00', '12:00:00', 1, 8), (1, 1, '14:00:00', '18:00:00', 1, 8),
(1, 2, '08:00:00', '12:00:00', 1, 8), (1, 2, '14:00:00', '18:00:00', 1, 8),
(1, 3, '08:00:00', '12:00:00', 1, 8), (1, 3, '14:00:00', '18:00:00', 1, 8),
(1, 4, '08:00:00', '12:00:00', 1, 8), (1, 4, '14:00:00', '18:00:00', 1, 8),
(1, 5, '08:00:00', '12:00:00', 1, 8),
(2, 1, '09:00:00', '13:00:00', 1, 6), (2, 1, '15:00:00', '17:00:00', 1, 4),
(2, 3, '09:00:00', '13:00:00', 1, 6), (2, 3, '15:00:00', '17:00:00', 1, 4),
(2, 5, '09:00:00', '13:00:00', 1, 6),
(3, 1, '08:30:00', '12:30:00', 1, 8), (3, 2, '08:30:00', '12:30:00', 1, 8),
(3, 3, '08:30:00', '12:30:00', 1, 8), (3, 4, '08:30:00', '12:30:00', 1, 8),
(3, 5, '08:30:00', '12:30:00', 1, 8),
(4, 2, '09:00:00', '13:00:00', 1, 8), (4, 2, '15:00:00', '18:00:00', 1, 6),
(4, 4, '09:00:00', '13:00:00', 1, 8), (4, 4, '15:00:00', '18:00:00', 1, 6),
(5, 1, '08:00:00', '12:00:00', 1, 6), (5, 1, '14:00:00', '17:00:00', 1, 5),
(5, 3, '08:00:00', '12:00:00', 1, 6), (5, 3, '14:00:00', '17:00:00', 1, 5),
(5, 5, '08:00:00', '12:00:00', 1, 6);

-- ============================================================
-- 8. PATIENTS
-- ============================================================

INSERT INTO `patients` (`patient_code`, `first_name`, `last_name`, `date_of_birth`, `gender`, `phone`, `email`, `address`, `city`, `postal_code`, `emergency_contact_name`, `emergency_contact_phone`, `blood_type`, `allergies`, `chronic_diseases`, `insurance_name`, `insurance_number`, `is_active`) VALUES
('P-2024-0001', 'Jean', 'Martin', '1985-03-15', 'male', '0611111111', 'jean.martin@email.com', '15 Rue de la Paix', 'Paris', '75002', 'Marie Martin', '0699999999', 'A+', 'Pénicilline', 'Hypertension', 'Mutuelle Santé', 'MS-12345678', 1),
('P-2024-0002', 'Sophie', 'Bernard', '1990-07-22', 'female', '0622222222', 'sophie.bernard@email.com', '28 Avenue Victor Hugo', 'Paris', '75016', 'Paul Bernard', '0688888888', 'O-', 'Aucune', 'Aucune', 'Harmonie Mutuelle', 'HM-87654321', 1),
('P-2024-0003', 'Lucas', 'Petit', '1978-11-08', 'male', '0633333333', 'lucas.petit@email.com', '5 Boulevard Haussmann', 'Paris', '75009', 'Anne Petit', '0677777777', 'B+', 'Ibuprofène', 'Diabète type 2', 'MGEN', 'MGEN-11223344', 1),
('P-2024-0004', 'Emma', 'Robert', '1995-01-30', 'female', '0644444444', 'emma.robert@email.com', '42 Rue du Commerce', 'Paris', '75015', 'Marc Robert', '0666666666', 'AB+', 'Latex', 'Aucune', 'LMDE', 'LMDE-55667788', 1),
('P-2024-0005', 'Thomas', 'Richard', '1965-05-12', 'male', '0655555555', 'thomas.richard@email.com', '8 Place de la République', 'Paris', '75011', 'Isabelle Richard', '0655555555', 'A-', 'Aucune', 'Asthme, Hypercholestérolémie', 'Mutuelle Générale', 'MG-99887766', 1),
('P-2024-0006', 'Camille', 'Durand', '2002-09-18', 'female', '0666666666', 'camille.durand@email.com', '12 Rue de Rivoli', 'Paris', '75004', 'Philippe Durand', '0644444444', 'O+', 'Arachides', 'Aucune', 'April', 'APR-33445566', 1),
('P-2024-0007', 'Hugo', 'Moreau', '1988-12-03', 'male', '0677777777', 'hugo.moreau@email.com', '3 Rue de la Pompe', 'Paris', '75016', 'Julie Moreau', '0633333333', 'B-', 'Sulfamides', 'Aucune', 'Mutuelle Santé', 'MS-77665544', 1),
('P-2024-0008', 'Léa', 'Simon', '1972-04-25', 'female', '0688888888', 'lea.simon@email.com', '19 Rue de Passy', 'Paris', '75016', 'David Simon', '0622222222', 'AB-', 'Aucune', 'Hypothyroïdie', 'Harmonie Mutuelle', 'HM-22334455', 1),
('P-2024-0009', 'Nathan', 'Laurent', '1998-08-14', 'male', '0699999999', 'nathan.laurent@email.com', '7 Rue de la Convention', 'Paris', '75015', 'Sarah Laurent', '0611111111', 'A+', 'Aucune', 'Aucune', 'MGEN', 'MGEN-66778899', 1),
('P-2024-0010', 'Chloé', 'Michel', '1980-02-28', 'female', '0610101010', 'chloe.michel@email.com', '25 Rue de Sèvres', 'Paris', '75007', 'Nicolas Michel', '0690909090', 'O-', 'Pénicilline, Aspirine', 'Migraine chronique', 'LMDE', 'LMDE-11223399', 1),
('P-2024-0011', 'Louis', 'Garcia', '1955-06-10', 'male', '0620202020', 'louis.garcia@email.com', '14 Rue de la Boétie', 'Paris', '75008', 'Françoise Garcia', '0680808080', 'B+', 'Aucune', 'Insuffisance cardiaque, Diabète', 'Mutuelle Générale', 'MG-44556677', 1),
('P-2024-0012', 'Julie', 'Roux', '1992-10-05', 'female', '0630303030', 'julie.roux@email.com', '33 Rue de Vaugirard', 'Paris', '75006', 'Antoine Roux', '0670707070', 'A+', 'Lactose', 'Aucune', 'April', 'APR-88990011', 1),
('P-2024-0013', 'Maxime', 'Leroy', '1983-12-20', 'male', '0640404040', 'maxime.leroy@email.com', '6 Rue de la Roquette', 'Paris', '75011', 'Valérie Leroy', '0660606060', 'AB+', 'Aucune', 'Hypertension', 'Mutuelle Santé', 'MS-22334455', 1),
('P-2024-0014', 'Sarah', 'Fournier', '2000-03-08', 'female', '0650505050', 'sarah.fournier@email.com', '11 Rue de la Fontaine', 'Paris', '75009', 'Benjamin Fournier', '0650505050', 'O+', 'Aucune', 'Aucune', 'Harmonie Mutuelle', 'HM-33445566', 1),
('P-2024-0015', 'Alexandre', 'Girard', '1970-07-17', 'male', '0660606060', 'alexandre.girard@email.com', '22 Rue de la Gaité', 'Paris', '75014', 'Catherine Girard', '0640404040', 'B-', 'Aucune', 'BPCO', 'MGEN', 'MGEN-44556677', 1);

-- ============================================================
-- 9. MEDICAL RECORDS
-- ============================================================

INSERT INTO `medical_records` (`patient_id`, `record_type`, `title`, `description`, `diagnosed_date`, `severity`, `status`) VALUES
(1, 'allergy', 'Allergie à la pénicilline', 'Réaction cutanée sévère observée en 2010. Contre-indication absolue.', '2010-05-15', 'high', 'active'),
(1, 'chronic_disease', 'Hypertension artérielle', 'HTA diagnostiquée en 2018. Traitement par IEC en cours.', '2018-03-20', 'medium', 'ongoing'),
(3, 'allergy', 'Allergie à l''ibuprofène', 'Urticaire et œdème après prise. Éviter tous les AINS.', '2015-08-10', 'high', 'active'),
(3, 'chronic_disease', 'Diabète type 2', 'Diabète type 2 diagnostiqué en 2019. HbA1c cible < 7%.', '2019-01-15', 'high', 'ongoing'),
(4, 'allergy', 'Allergie au latex', 'Contact dermatite au latex. Utiliser des gants sans latex.', '2012-11-22', 'medium', 'active'),
(5, 'chronic_disease', 'Asthme', 'Asthme allergique contrôlé. Ventoline en secours.', '2005-04-10', 'medium', 'ongoing'),
(5, 'chronic_disease', 'Hypercholestérolémie', 'LDL élevé. Traitement par statines.', '2015-09-05', 'low', 'ongoing'),
(6, 'allergy', 'Allergie aux arachides', 'Anaphylaxie potentielle. Épipen prescrit.', '2008-02-14', 'critical', 'active'),
(7, 'allergy', 'Allergie aux sulfamides', 'Éruption cutanée généralisée. Éviter les sulfamides.', '2011-06-30', 'high', 'active'),
(8, 'chronic_disease', 'Hypothyroïdie', 'Hypothyroïdie primaire. Lévothyrox 75µg/jour.', '2010-12-01', 'low', 'ongoing'),
(10, 'allergy', 'Allergie à la pénicilline', 'Choc anaphylactique en 2005. Contre-indication absolue.', '2005-07-20', 'critical', 'active'),
(10, 'allergy', 'Allergie à l''aspirine', 'Syndrome de Widal après prise d''aspirine.', '2008-03-15', 'high', 'active'),
(10, 'chronic_disease', 'Migraine chronique', 'Migraine avec aura. Traitement préventif par bêtabloquants.', '2012-01-10', 'medium', 'ongoing'),
(11, 'chronic_disease', 'Insuffisance cardiaque', 'IC NYHA II. FEVG 45%. Traitement optimisé.', '2020-06-18', 'high', 'ongoing'),
(11, 'chronic_disease', 'Diabète type 2', 'Diabète compliqué. Néphropathie diabétique stade 2.', '2018-04-22', 'high', 'ongoing'),
(12, 'allergy', 'Intolérance au lactose', 'Intolérance au lactose confirmée par test H2.', '2015-09-10', 'low', 'active'),
(13, 'chronic_disease', 'Hypertension artérielle', 'HTA légère. Hygiène de vie + monitoring.', '2021-02-14', 'low', 'monitored'),
(15, 'chronic_disease', 'BPCO', 'BPCO modérée. FEV1 65%. Arrêt tabac recommandé.', '2019-11-05', 'high', 'ongoing'),
(15, 'surgery', 'Pontage coronarien', 'Triple pontage en 2021. Suivi cardiologique régulier.', '2021-08-15', 'high', 'resolved');

-- ============================================================
-- 10. MEDICATIONS
-- ============================================================

INSERT INTO `medications` (`name`, `generic_name`, `category`, `form`, `dosage_strength`, `manufacturer`, `description`, `stock_quantity`, `stock_alert_level`, `unit_price`, `expiry_date`, `batch_number`, `is_active`) VALUES
('Doliprane', 'Paracétamol', 'Analgésique', 'tablet', '500mg', 'Sanofi', 'Antalgique et antipyrétique', 500, 50, 2.50, '2026-12-31', 'BTL-2024-001', 1),
('Doliprane', 'Paracétamol', 'Analgésique', 'syrup', '2.4%', 'Sanofi', 'Sirop antalgique pour enfants', 200, 20, 4.20, '2026-06-30', 'BTL-2024-002', 1),
('Amoxicilline', 'Amoxicilline', 'Antibiotique', 'capsule', '1g', 'GSK', 'Antibiotique pénicilline à large spectre', 300, 30, 8.90, '2026-03-15', 'GSK-2024-101', 1),
('Augmentin', 'Amoxicilline/Acide clavulanique', 'Antibiotique', 'tablet', '1g/125mg', 'GSK', 'Antibiotique à large spectre', 250, 25, 12.50, '2026-05-20', 'GSK-2024-102', 1),
('Ventoline', 'Salbutamol', 'Bronchodilatateur', 'inhaler', '100µg/dose', 'GSK', 'Bronchodilatateur de secours', 150, 15, 6.80, '2026-08-10', 'GSK-2024-201', 1),
('Seretide', 'Salmétérol/Fluticasone', 'Anti-asthmatique', 'inhaler', '50/250µg', 'GSK', 'Traitement de fond de l''asthme', 80, 10, 35.00, '2026-04-15', 'GSK-2024-202', 1),
('Lasilix', 'Furosémide', 'Diurétique', 'tablet', '40mg', 'Sanofi', 'Diurétique de l''anse', 400, 40, 3.20, '2026-10-01', 'SNF-2024-301', 1),
('Tahor', 'Atorvastatine', 'Hypolipémiant', 'tablet', '20mg', 'Pfizer', 'Inhibiteur de la HMG-CoA réductase', 350, 35, 15.80, '2026-07-22', 'PFR-2024-401', 1),
('Amlor', 'Amlodipine', 'Antihypertenseur', 'tablet', '5mg', 'Pfizer', 'Inhibiteur calcique', 400, 40, 7.50, '2026-09-15', 'PFR-2024-402', 1),
('Coversyl', 'Périndopril', 'Antihypertenseur', 'tablet', '5mg', 'Servier', 'Inhibiteur de l''enzyme de conversion', 380, 38, 9.20, '2026-11-30', 'SRV-2024-501', 1),
('Glucophage', 'Metformine', 'Antidiabétique', 'tablet', '850mg', 'Merck', 'Biguanide antihyperglycémiant', 450, 45, 5.60, '2026-06-18', 'MRK-2024-601', 1),
('Januvia', 'Sitagliptine', 'Antidiabétique', 'tablet', '100mg', 'Merck', 'Inhibiteur de la DPP-4', 120, 12, 42.00, '2026-02-28', 'MRK-2024-602', 1),
('Levothyrox', 'Lévothyroxine', 'Hormone thyroïdienne', 'tablet', '75µg', 'Merck', 'Hormonothérapie substitutive thyroïdienne', 600, 60, 3.80, '2026-12-15', 'MRK-2024-603', 1),
('Efferalgan', 'Paracétamol', 'Analgésique', 'tablet', '500mg', 'Upsa', 'Antalgique effervescent', 450, 45, 3.10, '2026-08-20', 'UPS-2024-701', 1),
('Spasfon', 'Phloroglucinol/Triméthylphloroglucinol', 'Antispasmodique', 'tablet', '80mg/80mg', 'Teva', 'Antispasmodique musculotrope', 380, 38, 5.40, '2026-05-10', 'TVA-2024-801', 1),
('Dafalgan', 'Paracétamol', 'Analgésique', 'tablet', '1g', 'Upsa', 'Antalgique fort', 320, 32, 4.50, '2026-09-05', 'UPS-2024-702', 1),
('Imodium', 'Lopéramide', 'Antidiarrhéique', 'capsule', '2mg', 'Janssen', 'Antidiarrhéique opioïde', 280, 28, 4.80, '2026-04-22', 'JNS-2024-901', 1),
('Nexium', 'Esomeprazole', 'Anti-ulcéreux', 'capsule', '20mg', 'AstraZeneca', 'Inhibiteur de la pompe à protons', 250, 25, 18.50, '2026-07-08', 'AZN-2024-001', 1),
('Xanax', 'Alprazolam', 'Anxiolytique', 'tablet', '0.25mg', 'Pfizer', 'Benzodiazépine anxiolytique', 100, 10, 8.90, '2026-03-30', 'PFR-2024-403', 1),
('Doliprane', 'Paracétamol', 'Analgésique', 'suppository', '300mg', 'Sanofi', 'Suppositoires pédiatriques', 150, 15, 5.20, '2025-11-15', 'BTL-2024-003', 1);

-- ============================================================
-- 11. APPOINTMENTS (30 rendez-vous)
-- ============================================================

INSERT INTO `appointments` (`patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `duration_minutes`, `type`, `reason`, `notes`, `status`, `created_by`) VALUES
(1, 1, CURDATE(), '09:00:00', 30, 'follow_up', 'Suivi HTA', 'Contrôle tensionnel', 'completed', 7),
(2, 2, CURDATE(), '09:30:00', 45, 'consultation', 'Palpitations', 'Première consultation cardiologie', 'in_progress', 7),
(3, 1, CURDATE(), '10:00:00', 30, 'follow_up', 'Suivi diabète', 'HbA1c à vérifier', 'confirmed', 7),
(4, 4, CURDATE(), '10:30:00', 30, 'consultation', 'Acné sévère', 'Traitement local inefficace', 'scheduled', 7),
(5, 2, CURDATE(), '11:00:00', 45, 'follow_up', 'Contrôle cardiaque', 'Post-pontage', 'confirmed', 7),
(6, 3, CURDATE(), '11:30:00', 30, 'routine_check', 'Vaccination', 'Rappel vaccin DTP', 'scheduled', 7),
(7, 1, CURDATE(), '14:00:00', 30, 'consultation', 'Fatigue chronique', 'Bilan à réaliser', 'scheduled', 7),
(8, 5, CURDATE(), '14:30:00', 30, 'follow_up', 'Suivi gynécologique', 'Contrôle annuel', 'confirmed', 7),
(9, 1, CURDATE(), '15:00:00', 30, 'consultation', 'Douleur thoracique', 'À évaluer', 'scheduled', 7),
(10, 1, CURDATE(), '15:30:00', 30, 'follow_up', 'Suivi migraine', 'Ajuster traitement', 'confirmed', 7),
(11, 2, CURDATE(), '16:00:00', 45, 'follow_up', 'Suivi IC', 'Bilan BNP', 'scheduled', 7),
(12, 3, CURDATE(), '16:30:00', 30, 'routine_check', 'Bilan pédiatrique', 'Croissance et développement', 'scheduled', 7),
(13, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', 30, 'consultation', 'Toux persistante', 'Depuis 3 semaines', 'scheduled', 7),
(14, 5, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:30:00', 30, 'consultation', 'Règles irrégulières', 'Bilan hormonal', 'scheduled', 7),
(15, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', 45, 'follow_up', 'Suivi BPCO', 'Spirométrie', 'scheduled', 7),
(1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:30:00', 30, 'follow_up', 'Renouvellement ordonnance', 'Perindopril', 'scheduled', 7),
(3, 1, DATE_ADD(CURDATE(), INTERVAL -1 DAY), '09:00:00', 30, 'follow_up', 'Suivi diabète', 'Glycémie contrôlée', 'completed', 7),
(5, 2, DATE_ADD(CURDATE(), INTERVAL -1 DAY), '10:00:00', 45, 'follow_up', 'Contrôle cardiaque', 'Stable', 'completed', 7),
(8, 5, DATE_ADD(CURDATE(), INTERVAL -1 DAY), '11:00:00', 30, 'consultation', 'Consultation initiale', 'Première visite', 'completed', 7),
(10, 1, DATE_ADD(CURDATE(), INTERVAL -1 DAY), '14:00:00', 30, 'follow_up', 'Suivi migraine', 'Fréquence réduite', 'completed', 7),
(2, 2, DATE_ADD(CURDATE(), INTERVAL -2 DAY), '09:30:00', 45, 'consultation', 'Douleur thoracique', 'ECG normal', 'completed', 7),
(4, 4, DATE_ADD(CURDATE(), INTERVAL -2 DAY), '10:30:00', 30, 'consultation', 'Dermatite', 'Traitement prescrit', 'completed', 7),
(6, 3, DATE_ADD(CURDATE(), INTERVAL -2 DAY), '11:30:00', 30, 'routine_check', 'Vaccin grippe', 'Saisonnier', 'completed', 7),
(7, 1, DATE_ADD(CURDATE(), INTERVAL -2 DAY), '14:00:00', 30, 'consultation', 'Maux de tête', 'Tension normale', 'completed', 7),
(11, 2, DATE_ADD(CURDATE(), INTERVAL -3 DAY), '09:00:00', 45, 'follow_up', 'Suivi IC', 'Oedèmes résolus', 'completed', 7),
(12, 3, DATE_ADD(CURDATE(), INTERVAL -3 DAY), '10:00:00', 30, 'routine_check', 'Bilan annuel', 'Croissance normale', 'completed', 7),
(13, 1, DATE_ADD(CURDATE(), INTERVAL -3 DAY), '11:00:00', 30, 'consultation', 'Rhinite allergique', 'Antihistaminique', 'completed', 7),
(14, 5, DATE_ADD(CURDATE(), INTERVAL -3 DAY), '14:00:00', 30, 'consultation', 'Contraception', 'Discussion options', 'completed', 7),
(15, 2, DATE_ADD(CURDATE(), INTERVAL -3 DAY), '15:00:00', 45, 'follow_up', 'Suivi BPCO', 'Exacerbation légère', 'completed', 7),
(9, 1, DATE_ADD(CURDATE(), INTERVAL -4 DAY), '09:00:00', 30, 'consultation', 'Mal de gorge', 'Amygdalite', 'completed', 7);

-- ============================================================
-- 12. CONSULTATIONS
-- ============================================================

INSERT INTO `consultations` (`patient_id`, `doctor_id`, `appointment_id`, `consultation_date`, `chief_complaint`, `symptoms`, `diagnosis`, `diagnosis_icd10`, `treatment_plan`, `notes`, `follow_up_date`, `status`) VALUES
(1, 1, 1, CONCAT(CURDATE(), ' 09:00:00'), 'Suivi tensionnel', 'Aucun symptôme', 'Hypertension artérielle contrôlée', 'I10', 'Poursuite Coversyl 5mg. Bilan biologique dans 3 mois.', 'Patient observant. Tension 128/82.', DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 'completed'),
(2, 2, 2, CONCAT(CURDATE(), ' 09:30:00'), 'Palpitations', 'Palpitations, légère dyspnée', 'Tachycardie sinusale', 'R00.0', 'Holter 24h. Réduire caféine. Propranolol si besoin.', 'ECG en cours.', DATE_ADD(CURDATE(), INTERVAL 1 WEEK), 'in_progress'),
(3, 1, 3, CONCAT(CURDATE(), ' 10:00:00'), 'Suivi diabète', 'Aucun symptôme', 'Diabète type 2 équilibré', 'E11.9', 'Poursuite Metformine 850mg x2/j. HbA1c 6.8%.', 'Bilan satisfaisant.', DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 'completed'),
(5, 2, 5, CONCAT(CURDATE(), ' 11:00:00'), 'Contrôle cardiaque', 'Légère fatigue', 'Insuffisance cardiaque stable NYHA II', 'I50.9', 'Poursuite traitement. BNP stable. Echocœur dans 6 mois.', 'État stable.', DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'completed'),
(3, 1, 17, CONCAT(DATE_ADD(CURDATE(), INTERVAL -1 DAY), ' 09:00:00'), 'Suivi diabète', 'Aucun symptôme', 'Diabète type 2 équilibré', 'E11.9', 'Poursuite traitement actuel.', 'Glycémie à jeun 1.15g/L.', DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 'completed'),
(5, 2, 18, CONCAT(DATE_ADD(CURDATE(), INTERVAL -1 DAY), ' 10:00:00'), 'Contrôle cardiaque', 'Aucun symptôme', 'Post-pontage stable', 'Z95.1', 'Poursuite traitement. Rééducation cardiaque.', 'Bonne tolérance.', DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'completed'),
(8, 5, 19, CONCAT(DATE_ADD(CURDATE(), INTERVAL -1 DAY), ' 11:00:00'), 'Consultation initiale', 'Règles douloureuses', 'Dysménorrhée primaire', 'N94.4', 'AINS pendant les règles. Réévaluation dans 3 mois.', 'Première consultation.', DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 'completed'),
(10, 1, 20, CONCAT(DATE_ADD(CURDATE(), INTERVAL -1 DAY), ' 14:00:00'), 'Suivi migraine', '2 crises ce mois', 'Migraine avec aura', 'G43.1', 'Ajuster propranolol. Éviter déclencheurs.', 'Fréquence en baisse.', DATE_ADD(CURDATE(), INTERVAL 2 MONTH), 'completed'),
(2, 2, 21, CONCAT(DATE_ADD(CURDATE(), INTERVAL -2 DAY), ' 09:30:00'), 'Douleur thoracique', 'Douleur rétrosternale', 'Douleur musculosquelettique', 'M79.6', 'ECG normal. Troponines négatives. Ibuprofène si besoin.', 'Rassurant.', NULL, 'completed'),
(4, 4, 22, CONCAT(DATE_ADD(CURDATE(), INTERVAL -2 DAY), ' 10:30:00'), 'Dermatite', 'Éruption prurigineuse', 'Dermatite de contact', 'L25.9', 'Crème corticoïde locale. Éviter allergène.', 'Latex identifié.', DATE_ADD(CURDATE(), INTERVAL 2 WEEK), 'completed'),
(11, 2, 25, CONCAT(DATE_ADD(CURDATE(), INTERVAL -3 DAY), ' 09:00:00'), 'Suivi IC', 'Oedèmes résolus', 'Insuffisance cardiaque compensée', 'I50.9', 'Réduction Lasilix. Poursuite IEC.', 'Amélioration.', DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'completed'),
(15, 2, 29, CONCAT(DATE_ADD(CURDATE(), INTERVAL -3 DAY), ' 15:00:00'), 'Suivi BPCO', 'Toux productive', 'BPCO exacerbation légère', 'J44.1', 'Augmenter bronchodilatateur. Antibiotique si pas d''amélioration.', 'Spirométrie à programmer.', DATE_ADD(CURDATE(), INTERVAL 1 WEEK), 'completed');

-- ============================================================
-- 13. VITALS
-- ============================================================

INSERT INTO `vitals` (`consultation_id`, `patient_id`, `blood_pressure_systolic`, `blood_pressure_diastolic`, `heart_rate`, `respiratory_rate`, `temperature`, `oxygen_saturation`, `weight_kg`, `height_cm`, `bmi`, `blood_glucose`, `recorded_by`) VALUES
(1, 1, 128, 82, 72, 16, 36.6, 98.0, 78.5, 175, 25.6, NULL, 2),
(2, 2, 135, 85, 88, 18, 36.8, 97.5, 70.0, 168, 24.8, NULL, 3),
(3, 3, 130, 80, 75, 16, 36.5, 98.5, 85.0, 172, 28.7, 1.15, 2),
(4, 5, 125, 78, 68, 16, 36.4, 96.0, 82.0, 178, 25.9, NULL, 3),
(5, 3, 132, 82, 74, 16, 36.6, 98.0, 84.5, 172, 28.5, 1.12, 2),
(6, 5, 126, 76, 66, 16, 36.5, 97.0, 81.5, 178, 25.7, NULL, 3),
(7, 8, 118, 75, 70, 16, 36.7, 99.0, 62.0, 165, 22.8, NULL, 6),
(8, 10, 122, 78, 72, 16, 36.6, 98.5, 58.0, 160, 22.7, NULL, 2),
(9, 2, 128, 82, 76, 16, 36.5, 98.0, 70.0, 168, 24.8, NULL, 3),
(10, 4, 120, 76, 72, 16, 36.8, 99.0, 65.0, 170, 22.5, NULL, 5),
(11, 11, 130, 82, 72, 18, 36.5, 95.0, 80.0, 172, 27.0, NULL, 3),
(12, 15, 138, 88, 82, 20, 36.7, 92.0, 75.0, 170, 25.9, NULL, 3);

-- ============================================================
-- 14. PRESCRIPTIONS
-- ============================================================

INSERT INTO `prescriptions` (`patient_id`, `doctor_id`, `consultation_id`, `prescription_date`, `notes`, `status`) VALUES
(1, 1, 1, CURDATE(), 'Renouvellement traitement HTA', 'issued'),
(2, 2, 2, CURDATE(), 'En attente résultats Holter', 'draft'),
(3, 1, 3, CURDATE(), 'Traitement diabète', 'issued'),
(5, 2, 4, CURDATE(), 'Traitement IC', 'issued'),
(8, 5, 7, DATE_ADD(CURDATE(), INTERVAL -1 DAY), 'Dysménorrhée', 'issued'),
(10, 1, 8, DATE_ADD(CURDATE(), INTERVAL -1 DAY), 'Migraine', 'issued'),
(2, 2, 9, DATE_ADD(CURDATE(), INTERVAL -2 DAY), 'Douleur musculosquelettique', 'issued'),
(4, 4, 10, DATE_ADD(CURDATE(), INTERVAL -2 DAY), 'Dermatite', 'issued'),
(11, 2, 11, DATE_ADD(CURDATE(), INTERVAL -3 DAY), 'IC compensée', 'issued'),
(15, 2, 12, DATE_ADD(CURDATE(), INTERVAL -3 DAY), 'BPCO exacerbation', 'issued');

INSERT INTO `prescription_items` (`prescription_id`, `medication_id`, `medication_name`, `dosage`, `frequency`, `duration`, `instructions`, `quantity`) VALUES
(1, 10, 'Coversyl', '5mg', '1 fois par jour', '3 mois', 'Le matin à jeun', 90),
(1, 9, 'Amlor', '5mg', '1 fois par jour', '3 mois', 'Le soir', 90),
(3, 11, 'Glucophage', '850mg', '2 fois par jour', '3 mois', 'Au cours des repas', 180),
(4, 7, 'Lasilix', '40mg', '1 fois par jour', '1 mois', 'Le matin', 30),
(4, 10, 'Coversyl', '5mg', '1 fois par jour', '1 mois', 'Le matin', 30),
(4, 8, 'Tahor', '20mg', '1 fois par jour', '1 mois', 'Le soir', 30),
(5, 15, 'Spasfon', '1 cp', '3 fois par jour', '5 jours', 'En cas de douleur', 15),
(6, 1, 'Doliprane', '500mg', 'En cas de crise', '1 mois', 'Max 4g/jour', 30),
(7, 1, 'Doliprane', '500mg', '3 fois par jour', '5 jours', 'Si douleur', 15),
(8, 18, 'Nexium', '20mg', '1 fois par jour', '14 jours', 'Avant le petit-déjeuner', 14),
(9, 7, 'Lasilix', '40mg', '1 fois par jour', '1 mois', 'Le matin', 30),
(10, 5, 'Ventoline', '1 inhalation', 'En cas de dyspnée', '1 mois', 'Secours uniquement', 1);

-- ============================================================
-- 15. LAB RESULTS
-- ============================================================

INSERT INTO `lab_results` (`patient_id`, `doctor_id`, `test_name`, `test_category`, `laboratory`, `test_date`, `result_date`, `result_value`, `unit`, `reference_range`, `interpretation`, `status`, `notes`, `created_by`) VALUES
(1, 1, 'Glycémie à jeun', 'Biochimie', 'BioLab Paris', DATE_ADD(CURDATE(), INTERVAL -7 DAY), DATE_ADD(CURDATE(), INTERVAL -5 DAY), '1.05', 'g/L', '0.70-1.00', 'Légèrement élevée', 'completed', 'Surveillance recommandée', 2),
(1, 1, 'Créatinine', 'Biochimie', 'BioLab Paris', DATE_ADD(CURDATE(), INTERVAL -7 DAY), DATE_ADD(CURDATE(), INTERVAL -5 DAY), '85', 'µmol/L', '62-106', 'Normale', 'completed', 'Fonction rénale conservée', 2),
(3, 1, 'HbA1c', 'Biochimie', 'BioLab Paris', DATE_ADD(CURDATE(), INTERVAL -7 DAY), DATE_ADD(CURDATE(), INTERVAL -5 DAY), '6.8', '%', '< 6.5%', 'Légèrement élevée', 'completed', 'Objectif < 7% atteint', 2),
(3, 1, 'Cholestérol total', 'Biochimie', 'BioLab Paris', DATE_ADD(CURDATE(), INTERVAL -7 DAY), DATE_ADD(CURDATE(), INTERVAL -5 DAY), '1.85', 'g/L', '< 2.00', 'Normale', 'completed', 'Bien contrôlé', 2),
(5, 2, 'BNP', 'Cardiologie', 'BioLab Paris', DATE_ADD(CURDATE(), INTERVAL -14 DAY), DATE_ADD(CURDATE(), INTERVAL -12 DAY), '320', 'pg/mL', '< 400', 'Élevée mais stable', 'completed', 'Stable par rapport au précédent', 3),
(5, 2, 'Troponines', 'Cardiologie', 'BioLab Paris', DATE_ADD(CURDATE(), INTERVAL -14 DAY), DATE_ADD(CURDATE(), INTERVAL -12 DAY), '< 14', 'ng/L', '< 14', 'Normale', 'completed', 'Pas de nécrose myocardique', 3),
(11, 2, 'BNP', 'Cardiologie', 'BioLab Paris', DATE_ADD(CURDATE(), INTERVAL -10 DAY), DATE_ADD(CURDATE(), INTERVAL -8 DAY), '450', 'pg/mL', '< 400', 'Élevée', 'abnormal', 'Augmentation légère', 3),
(15, 2, 'Spirométrie FEV1', 'Pneumologie', 'PulmoLab', DATE_ADD(CURDATE(), INTERVAL -5 DAY), DATE_ADD(CURDATE(), INTERVAL -3 DAY), '65', '%', '> 80%', 'Diminuée', 'abnormal', 'BPCO modérée', 3),
(15, 2, 'Gaz du sang pO2', 'Pneumologie', 'PulmoLab', DATE_ADD(CURDATE(), INTERVAL -5 DAY), DATE_ADD(CURDATE(), INTERVAL -3 DAY), '72', 'mmHg', '> 80', 'Légèrement bas', 'abnormal', 'Hypoxémie légère', 3),
(2, 2, 'ECG 24h', 'Cardiologie', 'CardioLab', DATE_ADD(CURDATE(), INTERVAL -2 DAY), NULL, NULL, NULL, NULL, NULL, 'in_progress', 'Holter en cours d''analyse', 3);

-- ============================================================
-- 16. INVOICES
-- ============================================================

INSERT INTO `invoices` (`invoice_number`, `patient_id`, `consultation_id`, `issue_date`, `due_date`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `paid_amount`, `balance_due`, `status`, `notes`, `created_by`) VALUES
('F-2024-0001', 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 50.00, 10.00, 0.00, 60.00, 60.00, 0.00, 'paid', 'Consultation suivi', 9),
('F-2024-0002', 2, 2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 80.00, 16.00, 0.00, 96.00, 0.00, 96.00, 'pending', 'Consultation cardiologie', 9),
('F-2024-0003', 3, 3, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 50.00, 10.00, 0.00, 60.00, 60.00, 0.00, 'paid', 'Suivi diabète', 9),
('F-2024-0004', 5, 4, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 80.00, 16.00, 0.00, 96.00, 50.00, 46.00, 'partially_paid', 'Suivi cardiaque', 9),
('F-2024-0005', 3, 5, DATE_ADD(CURDATE(), INTERVAL -1 DAY), DATE_ADD(CURDATE(), INTERVAL 29 DAY), 50.00, 10.00, 0.00, 60.00, 60.00, 0.00, 'paid', 'Suivi diabète', 9),
('F-2024-0006', 5, 6, DATE_ADD(CURDATE(), INTERVAL -1 DAY), DATE_ADD(CURDATE(), INTERVAL 29 DAY), 80.00, 16.00, 0.00, 96.00, 96.00, 0.00, 'paid', 'Contrôle cardiaque', 9),
('F-2024-0007', 8, 7, DATE_ADD(CURDATE(), INTERVAL -1 DAY), DATE_ADD(CURDATE(), INTERVAL 29 DAY), 65.00, 13.00, 0.00, 78.00, 78.00, 0.00, 'paid', 'Consultation gynécologie', 9),
('F-2024-0008', 10, 8, DATE_ADD(CURDATE(), INTERVAL -1 DAY), DATE_ADD(CURDATE(), INTERVAL 29 DAY), 50.00, 10.00, 0.00, 60.00, 60.00, 0.00, 'paid', 'Suivi migraine', 9),
('F-2024-0009', 2, 9, DATE_ADD(CURDATE(), INTERVAL -2 DAY), DATE_ADD(CURDATE(), INTERVAL 28 DAY), 80.00, 16.00, 0.00, 96.00, 96.00, 0.00, 'paid', 'Consultation cardiologie', 9),
('F-2024-0010', 4, 10, DATE_ADD(CURDATE(), INTERVAL -2 DAY), DATE_ADD(CURDATE(), INTERVAL 28 DAY), 70.00, 14.00, 0.00, 84.00, 84.00, 0.00, 'paid', 'Consultation dermatologie', 9),
('F-2024-0011', 11, 11, DATE_ADD(CURDATE(), INTERVAL -3 DAY), DATE_ADD(CURDATE(), INTERVAL 27 DAY), 80.00, 16.00, 0.00, 96.00, 0.00, 96.00, 'overdue', 'Suivi IC', 9),
('F-2024-0012', 15, 12, DATE_ADD(CURDATE(), INTERVAL -3 DAY), DATE_ADD(CURDATE(), INTERVAL 27 DAY), 80.00, 16.00, 0.00, 96.00, 0.00, 96.00, 'overdue', 'Suivi BPCO', 9);

INSERT INTO `invoice_items` (`invoice_id`, `description`, `quantity`, `unit_price`, `total_price`, `item_type`) VALUES
(1, 'Consultation de suivi - Dr. Dupont', 1, 50.00, 50.00, 'consultation'),
(2, 'Consultation cardiologie - Dr. Bernard', 1, 80.00, 80.00, 'consultation'),
(3, 'Consultation de suivi - Dr. Dupont', 1, 50.00, 50.00, 'consultation'),
(4, 'Consultation cardiologie - Dr. Bernard', 1, 80.00, 80.00, 'consultation'),
(5, 'Consultation de suivi - Dr. Dupont', 1, 50.00, 50.00, 'consultation'),
(6, 'Consultation cardiologie - Dr. Bernard', 1, 80.00, 80.00, 'consultation'),
(7, 'Consultation gynécologie - Dr. Richard', 1, 65.00, 65.00, 'consultation'),
(8, 'Consultation de suivi - Dr. Dupont', 1, 50.00, 50.00, 'consultation'),
(9, 'Consultation cardiologie - Dr. Bernard', 1, 80.00, 80.00, 'consultation'),
(10, 'Consultation dermatologie - Dr. Robert', 1, 70.00, 70.00, 'consultation'),
(11, 'Consultation cardiologie - Dr. Bernard', 1, 80.00, 80.00, 'consultation'),
(12, 'Consultation cardiologie - Dr. Bernard', 1, 80.00, 80.00, 'consultation');

INSERT INTO `payments` (`invoice_id`, `amount`, `payment_method`, `payment_date`, `reference_number`, `notes`, `received_by`) VALUES
(1, 60.00, 'card', CURDATE(), 'CB-2024-001', 'Paiement par carte', 9),
(3, 60.00, 'cash', CURDATE(), 'ESP-2024-001', 'Paiement espèces', 9),
(4, 50.00, 'card', CURDATE(), 'CB-2024-002', 'Acompte', 9),
(5, 60.00, 'insurance', DATE_ADD(CURDATE(), INTERVAL -1 DAY), 'MUT-2024-001', 'Prise en charge mutuelle', 9),
(6, 96.00, 'card', DATE_ADD(CURDATE(), INTERVAL -1 DAY), 'CB-2024-003', 'Paiement par carte', 9),
(7, 78.00, 'card', DATE_ADD(CURDATE(), INTERVAL -1 DAY), 'CB-2024-004', 'Paiement par carte', 9),
(8, 60.00, 'cash', DATE_ADD(CURDATE(), INTERVAL -1 DAY), 'ESP-2024-002', 'Paiement espèces', 9),
(9, 96.00, 'card', DATE_ADD(CURDATE(), INTERVAL -2 DAY), 'CB-2024-005', 'Paiement par carte', 9),
(10, 84.00, 'insurance', DATE_ADD(CURDATE(), INTERVAL -2 DAY), 'MUT-2024-002', 'Prise en charge mutuelle', 9);

-- ============================================================
-- 17. NOTIFICATIONS
-- ============================================================

INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 'system', 'Bienvenue sur Docly', 'Votre compte administrateur a été créé avec succès.', '/dashboard', 1, DATE_ADD(NOW(), INTERVAL -7 DAY)),
(2, 'appointment', 'Nouveau rendez-vous', 'Rendez-vous avec Sophie Bernard à 09:30', '/appointments', 0, NOW()),
(2, 'lab_result', 'Résultat disponible', 'Les résultats de l''ECG 24h sont disponibles', '/lab-results', 0, DATE_ADD(NOW(), INTERVAL -1 DAY)),
(3, 'appointment', 'Rendez-vous confirmé', 'Rendez-vous avec Lucas Petit à 10:00', '/appointments', 1, DATE_ADD(NOW(), INTERVAL -1 DAY)),
(7, 'appointment', 'Nouveau rendez-vous', '5 nouveaux rendez-vous aujourd''hui', '/appointments', 0, NOW()),
(1, 'payment', 'Paiement reçu', 'Paiement de 60€ reçu - Facture F-2024-0001', '/billing', 0, NOW()),
(1, 'medication', 'Stock faible', 'Ventoline : stock inférieur à 15 unités', '/medications', 0, DATE_ADD(NOW(), INTERVAL -2 DAY)),
(1, 'medication', 'Produit expirant', 'Doliprane suppositoires expire dans 3 mois', '/medications', 0, DATE_ADD(NOW(), INTERVAL -3 DAY)),
(9, 'payment', 'Paiement en retard', 'Facture F-2024-0011 en retard de 3 jours', '/billing', 0, DATE_ADD(NOW(), INTERVAL -1 DAY)),
(9, 'payment', 'Paiement en retard', 'Facture F-2024-0012 en retard de 3 jours', '/billing', 0, DATE_ADD(NOW(), INTERVAL -1 DAY));

-- ============================================================
-- 18. AUDIT LOGS
-- ============================================================

INSERT INTO `audit_logs` (`user_id`, `user_name`, `action`, `module`, `entity_type`, `entity_id`, `description`, `ip_address`, `created_at`) VALUES
(1, 'Alexandre Martin', 'login', 'auth', 'user', 1, 'Connexion réussie', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -1 HOUR)),
(2, 'Dr. Marie Dupont', 'login', 'auth', 'user', 2, 'Connexion réussie', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -2 HOUR)),
(7, 'Lucas Moreau', 'login', 'auth', 'user', 7, 'Connexion réussie', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -30 MINUTE)),
(2, 'Dr. Marie Dupont', 'create', 'consultation', 'consultation', 1, 'Consultation créée pour Jean Martin', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -45 MINUTE)),
(2, 'Dr. Marie Dupont', 'create', 'prescription', 'prescription', 1, 'Ordonnance émise pour Jean Martin', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -40 MINUTE)),
(3, 'Dr. Pierre Bernard', 'create', 'consultation', 'consultation', 2, 'Consultation créée pour Sophie Bernard', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -20 MINUTE)),
(7, 'Lucas Moreau', 'create', 'appointment', 'appointment', 4, 'Rendez-vous créé pour Emma Robert', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -15 MINUTE)),
(9, 'Thomas Simon', 'create', 'invoice', 'invoice', 2, 'Facture F-2024-0002 créée', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -10 MINUTE)),
(1, 'Alexandre Martin', 'update', 'settings', 'settings', 1, 'Paramètres de la clinique mis à jour', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -5 MINUTE)),
(2, 'Dr. Marie Dupont', 'update', 'patient', 'patient', 3, 'Dossier patient Lucas Petit mis à jour', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -2 MINUTE));

SET FOREIGN_KEY_CHECKS = 1;
