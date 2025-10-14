<?php
require_once __DIR__ . '/../models/SystemSetting.php';
require_once __DIR__ . '/../core/Auth.php';

class AdminSettingsController {
    public function index() {
        $user = Auth::user();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = $_POST['settings'] ?? [];
            SystemSetting::updateMany($settings);
            header("Location: /admin/settings?success=1");
            exit;
        }

        $settingsMap = SystemSetting::all();
        require __DIR__ . '/../views/admin/settings.php';
    }
}
