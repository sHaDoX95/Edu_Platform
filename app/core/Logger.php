<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

class Logger
{
    public static function log(string $action, ?string $details = null, ?int $userId = null)
    {
        $db = Database::connect();
        
        if ($userId === null) {
            $user = Auth::user();
            $userId = $user['id'] ?? null;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $stmt = $db->prepare("
            INSERT INTO system_logs (user_id, action, details, ip) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $action, $details, $ip]);
    }
}
