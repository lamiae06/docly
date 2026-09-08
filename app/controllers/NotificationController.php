<?php
namespace App\Controllers;

use App\Models\Notification;

class NotificationController extends Controller {

    public function index(): void {
        $userId = $_SESSION['user_id'] ?? 0;
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = :id ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([':id' => $userId]);

        $this->view('notifications.index', [
            'notifications' => $stmt->fetchAll(),
            'pageTitle' => 'Notifications',
        ]);
    }

    public function apiIndex(): void {
        $userId = $_SESSION['user_id'] ?? 0;
        $db = $GLOBALS['db'];
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = :id ORDER BY created_at DESC LIMIT 15");
        $stmt->execute([':id' => $userId]);
        $this->json(['data' => $stmt->fetchAll()]);
    }

    public function apiMarkRead(int $id): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }
        Notification::markAsRead($id);
        $this->json(['success' => true]);
    }

    public function apiMarkAllRead(): void {
        if (!$this->isMethod('POST')) {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }
        Notification::markAllAsRead($_SESSION['user_id'] ?? 0);
        $this->json(['success' => true]);
    }

    public function apiUnreadCount(): void {
        $this->json(['count' => Notification::unreadCount($_SESSION['user_id'] ?? 0)]);
    }
}
