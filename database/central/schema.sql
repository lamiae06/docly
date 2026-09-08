-- ============================================================
-- Docly - Base CENTRALE (plateforme multi-cabinets)
-- Cette base ne contient JAMAIS de données médicales : uniquement
-- la liste des cabinets et les comptes des administrateurs de la
-- plateforme. Chaque cabinet a sa PROPRE base de données (voir
-- database/migrations/schema.sql, provisionnée automatiquement).
-- ============================================================

CREATE TABLE IF NOT EXISTS `cabinets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(60) NOT NULL UNIQUE COMMENT 'Identifiant tapé au login, ex: clinique-dupont',
    `db_name` VARCHAR(80) NOT NULL UNIQUE COMMENT 'Nom réel de la base MySQL dédiée à ce cabinet',
    `status` ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    `contact_email` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_cabinets_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `platform_admins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compte super-admin par défaut : platform@docly.local / password123
-- (À CHANGER IMMÉDIATEMENT après la première connexion)
INSERT IGNORE INTO `platform_admins` (`email`, `password_hash`, `first_name`, `last_name`) VALUES
('platform@docly.local', '$2y$10$PR5KoTzfNq9zKY.io7RXaeWo7Bbs130CBSBs84aLbjXlwweoJLb8q', 'Super', 'Admin');
