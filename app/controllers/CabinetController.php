<?php
namespace App\Controllers;

use App\Models\User;

/**
 * Inscription en libre-service d'un nouveau cabinet : crée une base de
 * données MySQL dédiée, la peuple (schéma + rôles de base), puis crée le
 * compte administrateur de ce cabinet.
 */
class CabinetController extends Controller {

    public function showRegister(): void {
        if (isset($_SESSION['user_id'])) {
            redirect('/dashboard');
        }
        $this->view('auth.register_cabinet', [], 'auth');
    }

    public function apiRegister(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $cabinetName = trim($_POST['cabinet_name'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $errors = [];
        if (strlen($cabinetName) < 2) $errors['cabinet_name'] = 'Nom de cabinet trop court.';
        if ($firstName === '') $errors['first_name'] = 'Prénom requis.';
        if ($lastName === '') $errors['last_name'] = 'Nom requis.';
        if (!$email) $errors['email'] = 'Email invalide.';
        if (strlen($password) < 8) $errors['password'] = 'Minimum 8 caractères.';
        if ($password !== $confirm) $errors['confirm_password'] = 'Les mots de passe ne correspondent pas.';

        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        $centralDb = $GLOBALS['central_db'];
        $dbConfig = require CONFIG_PATH . '/database.php';

        $slug = generate_unique_cabinet_slug($centralDb, $cabinetName);
        $dbName = slug_to_db_name($slug);

        try {
            // 1. Provisionner la base dédiée à ce cabinet (schéma + rôles)
            provision_cabinet_database($centralDb, $dbConfig, $dbName);

            // 2. Enregistrer le cabinet dans la base centrale
            $stmt = $centralDb->prepare("
                INSERT INTO cabinets (name, slug, db_name, status, contact_email)
                VALUES (:name, :slug, :db_name, 'active', :email)
            ");
            $stmt->execute([
                ':name' => $cabinetName,
                ':slug' => $slug,
                ':db_name' => $dbName,
                ':email' => $email,
            ]);
            $cabinetId = (int) $centralDb->lastInsertId();

            // 3. Se connecter à la nouvelle base pour y créer le compte admin
            $tenantDb = open_pdo_connection($dbConfig, $dbName);
            $GLOBALS['db'] = $tenantDb;

            $roleStmt = $tenantDb->prepare("SELECT id FROM roles WHERE slug = 'admin'");
            $roleStmt->execute();
            $adminRoleId = $roleStmt->fetchColumn();

            $userId = User::create([
                'role_id' => $adminRoleId,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'is_active' => 1,
            ]);

            $updateSettings = $tenantDb->prepare("UPDATE clinic_settings SET `value` = :value WHERE `key` = 'clinic_name'");
            $updateSettings->execute([':value' => $cabinetName]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Impossible de créer le cabinet : ' . $e->getMessage()], 500);
        }

        // 4. Connecter automatiquement l'administrateur qui vient de créer le cabinet
        $userFull = User::findWithRole($userId);

        $_SESSION['cabinet_id'] = $cabinetId;
        $_SESSION['cabinet_slug'] = $slug;
        $_SESSION['cabinet_name'] = $cabinetName;
        $_SESSION['user_id'] = $userFull['id'];
        $_SESSION['user_name'] = $userFull['first_name'] . ' ' . $userFull['last_name'];
        $_SESSION['user_email'] = $userFull['email'];
        $_SESSION['user_role'] = $userFull['role_slug'];
        $_SESSION['user_role_name'] = $userFull['role_name'];
        $_SESSION['user_role_color'] = $userFull['role_color'];
        $_SESSION['user_avatar'] = $userFull['avatar'];
        $_SESSION['user_permissions'] = $userFull['permissions'];
        $_SESSION['user_theme'] = $userFull['theme'];

        audit_log('create', 'settings', 'cabinet', $cabinetId, 'Cabinet créé : ' . $cabinetName);

        $this->json([
            'success' => true,
            'cabinet' => ['id' => $cabinetId, 'name' => $cabinetName, 'slug' => $slug],
            'redirect' => '/dashboard',
        ]);
    }
}
