<?php
namespace App\Controllers;

use App\Models\User;

class AuthController extends Controller {

    /**
     * Affiche la page de connexion
     */
    public function showLogin(): void {
        if (isset($_SESSION['user_id'])) {
            redirect('/dashboard');
        }
        $this->view('auth.login', [], 'auth');
    }

    /**
     * Connexion API (AJAX)
     */
    public function apiLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $cabinetSlug = trim($_POST['cabinet'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember = filter_input(INPUT_POST, 'remember', FILTER_VALIDATE_BOOL);

        // Rate limiting simple
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = 'login_attempts_' . $ip;
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'time' => time()];
        }
        if ($_SESSION[$key]['count'] >= 5 && time() - $_SESSION[$key]['time'] < 900) {
            $this->json(['error' => 'Trop de tentatives. Réessayez dans 15 minutes.'], 429);
        }

        if (empty($cabinetSlug) || empty($email) || empty($password)) {
            $_SESSION[$key]['count']++;
            $this->json(['error' => 'Identifiant du cabinet, email et mot de passe requis.'], 400);
        }

        // Résolution du cabinet (base centrale) : c'est elle qui détermine
        // à QUELLE base de données on va se connecter pour vérifier le
        // mot de passe. Deux cabinets ne partagent jamais de connexion.
        $stmt = $GLOBALS['central_db']->prepare("SELECT id, name, slug, db_name, status FROM cabinets WHERE slug = :slug");
        $stmt->execute([':slug' => strtolower($cabinetSlug)]);
        $cabinet = $stmt->fetch();

        if (!$cabinet) {
            $_SESSION[$key]['count']++;
            $this->json(['error' => 'Identifiant de cabinet inconnu.'], 401);
        }
        if ($cabinet['status'] !== 'active') {
            $this->json(['error' => 'Ce cabinet est suspendu. Contactez votre administrateur.'], 403);
        }

        try {
            $dbConfig = require CONFIG_PATH . '/database.php';
            $tenantDb = open_pdo_connection($dbConfig, $cabinet['db_name']);
        } catch (\PDOException $e) {
            $this->json(['error' => 'Impossible de joindre la base de ce cabinet.'], 500);
        }
        // On bascule temporairement $GLOBALS['db'] sur la base de ce cabinet
        // le temps de vérifier les identifiants (User::findByEmail etc.
        // utilisent App\Models\Model::db() = $GLOBALS['db']).
        $GLOBALS['db'] = $tenantDb;

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION[$key]['count']++;
            audit_log('login_failed', 'auth', 'user', null, "Tentative de connexion échouée pour: $email");
            $this->json(['error' => 'Email ou mot de passe incorrect.'], 401);
        }

        if (!$user['is_active']) {
            $this->json(['error' => 'Ce compte est désactivé.'], 403);
        }

        // Récupérer l'utilisateur avec rôle et permissions
        $userFull = User::findWithRole($user['id']);

        // Créer la session
        $_SESSION['cabinet_id'] = $cabinet['id'];
        $_SESSION['cabinet_slug'] = $cabinet['slug'];
        $_SESSION['cabinet_name'] = $cabinet['name'];
        $_SESSION['user_id'] = $userFull['id'];
        $_SESSION['user_name'] = $userFull['first_name'] . ' ' . $userFull['last_name'];
        $_SESSION['user_email'] = $userFull['email'];
        $_SESSION['user_role'] = $userFull['role_slug'];
        $_SESSION['user_role_name'] = $userFull['role_name'];
        $_SESSION['user_role_color'] = $userFull['role_color'];
        $_SESSION['user_avatar'] = $userFull['avatar'];
        $_SESSION['user_permissions'] = $userFull['permissions'];
        $_SESSION['user_theme'] = $userFull['theme'];
        $_SESSION['user_lang'] = $userFull['language'] ?? ($_COOKIE['docly_lang'] ?? 'fr');

        // Remember me
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + 30 * 24 * 3600, '/', '', false, true);
        }

        // Mettre à jour la dernière connexion
        User::updateLastLogin($userFull['id']);

        // Reset rate limiting
        $_SESSION[$key] = ['count' => 0, 'time' => time()];

        // Audit log
        audit_log('login', 'auth', 'user', $userFull['id'], 'Connexion réussie');

        $this->json([
            'success' => true,
            'user' => [
                'id' => $userFull['id'],
                'name' => $_SESSION['user_name'],
                'email' => $userFull['email'],
                'role' => $userFull['role_name'],
                'role_slug' => $userFull['role_slug'],
                'avatar' => $userFull['avatar'],
                'theme' => $userFull['theme'],
            ],
            'cabinet' => ['id' => $cabinet['id'], 'name' => $cabinet['name'], 'slug' => $cabinet['slug']],
            'redirect' => '/dashboard'
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout(): void {
        if (isset($_SESSION['user_id'])) {
            audit_log('logout', 'auth', 'user', $_SESSION['user_id'], 'Déconnexion');
        }

        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');

        if ($this->isAjax()) {
            $this->json(['success' => true]);
        }
        redirect('/login');
    }

    /**
     * Récupère l'utilisateur connecté (API)
     */
    public function me(): void {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['error' => 'Non authentifié'], 401);
        }

        $user = User::findWithRole($_SESSION['user_id']);
        $this->json([
            'id' => $user['id'],
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'email' => $user['email'],
            'role' => $user['role_name'],
            'role_slug' => $user['role_slug'],
            'avatar' => $user['avatar'],
            'theme' => $user['theme'],
            'permissions' => $user['permissions'],
        ]);
    }

    private function isAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
