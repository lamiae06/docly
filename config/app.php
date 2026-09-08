<?php
/**
 * Docly - Configuration de l'Application
 */

return [
    'name'        => 'Docly',
    'version'     => '2.0.0',
    'environment' => $_ENV['APP_ENV'] ?? 'local',
    'debug'       => ($_ENV['APP_DEBUG'] ?? 'true') === 'true',
    'timezone'    => 'Europe/Paris',
    'locale'      => 'fr_FR',
    'url'         => $_ENV['APP_URL'] ?? 'http://localhost:8000',
    'session_lifetime' => 120,
    'csrf_token_name'  => '_csrf_token',
    'upload_max_size'  => 10 * 1024 * 1024, // 10MB
    'allowed_upload_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
];
