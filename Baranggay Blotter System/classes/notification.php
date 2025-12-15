<?php
require_once "database.php";

class Notification extends Database {
    protected $conn;

    public function __construct() {
        $this->conn = $this->connect();
    }

    public function addNotification($userId, $message, $type = 'info') {
        try {
            // Get type_id from notification_type table based on type_code
            $typeStmt = $this->conn->prepare("SELECT type_id FROM notification_type WHERE type_code = ? LIMIT 1");
            $typeStmt->execute([$type]);
            $typeResult = $typeStmt->fetch(PDO::FETCH_ASSOC);
            $typeId = $typeResult['type_id'] ?? 1; // Default to 'info' type if not found
            
            $stmt = $this->conn->prepare("INSERT INTO notifications (user_id, type_id, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
            return $stmt->execute([$userId, $typeId, $message]);
        } catch (Exception $e) {
            error_log("Notification Error: " . $e->getMessage());
            return false;
        }
    }

    public function getNotifications($userId, $limit = 10) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
            $stmt->bindParam(1, $userId, PDO::PARAM_INT);
            $stmt->bindParam(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get Notifications Error: " . $e->getMessage());
            return [];
        }
    }

    public function getUnreadCount($userId) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Get Unread Count Error: " . $e->getMessage());
            return 0;
        }
    }

    public function markAsRead($notificationId) {
        try {
            $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
            return $stmt->execute([$notificationId]);
        } catch (Exception $e) {
            error_log("Mark As Read Error: " . $e->getMessage());
            return false;
        }
    }

    public function markAllAsRead($userId) {
        try {
            $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Mark All As Read Error: " . $e->getMessage());
            return false;
        }
    }
}
