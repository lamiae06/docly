-- ============================================================
-- Docly - Schéma de Base de Données Complet
-- MySQL 8.0+
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. ROLES & PERMISSIONS
-- ============================================================

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255),
    `color` VARCHAR(20) DEFAULT '#3b82f6',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
    `role_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. USERS
-- ============================================================

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT UNSIGNED NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20),
    `avatar` VARCHAR(255),
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login` DATETIME,
    `last_login_ip` VARCHAR(45),
    `email_verified_at` DATETIME,
    `remember_token` VARCHAR(100),
    `theme` ENUM('light', 'dark', 'system') DEFAULT 'system',
    `language` VARCHAR(10) DEFAULT 'fr',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. CLINIC SETTINGS
-- ============================================================

DROP TABLE IF EXISTS `clinic_settings`;
CREATE TABLE `clinic_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT,
    `type` ENUM('string', 'integer', 'boolean', 'json', 'text') DEFAULT 'string',
    `group` VARCHAR(50) DEFAULT 'general',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. PATIENTS
-- ============================================================

DROP TABLE IF EXISTS `patients`;
CREATE TABLE `patients` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `patient_code` VARCHAR(20) NOT NULL UNIQUE,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `date_of_birth` DATE NOT NULL,
    `gender` ENUM('male', 'female', 'other') NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(255),
    `address` TEXT,
    `city` VARCHAR(100),
    `postal_code` VARCHAR(20),
    `emergency_contact_name` VARCHAR(200),
    `emergency_contact_phone` VARCHAR(20),
    `blood_type` ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'unknown') DEFAULT 'unknown',
    `allergies` TEXT,
    `chronic_diseases` TEXT,
    `insurance_name` VARCHAR(100),
    `insurance_number` VARCHAR(100),
    `insurance_expiry` DATE,
    `avatar` VARCHAR(255),
    `notes` TEXT,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    INDEX `idx_patients_name` (`last_name`, `first_name`),
    INDEX `idx_patients_phone` (`phone`),
    INDEX `idx_patients_code` (`patient_code`),
    INDEX `idx_patients_active` (`is_active`, `deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. DOCTORS
-- ============================================================

DROP TABLE IF EXISTS `doctors`;
CREATE TABLE `doctors` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `specialty` VARCHAR(100) NOT NULL,
    `sub_specialty` VARCHAR(100),
    `license_number` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20),
    `email` VARCHAR(255),
    `biography` TEXT,
    `consultation_fee` DECIMAL(10,2) DEFAULT 0.00,
    `avatar` VARCHAR(255),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_doctors_specialty` (`specialty`),
    INDEX `idx_doctors_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `doctor_schedules`;
CREATE TABLE `doctor_schedules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `doctor_id` INT UNSIGNED NOT NULL,
    `day_of_week` TINYINT NOT NULL COMMENT '0=Dimanche, 6=Samedi',
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `is_available` TINYINT(1) DEFAULT 1,
    `max_appointments` INT DEFAULT 10,
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE,
    INDEX `idx_schedules_doctor_day` (`doctor_id`, `day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. APPOINTMENTS
-- ============================================================

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `patient_id` INT UNSIGNED NOT NULL,
    `doctor_id` INT UNSIGNED NOT NULL,
    `appointment_date` DATE NOT NULL,
    `appointment_time` TIME NOT NULL,
    `duration_minutes` INT DEFAULT 30,
    `type` ENUM('consultation', 'follow_up', 'emergency', 'routine_check', 'vaccination', 'surgery', 'other') DEFAULT 'consultation',
    `reason` TEXT,
    `notes` TEXT,
    `status` ENUM('scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    `cancelled_reason` TEXT,
    `created_by` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`),
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_appointments_date` (`appointment_date`),
    INDEX `idx_appointments_status` (`status`),
    INDEX `idx_appointments_patient` (`patient_id`),
    INDEX `idx_appointments_doctor` (`doctor_id`),
    INDEX `idx_appointments_date_time` (`appointment_date`, `appointment_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. MEDICAL RECORDS
-- ============================================================

DROP TABLE IF EXISTS `medical_records`;
CREATE TABLE `medical_records` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `patient_id` INT UNSIGNED NOT NULL,
    `record_type` ENUM('allergy', 'chronic_disease', 'surgery', 'family_history', 'vaccination', 'general') DEFAULT 'general',
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `diagnosed_date` DATE,
    `severity` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'low',
    `status` ENUM('active', 'resolved', 'ongoing', 'monitored') DEFAULT 'active',
    `created_by` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_records_patient` (`patient_id`),
    INDEX `idx_records_type` (`record_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. CONSULTATIONS
-- ============================================================

DROP TABLE IF EXISTS `consultations`;
CREATE TABLE `consultations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `patient_id` INT UNSIGNED NOT NULL,
    `doctor_id` INT UNSIGNED NOT NULL,
    `appointment_id` INT UNSIGNED,
    `consultation_date` DATETIME NOT NULL,
    `chief_complaint` TEXT,
    `symptoms` TEXT,
    `diagnosis` TEXT,
    `diagnosis_icd10` VARCHAR(20),
    `treatment_plan` TEXT,
    `notes` TEXT,
    `follow_up_date` DATE,
    `follow_up_notes` TEXT,
    `status` ENUM('in_progress', 'completed', 'cancelled') DEFAULT 'in_progress',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`),
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`),
    FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE SET NULL,
    INDEX `idx_consultations_patient` (`patient_id`),
    INDEX `idx_consultations_doctor` (`doctor_id`),
    INDEX `idx_consultations_date` (`consultation_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `vitals`;
CREATE TABLE `vitals` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `consultation_id` INT UNSIGNED NOT NULL,
    `patient_id` INT UNSIGNED NOT NULL,
    `blood_pressure_systolic` INT,
    `blood_pressure_diastolic` INT,
    `heart_rate` INT,
    `respiratory_rate` INT,
    `temperature` DECIMAL(4,1),
    `oxygen_saturation` DECIMAL(5,2),
    `weight_kg` DECIMAL(5,2),
    `height_cm` DECIMAL(5,2),
    `bmi` DECIMAL(4,2),
    `blood_glucose` DECIMAL(5,2),
    `notes` TEXT,
    `recorded_by` INT UNSIGNED,
    `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`consultation_id`) REFERENCES `consultations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`),
    FOREIGN KEY (`recorded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_vitals_patient` (`patient_id`),
    INDEX `idx_vitals_consultation` (`consultation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. PRESCRIPTIONS
-- ============================================================

DROP TABLE IF EXISTS `prescriptions`;
CREATE TABLE `prescriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `patient_id` INT UNSIGNED NOT NULL,
    `doctor_id` INT UNSIGNED NOT NULL,
    `consultation_id` INT UNSIGNED,
    `prescription_date` DATE NOT NULL,
    `notes` TEXT,
    `status` ENUM('draft', 'issued', 'dispensed', 'cancelled') DEFAULT 'draft',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`),
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`),
    FOREIGN KEY (`consultation_id`) REFERENCES `consultations`(`id`) ON DELETE SET NULL,
    INDEX `idx_prescriptions_patient` (`patient_id`),
    INDEX `idx_prescriptions_doctor` (`doctor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `prescription_items`;
CREATE TABLE `prescription_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `prescription_id` INT UNSIGNED NOT NULL,
    `medication_id` INT UNSIGNED,
    `medication_name` VARCHAR(255) NOT NULL,
    `dosage` VARCHAR(100) NOT NULL,
    `frequency` VARCHAR(100) NOT NULL,
    `duration` VARCHAR(100),
    `instructions` TEXT,
    `quantity` INT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`medication_id`) REFERENCES `medications`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. MEDICATIONS
-- ============================================================

DROP TABLE IF EXISTS `medications`;
CREATE TABLE `medications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `generic_name` VARCHAR(255),
    `category` VARCHAR(100),
    `form` ENUM('tablet', 'capsule', 'syrup', 'injection', 'cream', 'ointment', 'drops', 'inhaler', 'patch', 'suppository', 'other') DEFAULT 'tablet',
    `dosage_strength` VARCHAR(100),
    `manufacturer` VARCHAR(200),
    `description` TEXT,
    `side_effects` TEXT,
    `contraindications` TEXT,
    `stock_quantity` INT DEFAULT 0,
    `stock_alert_level` INT DEFAULT 10,
    `unit_price` DECIMAL(10,2) DEFAULT 0.00,
    `expiry_date` DATE,
    `batch_number` VARCHAR(100),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_medications_name` (`name`),
    INDEX `idx_medications_category` (`category`),
    INDEX `idx_medications_expiry` (`expiry_date`),
    INDEX `idx_medications_stock` (`stock_quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `medication_stock_logs`;
CREATE TABLE `medication_stock_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `medication_id` INT UNSIGNED NOT NULL,
    `quantity_change` INT NOT NULL,
    `reason` VARCHAR(255),
    `reference_type` ENUM('purchase', 'dispense', 'return', 'adjustment', 'expired') DEFAULT 'adjustment',
    `reference_id` INT UNSIGNED,
    `notes` TEXT,
    `created_by` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`medication_id`) REFERENCES `medications`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. LAB RESULTS
-- ============================================================

DROP TABLE IF EXISTS `lab_results`;
CREATE TABLE `lab_results` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `patient_id` INT UNSIGNED NOT NULL,
    `doctor_id` INT UNSIGNED,
    `test_name` VARCHAR(255) NOT NULL,
    `test_category` VARCHAR(100),
    `laboratory` VARCHAR(200),
    `test_date` DATE NOT NULL,
    `result_date` DATE,
    `result_value` VARCHAR(255),
    `unit` VARCHAR(50),
    `reference_range` VARCHAR(100),
    `interpretation` TEXT,
    `status` ENUM('ordered', 'in_progress', 'completed', 'abnormal', 'critical') DEFAULT 'ordered',
    `document_path` VARCHAR(255),
    `notes` TEXT,
    `created_by` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`),
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_lab_patient` (`patient_id`),
    INDEX `idx_lab_status` (`status`),
    INDEX `idx_lab_date` (`test_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. DOCUMENTS
-- ============================================================

DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `patient_id` INT UNSIGNED,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `category` ENUM('prescription', 'lab_result', 'medical_report', 'administrative', 'imaging', 'consent', 'other') DEFAULT 'other',
    `file_path` VARCHAR(255) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_size` INT UNSIGNED,
    `mime_type` VARCHAR(100),
    `uploaded_by` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_documents_patient` (`patient_id`),
    INDEX `idx_documents_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 13. BILLING
-- ============================================================

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
    `patient_id` INT UNSIGNED NOT NULL,
    `consultation_id` INT UNSIGNED,
    `issue_date` DATE NOT NULL,
    `due_date` DATE NOT NULL,
    `subtotal` DECIMAL(12,2) DEFAULT 0.00,
    `tax_amount` DECIMAL(12,2) DEFAULT 0.00,
    `discount_amount` DECIMAL(12,2) DEFAULT 0.00,
    `total_amount` DECIMAL(12,2) DEFAULT 0.00,
    `paid_amount` DECIMAL(12,2) DEFAULT 0.00,
    `balance_due` DECIMAL(12,2) DEFAULT 0.00,
    `status` ENUM('pending', 'paid', 'partially_paid', 'overdue', 'cancelled') DEFAULT 'pending',
    `notes` TEXT,
    `created_by` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`),
    FOREIGN KEY (`consultation_id`) REFERENCES `consultations`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_invoices_patient` (`patient_id`),
    INDEX `idx_invoices_status` (`status`),
    INDEX `idx_invoices_number` (`invoice_number`),
    INDEX `idx_invoices_due` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoice_id` INT UNSIGNED NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `quantity` INT DEFAULT 1,
    `unit_price` DECIMAL(10,2) DEFAULT 0.00,
    `total_price` DECIMAL(10,2) DEFAULT 0.00,
    `item_type` ENUM('consultation', 'medication', 'lab_test', 'procedure', 'other') DEFAULT 'other',
    `reference_id` INT UNSIGNED,
    FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoice_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL,
    `payment_method` ENUM('cash', 'card', 'check', 'bank_transfer', 'insurance', 'other') DEFAULT 'cash',
    `payment_date` DATE NOT NULL,
    `reference_number` VARCHAR(100),
    `notes` TEXT,
    `received_by` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`received_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_payments_invoice` (`invoice_id`),
    INDEX `idx_payments_date` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 14. NOTIFICATIONS
-- ============================================================

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` ENUM('appointment', 'payment', 'lab_result', 'medication', 'system', 'reminder') DEFAULT 'system',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `link` VARCHAR(255),
    `is_read` TINYINT(1) DEFAULT 0,
    `read_at` DATETIME,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_notifications_user` (`user_id`, `is_read`),
    INDEX `idx_notifications_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 15. AUDIT LOGS
-- ============================================================

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED,
    `user_name` VARCHAR(200),
    `action` VARCHAR(50) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `entity_type` VARCHAR(50),
    `entity_id` INT UNSIGNED,
    `old_values` JSON,
    `new_values` JSON,
    `description` TEXT,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_action` (`action`),
    INDEX `idx_audit_module` (`module`),
    INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 16. AI CONVERSATIONS
-- ============================================================

DROP TABLE IF EXISTS `ai_conversations`;
CREATE TABLE `ai_conversations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `session_id` VARCHAR(100) NOT NULL,
    `title` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_ai_user` (`user_id`),
    INDEX `idx_ai_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `ai_messages`;
CREATE TABLE `ai_messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT UNSIGNED NOT NULL,
    `role` ENUM('user', 'assistant', 'system') NOT NULL,
    `content` TEXT NOT NULL,
    `model_used` VARCHAR(100),
    `tokens_used` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`conversation_id`) REFERENCES `ai_conversations`(`id`) ON DELETE CASCADE,
    INDEX `idx_ai_msg_conversation` (`conversation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
