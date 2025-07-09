<?php

class Auth {
    public static function user() {
        if (!empty($_SESSION['user_id'])) {
            $pdo = Database::connect();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    public static function check() {
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin() {
        if (!self::check()) {
            header('Location: /auth/login');
            exit;
        }
    }
}