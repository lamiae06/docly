<?php
namespace App\Controllers;

/**
 * Espace super-admin de la plateforme : liste et suspend/réactive les
 * cabinets. Utilise EXCLUSIVEMENT $GLOBALS['central_db'] — jamais la base
 * d'un cabinet.
 */
class PlatformController extends Controller {

    public function showLogin(): void {
        if (!empty($_SESSION['platform_admin_id'])) {
            redirect('/platform/dashboard');
        }
        $this->view('platform.login', [], 'auth');
    }

    public function apiLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        $stmt = $GLOBALS['central_db']->prepare("SELECT * FROM platform_admins WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            $this->json(['error' => 'Email ou mot de passe incorrect.'], 401);
        }

        $_SESSION['platform_admin_id'] = $admin['id'];
        $_SESSION['platform_admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];

        $this->json(['success' => true, 'redirect' => '/platform/dashboard']);
    }

    public function logout(): void {
        unset($_SESSION['platform_admin_id'], $_SESSION['platform_admin_name']);
        redirect('/platform/login');
    }

    public function dashboard(): void {
        $cabinets = $GLOBALS['central_db']->query("
            SELECT c.*, (SELECT COUNT(*) FROM cabinets) as total
            FROM cabinets c
            ORDER BY c.created_at DESC
        ")->fetchAll();

        $this->view('platform.dashboard', [
            'cabinets' => $cabinets,
            'pageTitle' => 'Administration de la plateforme',
        ], 'platform');
    }

    public function apiToggleCabinet(int $id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $stmt = $GLOBALS['central_db']->prepare("SELECT * FROM cabinets WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $cabinet = $stmt->fetch();
        if (!$cabinet) {
            $this->json(['error' => 'Cabinet introuvable'], 404);
        }

        $newStatus = $cabinet['status'] === 'active' ? 'suspended' : 'active';
        $upd = $GLOBALS['central_db']->prepare("UPDATE cabinets SET status = :status WHERE id = :id");
        $upd->execute([':status' => $newStatus, ':id' => $id]);

        $this->json(['success' => true, 'status' => $newStatus]);
    }
}
