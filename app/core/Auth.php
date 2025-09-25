<?php

class Auth {
    public static function user() {
        if (!empty($_SESSION['user_id'])) {
            $pdo = Database::connect();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && !empty($user['blocked']) && $user['blocked'] == true) {
                session_destroy();
                return null;
            }

            return $user;
        }
        return null;
    }

    public static function check() {
        $user = self::user();
        return $user !== null;
    }

    public static function requireLogin() {
        if (!self::check()) {
            header('Location: /auth/login');
            exit;
        }
    }

    public static function requireRole($role) {
        $user = self::user();
        if (!$user || $user['role'] !== $role) {
            header("Location: /auth/login");
            exit;
        }
    }    
}