<?php
/**
 * Docly - Front Controller
 * Point d'entrée unique de l'application
 */

// La durée de session (voir config/app.php: session_lifetime, en minutes)
// doit être appliquée AVANT session_start(), sinon elle est ignorée et PHP
// retombe sur son réglage par défaut (session infinie tant que le navigateur
// reste ouvert).
session_set_cookie_params([
    'lifetime' => 120 * 60,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Define base path
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Load .env if present (simple KEY=VALUE loader, no external dependency)
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Autoloader
// Namespaces are `App\Controllers\...`, `App\Models\...`, `App\Middleware\...`
// but the folders on disk are lowercase (app/controllers, app/models, app/middleware).
// We map each namespace segment explicitly instead of relying on string
// replacement, which broke on case-sensitive filesystems (Linux/macOS APFS
// in case-sensitive mode) even though it happened to work on Windows/XAMPP.
spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $parts = explode('\\', substr($class, 4)); // strip leading "App\"
    if (count($parts) < 2) {
        return;
    }
    $folder = strtolower(array_shift($parts));  // Controllers -> controllers, Models -> models, ...
    $file = APP_PATH . '/' . $folder . '/' . implode('/', $parts) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load helpers
require_once APP_PATH . '/helpers/functions.php';
require_once APP_PATH . '/helpers/tenant.php';
require_once APP_PATH . '/helpers/i18n.php';

// Load configurations
$dbConfig = require CONFIG_PATH . '/database.php';
$appConfig = require CONFIG_PATH . '/app.php';

// Set timezone
date_default_timezone_set($appConfig['timezone']);

// Error handling
if ($appConfig['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Check that the MySQL PDO driver is enabled before trying to connect
if (!extension_loaded('pdo_mysql')) {
    $iniFile = php_ini_loaded_file() ?: '(fichier php.ini introuvable, tapez "php --ini" dans un terminal)';
    die(
        "Extension PHP manquante : pdo_mysql n'est pas activée.<br><br>" .
        "Fichier php.ini utilisé : <code>" . htmlspecialchars($iniFile) . "</code><br><br>" .
        "Ouvrez ce fichier, retirez le point-virgule devant <code>extension=pdo_mysql</code> " .
        "(et <code>extension=mysqli</code> si présent), enregistrez, puis redémarrez le serveur PHP."
    );
}

// ---------------------------------------------------------------
// Connexions base de données (architecture multi-cabinets)
//
// $GLOBALS['central_db'] : base plateforme (liste des cabinets, super-admins)
// $GLOBALS['db']         : base du CABINET actuellement connecté (résolue à
//                          partir de la session). C'est cette connexion que
//                          App\Models\Model et tout le code existant
//                          utilisent, donc l'isolation entre cabinets est
//                          garantie au niveau de la connexion elle-même,
//                          pas d'un simple filtre WHERE oubliable.
// ---------------------------------------------------------------
try {
    $GLOBALS['central_db'] = open_pdo_connection($dbConfig, $dbConfig['central_database']);
} catch (PDOException $e) {
    if ($appConfig['debug']) {
        die("Erreur de connexion à la base centrale: " . $e->getMessage() .
            "<br><br>Avez-vous créé la base <code>{$dbConfig['central_database']}</code> et importé " .
            "<code>database/central/schema.sql</code> ?");
    }
    die("Une erreur est survenue. Veuillez réessayer plus tard.");
}

$GLOBALS['db'] = null;
if (!empty($_SESSION['cabinet_id'])) {
    $stmt = $GLOBALS['central_db']->prepare("SELECT db_name, status FROM cabinets WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['cabinet_id']]);
    $cabinet = $stmt->fetch();

    if (!$cabinet || $cabinet['status'] !== 'active') {
        // Le cabinet a été suspendu/supprimé depuis la dernière requête :
        // on coupe la session immédiatement plutôt que d'attendre la
        // déconnexion manuelle de l'utilisateur.
        session_destroy();
        $currentUri = trim(str_replace('/docly', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), '/');
        if (str_starts_with($currentUri, 'api/')) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Ce cabinet a été suspendu ou supprimé.', 'redirect' => '/login']);
            exit;
        }
        redirect('/login');
    }

    try {
        $GLOBALS['db'] = open_pdo_connection($dbConfig, $cabinet['db_name']);
    } catch (PDOException $e) {
        if ($appConfig['debug']) {
            die("Erreur de connexion à la base du cabinet: " . $e->getMessage());
        }
        die("Une erreur est survenue. Veuillez réessayer plus tard.");
    }
}

$GLOBALS['config'] = $appConfig;

// Route the request
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/docly', '', $uri);
$uri = trim($uri, '/');

// Load routes
$routes = require BASE_PATH . '/routes/web.php';
$apiRoutes = require BASE_PATH . '/routes/api.php';

// Merge routes
$allRoutes = array_merge($routes, $apiRoutes);

// Find matching route
$matched = false;
foreach ($allRoutes as $pattern => $handler) {
    $regex = '#^' . preg_replace('#\{([^}]+)\}#', '([^/]+)', $pattern) . '$#';
    if (preg_match($regex, $uri, $matches)) {
        array_shift($matches);
        $matched = true;

        // Check middleware
        if (isset($handler['middleware'])) {
            $middlewareClass = 'App\\Middleware\\' . $handler['middleware'];
            if (class_exists($middlewareClass)) {
                $middleware = new $middlewareClass();
                $middleware->handle();
            }
        }

        // CSRF protection for state-changing requests.
        // The token is issued via csrf_token() (rendered as a <meta> tag by
        // the layouts) and docly.js automatically attaches it as the
        // X-CSRF-Token header on every non-GET fetch() call.
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        if (!in_array($httpMethod, ['GET', 'HEAD', 'OPTIONS'], true) && !str_starts_with($uri, 'api/auth/login')) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf_token'] ?? '');
            if (!verify_csrf_token($token)) {
                http_response_code(419);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Session expirée ou jeton de sécurité invalide. Veuillez recharger la page.']);
                exit;
            }
        }

        // Permission check (routes can declare a 'permission' key; see routes/web.php)
        if (isset($handler['permission']) && !can($handler['permission'])) {
            http_response_code(403);
            // Toute requête non-GET (store/update/delete/...) est appelée en
            // fetch() par le frontend et attend TOUJOURS du JSON en retour,
            // que la route soit préfixée "api/" ou non (ex: /documents/store,
            // /billing/store). Auparavant, seules les routes "api/" recevaient
            // du JSON ; les autres routes non-GET recevaient la page HTML
            // errors/403.php, que res.json() ne peut pas parser côté client.
            // Cela remontait comme une "Erreur réseau" trompeuse (catch du
            // fetch) au lieu du vrai message "permission refusée", et donnait
            // l'impression que la facture/le document n'avait pas été
            // enregistré alors que la requête n'avait même pas atteint le
            // contrôleur.
            $expectsJson = str_starts_with($uri, 'api/') || $httpMethod !== 'GET';
            if ($expectsJson) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Vous n\'avez pas la permission d\'accéder à cette ressource.']);
            } else {
                require APP_PATH . '/views/errors/403.php';
            }
            exit;
        }

        // Call controller
        $controllerClass = 'App\\Controllers\\' . $handler['controller'];
        $method = $handler['method'];

        // Filet de sécurité : si le contrôleur lève une exception (ou une
        // erreur fatale PHP 7+, capturée ici via \Throwable) pendant son
        // exécution, cela produisait auparavant une page d'erreur HTML —
        // que res.json() ne peut pas parser côté client, d'où un message
        // "Erreur réseau" qui masque complètement la vraie cause. On
        // renvoie maintenant une réponse JSON exploitable dans tous les cas
        // pour les requêtes non-GET (le mode debug ajoute le détail réel de
        // l'erreur, à désactiver en production via config/app.php).
        try {
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $method)) {
                    call_user_func_array([$controller, $method], $matches);
                } else {
                    http_response_code(404);
                    echo "Méthode non trouvée";
                }
            } else {
                http_response_code(404);
                echo "Contrôleur non trouvé";
            }
        } catch (\Throwable $e) {
            if (!headers_sent()) {
                http_response_code(500);
            }
            $expectsJson = str_starts_with($uri, 'api/') || $httpMethod !== 'GET';
            if ($expectsJson) {
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }
                $message = $appConfig['debug']
                    ? $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')'
                    : 'Une erreur interne est survenue. Veuillez réessayer.';
                echo json_encode(['error' => $message]);
            } elseif ($appConfig['debug']) {
                echo '<pre style="padding:2rem;color:#b91c1c;white-space:pre-wrap">'
                    . htmlspecialchars($e->getMessage() . "\n\n" . $e->getTraceAsString())
                    . '</pre>';
            } else {
                require APP_PATH . '/views/errors/404.php';
            }
        }
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    require APP_PATH . '/views/errors/404.php';
}
