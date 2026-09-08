<?php
namespace App\Middleware;

/**
 * Docly - Middleware d'Authentification
 */
class AuthMiddleware {

    public function handle(): void {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            // Si c'est une requête AJAX/API, retourner 401
            if ($this->isAjax()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Non authentifié', 'redirect' => '/login']);
                exit;
            }
            // Sinon rediriger vers login
            redirect('/login');
        }

        // Garde-fou multi-cabinets : un utilisateur connecté doit toujours
        // être rattaché à un cabinet actif dont la base a bien été résolue
        // (voir public/index.php). Si ce n'est pas le cas (session
        // incohérente, cabinet supprimé...), on force la déconnexion plutôt
        // que de laisser tourner une requête sans base de données valide.
        if (empty($_SESSION['cabinet_id']) || $GLOBALS['db'] === null) {
            session_destroy();
            if ($this->isAjax()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Session invalide', 'redirect' => '/login']);
                exit;
            }
            redirect('/login');
        }

        // Régénérer l'ID de session périodiquement
        if (!isset($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }

        // Recharger le rôle/les permissions depuis la base à chaque requête.
        // Sans cela, ils restaient figés depuis le login : un changement de
        // permissions fait depuis "Rôles & Permissions" (ou directement en
        // base) n'était visible qu'après déconnexion/reconnexion, ce qui
        // provoquait des refus d'accès (403) incompréhensibles sur des
        // actions pourtant autorisées entre-temps.
        $freshUser = \App\Models\User::findWithRole((int) $_SESSION['user_id']);
        if (!$freshUser || !$freshUser['is_active']) {
            session_destroy();
            if ($this->isAjax()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Compte désactivé ou introuvable', 'redirect' => '/login']);
                exit;
            }
            redirect('/login');
        }
        $_SESSION['user_role'] = $freshUser['role_slug'];
        $_SESSION['user_role_name'] = $freshUser['role_name'];
        $_SESSION['user_role_color'] = $freshUser['role_color'];
        $_SESSION['user_permissions'] = $freshUser['permissions'];
        if (!empty($freshUser['language'])) {
            $_SESSION['user_lang'] = $freshUser['language'];
        }

        // Vérifier le rôle pour les routes admin
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (str_contains($uri, '/settings/users') || str_contains($uri, '/settings/roles') || str_contains($uri, '/settings/audit-logs')) {
            if (!has_role('admin')) {
                http_response_code(403);
                if ($this->isAjax()) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Accès interdit']);
                    exit;
                }
                die('Accès interdit');
            }
        }
    }

    private function isAjax(): bool {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = trim(str_replace('/docly', '', $uri), '/');
        return str_starts_with($uri, 'api/') ||
               (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }
}
