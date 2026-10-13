<?php

namespace App\Helpers;

use App\Config\Database;
use PDO;

class Logger
{
    public static function log(string $action, ?string $entityType = null, ?string $entityId = null, ?string $details = null): void
    {
        try {
            $db = Database::getConnection();
            $userId = Auth::id();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250);

            $stmt = $db->prepare("
                INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
                VALUES (:user_id, :action, :entity_type, :entity_id, :details, :ip, :ua)
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':entity_type' => $entityType,
                ':entity_id' => $entityId,
                ':details' => $details,
                ':ip' => $ip,
                ':ua' => $ua
            ]);
        } catch (\Throwable $e) {
            // Fail silently so logging error doesn't break main business transaction
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
}
