<?php
namespace App\Models;

class Notification extends Model {
    protected static string $table = 'notifications';
    protected static array $fillable = ['user_id', 'type', 'title', 'message', 'link', 'is_read'];

    /**
     * Notifications non lues d'un utilisateur
     */
    public static function unread(int $userId, int $limit = 10): array {
        $stmt = self::db()->prepare("
            SELECT * FROM notifications 
            WHERE user_id = :user_id AND is_read = 0 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Compte les notifications non lues
     */
    public static function unreadCount(int $userId): int {
        return self::count("user_id = :user_id AND is_read = 0", [':user_id' => $userId]);
    }

    /**
     * Marque comme lue
     */
    public static function markAsRead(int $id): void {
        $stmt = self::db()->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    /**
     * Marque tout comme lu
     */
    public static function markAllAsRead(int $userId): void {
        $stmt = self::db()->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = :user_id AND is_read = 0");
        $stmt->execute([':user_id' => $userId]);
    }
}
