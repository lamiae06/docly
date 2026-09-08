<?php
namespace App\Models;

class User extends Model {
    protected static string $table = 'users';
    protected static array $fillable = ['role_id', 'email', 'password_hash', 'first_name', 'last_name', 'phone', 'avatar', 'is_active', 'theme', 'language'];

    /**
     * Trouve un utilisateur par email
     */
    public static function findByEmail(string $email): ?array {
        return self::firstWhere('email', $email);
    }

    /**
     * Récupère l'utilisateur avec son rôle et ses permissions
     */
    public static function findWithRole(int $id): ?array {
        $stmt = self::db()->prepare("
            SELECT u.*, r.name as role_name, r.slug as role_slug, r.color as role_color
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id AND u.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        if (!$user) return null;

        // Récupérer les permissions
        $permStmt = self::db()->prepare("
            SELECT p.slug FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = :role_id
        ");
        $permStmt->execute([':role_id' => $user['role_id']]);
        $user['permissions'] = array_column($permStmt->fetchAll(), 'slug');

        return $user;
    }

    /**
     * Met à jour la dernière connexion
     */
    public static function updateLastLogin(int $id): void {
        $stmt = self::db()->prepare("
            UPDATE users SET last_login = NOW(), last_login_ip = :ip WHERE id = :id
        ");
        $stmt->execute([':id' => $id, ':ip' => $_SERVER['REMOTE_ADDR'] ?? null]);
    }

    /**
     * Met à jour le thème
     */
    public static function updateTheme(int $id, string $theme): void {
        $stmt = self::db()->prepare("UPDATE users SET theme = :theme WHERE id = :id");
        $stmt->execute([':id' => $id, ':theme' => $theme]);
    }

    /**
     * Récupère tous les utilisateurs (actifs et désactivés) avec leur rôle.
     * Utilisé par la page d'administration des utilisateurs : on veut aussi
     * voir les comptes désactivés pour pouvoir les réactiver.
     */
    public static function allWithRoles(): array {
        $stmt = self::db()->query("
            SELECT u.*, r.name as role_name, r.color as role_color
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.deleted_at IS NULL
            ORDER BY u.is_active DESC, u.created_at DESC
        ");
        return $stmt->fetchAll();
    }
}
