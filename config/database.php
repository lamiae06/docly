<?php
/**
 * Docly - Configuration de la Base de Données
 */

return [
    'host'     => $_ENV['DB_HOST']     ?? 'localhost',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset'  => 'utf8mb4',
    'collation'=> 'utf8mb4_unicode_ci',
    // Base centrale de la plateforme (liste des cabinets, super-admins).
    // Ne contient jamais de données médicales. Chaque cabinet a ensuite sa
    // propre base, nommée dynamiquement (voir app/helpers/tenant.php).
    'central_database' => $_ENV['DB_CENTRAL_DATABASE'] ?? 'docly_central',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];
