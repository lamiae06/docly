<?php
namespace App\Controllers;

use App\Models\User;

class SettingsController extends Controller {

    public function index(): void {
        $this->view('settings.index', ['pageTitle' => 'Paramètres']);
    }

    public function profile(): void {
        $user = User::findWithRole($_SESSION['user_id'] ?? 0);
        $this->view('settings.profile', ['user' => $user, 'pageTitle' => 'Profil']);
    }

    public function security(): void {
        $this->view('settings.security', ['pageTitle' => 'Sécurité']);
    }

    public function clinic(): void {
        $this->requireAdmin();

        $db = $GLOBALS['db'];
        $stmt = $db->query("SELECT `key`, `value` FROM clinic_settings ORDER BY `group`, `key`");
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }

        $this->view('settings.clinic', ['settings' => $settings, 'pageTitle' => 'Clinique']);
    }

    public function users(): void {
        $this->requireAdmin();

        $users = User::allWithRoles();
        $db = $GLOBALS['db'];
        $roles = $db->query("SELECT id, name, slug, color FROM roles ORDER BY name")->fetchAll();

        $this->view('settings.users', ['users' => $users, 'roles' => $roles, 'pageTitle' => 'Utilisateurs']);
    }

    public function roles(): void {
        $this->requireAdmin();

        $db = $GLOBALS['db'];
        $roles = $db->query("
            SELECT r.*, COUNT(rp.permission_id) as permission_count, (SELECT COUNT(*) FROM users u WHERE u.role_id = r.id AND u.deleted_at IS NULL) as user_count
            FROM roles r
            LEFT JOIN role_permissions rp ON r.id = rp.role_id
            GROUP BY r.id
            ORDER BY r.name
        ")->fetchAll();

        $this->view('settings.roles', ['roles' => $roles, 'pageTitle' => 'Rôles & Permissions']);
    }

    public function auditLogs(): void {
        $this->requireAdmin();

        $db = $GLOBALS['db'];

        // Filtres optionnels (utilisés par les liens "Historique" placés sur
        // chaque fiche patient/rendez-vous/facture/etc., qui renvoient ici
        // avec entity_type + entity_id pour ne montrer que les actions liées
        // à CET enregistrement précis).
        $module = trim($_GET['module'] ?? '');
        $entityType = trim($_GET['entity_type'] ?? '');
        $entityId = (int) ($_GET['entity_id'] ?? 0);

        $where = [];
        $params = [];
        if ($module !== '') { $where[] = 'module = :module'; $params[':module'] = $module; }
        if ($entityType !== '') { $where[] = 'entity_type = :entity_type'; $params[':entity_type'] = $entityType; }
        if ($entityId > 0) { $where[] = 'entity_id = :entity_id'; $params[':entity_id'] = $entityId; }

        $sql = "SELECT * FROM audit_logs";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY created_at DESC LIMIT 200";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        $this->view('settings.audit', [
            'logs' => $logs,
            'filterModule' => $module,
            'filterEntityType' => $entityType,
            'filterEntityId' => $entityId,
            'pageTitle' => "Logs d'audit",
        ]);
    }

    /**
     * Affiche la grille des permissions pour UN rôle (case à cocher par
     * permission, groupées par module). Remplace le message "à faire
     * depuis la base de données".
     */
    public function roleShow(int $id): void {
        $this->requireAdmin();

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM roles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $role = $stmt->fetch();
        if (!$role) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $allPermissions = $db->query("SELECT * FROM permissions ORDER BY slug")->fetchAll();

        $grantedStmt = $db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = :id");
        $grantedStmt->execute([':id' => $id]);
        $granted = array_map('intval', array_column($grantedStmt->fetchAll(), 'permission_id'));

        // Regroupe les permissions par module (préfixe avant le point, ex :
        // "billing.manage" -> module "billing") pour un affichage en
        // sections plutôt qu'une longue liste plate.
        $grouped = [];
        foreach ($allPermissions as $perm) {
            $moduleKey = explode('.', $perm['slug'])[0];
            $grouped[$moduleKey][] = $perm;
        }
        ksort($grouped);

        $this->view('settings.role_detail', [
            'role' => $role,
            'groupedPermissions' => $grouped,
            'grantedIds' => $granted,
            'pageTitle' => 'Permissions - ' . $role['name'],
        ]);
    }

    /**
     * Enregistre la liste complète des permissions cochées pour un rôle
     * (remplace entièrement l'ancien contenu de role_permissions pour ce
     * rôle : "sync", pas "append").
     */
    public function apiUpdateRolePermissions(int $id): void {
        $this->requireAdmin(true);
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM roles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $role = $stmt->fetch();
        if (!$role) {
            $this->json(['error' => 'Rôle introuvable'], 404);
        }

        // Un administrateur ne peut pas se retirer ses propres permissions
        // "settings.manage" par erreur en décochant tout : cela le verrouillerait
        // hors de cette page sans autre administrateur pour la rouvrir.
        $permissionIds = array_map('intval', $_POST['permission_ids'] ?? []);
        if ($role['slug'] === 'admin') {
            $manageSettingsId = $db->query("SELECT id FROM permissions WHERE slug = 'settings.manage'")->fetchColumn();
            if ($manageSettingsId && !in_array((int) $manageSettingsId, $permissionIds, true)) {
                $this->json(['error' => 'Impossible de retirer "Gérer les paramètres" au rôle Administrateur.'], 422);
            }
        }

        $db->beginTransaction();
        try {
            $db->prepare("DELETE FROM role_permissions WHERE role_id = :id")->execute([':id' => $id]);
            if (!empty($permissionIds)) {
                $insert = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");
                foreach (array_unique($permissionIds) as $permId) {
                    $insert->execute([':role_id' => $id, ':permission_id' => $permId]);
                }
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->json(['error' => 'Erreur lors de l\'enregistrement des permissions.'], 500);
        }

        audit_log('update', 'settings', 'role', $id, 'Permissions mises à jour pour le rôle : ' . $role['name']);

        // Les sessions déjà ouvertes des utilisateurs de ce rôle se
        // rafraîchissent automatiquement à la requête suivante (voir
        // AuthMiddleware::handle), sans besoin de les déconnecter.
        $this->json(['success' => true]);
    }

    // ---------------------------------------------------------------
    // API
    // ---------------------------------------------------------------

    public function apiUpdateProfile(): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $userId = $_SESSION['user_id'] ?? 0;
        $data = $this->input([
            'first_name' => 'string',
            'last_name' => 'string',
            'phone' => 'string',
            'theme' => 'string',
        ]);

        $errors = $this->validate($data, [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
        ]);
        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        if (!in_array($data['theme'], ['light', 'dark', 'system'], true)) {
            $data['theme'] = 'system';
        }

        User::update($userId, $data);

        $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
        $_SESSION['user_theme'] = $data['theme'];

        audit_log('update', 'settings', 'user', $userId, 'Profil mis à jour');

        $this->json(['success' => true]);
    }

    public function apiUpdatePassword(): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $userId = $_SESSION['user_id'] ?? 0;
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $user = User::find($userId);
        if (!$user || !password_verify($current, $user['password_hash'])) {
            $this->json(['error' => 'Mot de passe actuel incorrect'], 422);
        }

        if (strlen($new) < 8) {
            $this->json(['error' => 'Le nouveau mot de passe doit contenir au moins 8 caractères'], 422);
        }

        if ($new !== $confirm) {
            $this->json(['error' => 'Les mots de passe ne correspondent pas'], 422);
        }

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        $stmt->execute([':hash' => password_hash($new, PASSWORD_DEFAULT), ':id' => $userId]);

        audit_log('update', 'settings', 'user', $userId, 'Mot de passe modifié');

        $this->json(['success' => true]);
    }

    public function apiUpdateClinic(): void {
        $this->requireAdmin(true);
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $allowedKeys = [
            'clinic_name', 'clinic_address', 'clinic_phone', 'clinic_email', 'clinic_website',
            'tax_rate', 'currency', 'appointment_default_duration', 'working_hours_start', 'working_hours_end',
        ];

        $db = $GLOBALS['db'];
        $stmt = $db->prepare("UPDATE clinic_settings SET `value` = :value WHERE `key` = :key");
        foreach ($allowedKeys as $key) {
            if (isset($_POST[$key])) {
                $stmt->execute([':value' => trim(strip_tags($_POST[$key])), ':key' => $key]);
            }
        }

        audit_log('update', 'settings', 'clinic_settings', null, 'Paramètres de la clinique mis à jour');

        $this->json(['success' => true]);
    }

    public function apiCreateUser(): void {
        $this->requireAdmin(true);
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $data = $this->input([
            'first_name' => 'string',
            'last_name' => 'string',
            'email' => 'email',
            'phone' => 'string',
        ]);
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $password = $_POST['password'] ?? '';

        $errors = $this->validate($data, [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'required|email',
        ]);
        if ($roleId <= 0) {
            $errors['role_id'] = 'Le rôle est obligatoire.';
        }
        if (strlen($password) < 8) {
            $errors['password'] = 'Minimum 8 caractères requis.';
        }
        if (!empty($errors)) {
            $this->json(['error' => 'Validation échouée', 'errors' => $errors], 422);
        }

        if (User::findByEmail($data['email'])) {
            $this->json(['error' => 'Un utilisateur avec cet email existe déjà'], 422);
        }

        $data['role_id'] = $roleId;
        $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        $data['is_active'] = 1;

        $id = User::create($data);
        audit_log('create', 'settings', 'user', $id, 'Utilisateur créé: ' . $data['first_name'] . ' ' . $data['last_name']);

        $this->json(['success' => true, 'id' => $id]);
    }

    /**
     * Change la langue de l'interface pour l'utilisateur connecté.
     * Utilisé par le sélecteur de langue dans l'en-tête (FR / EN / AR).
     */
    public function apiSetLanguage(): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $lang = $_POST['lang'] ?? '';
        if (!array_key_exists($lang, DOCLY_SUPPORTED_LANGUAGES)) {
            $this->json(['error' => 'Langue non supportée'], 422);
        }

        $userId = $_SESSION['user_id'] ?? 0;
        if ($userId) {
            User::update($userId, ['language' => $lang]);
        }
        $_SESSION['user_lang'] = $lang;
        setcookie('docly_lang', $lang, time() + 365 * 24 * 3600, '/', '', false, false);

        $this->json(['success' => true, 'lang' => $lang, 'dir' => lang_direction($lang)]);
    }

    public function apiToggleUser(int $id): void {
        $this->requireAdmin(true);
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $user = User::find($id);
        if (!$user) {
            $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        if ((int) $user['id'] === (int) ($_SESSION['user_id'] ?? 0)) {
            $this->json(['error' => 'Vous ne pouvez pas désactiver votre propre compte'], 400);
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("UPDATE users SET is_active = :active WHERE id = :id");
        $stmt->execute([':active' => $newStatus, ':id' => $id]);

        audit_log('update', 'settings', 'user', $id, $newStatus ? 'Compte réactivé' : 'Compte désactivé');

        $this->json(['success' => true, 'is_active' => $newStatus]);
    }

    /**
     * Vérifie que l'utilisateur connecté est administrateur.
     * $asApi = true renvoie une erreur JSON, sinon une page 403.
     */
    private function requireAdmin(bool $asApi = false): void {
        if (!has_role('admin')) {
            if ($asApi) {
                $this->json(['error' => 'Accès réservé aux administrateurs'], 403);
            }
            http_response_code(403);
            die('Accès réservé aux administrateurs.');
        }
    }
}
