<?php
require_once __DIR__ . '/../core/Database.php';

class User {
    public static function findByEmail($email) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($email, $password, $name) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO users (email, password, name) VALUES (:email, :password, :name)");
        $stmt->execute([
            'email' => $email,
            'password' => $password,
            'name' => $name
        ]);
    }
}
