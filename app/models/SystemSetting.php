<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Logger.php';

class SystemSetting {
    public static function all() {
        $db = Database::connect();
        $stmt = $db->query("SELECT * FROM system_settings ORDER BY key");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $row) {
            $map[$row['key']] = [
                'value' => $row['value'],
                'description' => $row['description'] ?? ''
            ];
        }
        return $map;
    }

    public static function updateMany($settings) {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO system_settings (key, value) VALUES (:key, :value)
            ON CONFLICT (key) DO UPDATE SET value = :value");

        foreach ($settings as $key => $value) {
            $stmt->execute(['key' => $key, 'value' => $value]);
            Logger::log('Изменена настройка', "$key = $value");
        }
    }

    public static function get($key, $default = null) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT value FROM system_settings WHERE key = :key LIMIT 1");
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : $default;
    }
}