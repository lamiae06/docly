<?php
namespace App\Middleware;

/**
 * Protège l'espace super-admin (/platform/...). Complètement indépendant de
 * la session "cabinet" : un super-admin de la plateforme n'a pas de compte
 * dans une base de cabinet, et n'utilise jamais $GLOBALS['db'] (uniquement
 * $GLOBALS['central_db']).
 */
class PlatformAuthMiddleware {
    public function handle(): void {
        if (empty($_SESSION['platform_admin_id'])) {
            if ($this->isAjax()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Non authentifié', 'redirect' => '/platform/login']);
                exit;
            }
            redirect('/platform/login');
        }
    }

    private function isAjax(): bool {
        $uri = trim(str_replace('/docly', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), '/');
        return str_starts_with($uri, 'api/platform/');
    }
}
