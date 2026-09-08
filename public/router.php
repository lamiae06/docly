<?php
/**
 * Docly - Routeur pour le serveur PHP integre (php -S)
 * Reproduit le comportement du .htaccess : si le fichier/dossier demande
 * existe reellement dans public/, on le sert tel quel (CSS, JS, images...),
 * sinon on redirige tout vers index.php (front controller).
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    // Fichier statique existant (asset, image, etc.) -> on laisse le serveur PHP le servir
    return false;
}

// Sinon on passe tout au front controller
require __DIR__ . '/index.php';
