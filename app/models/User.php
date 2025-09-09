<?php
require_once __DIR__ . '/../core/Database.php';

class User {
    public static function create($email, $password, $name, $role = 'student') {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO users (email, password, name, role) VALUES (:email, :password, :name, :role)");
        $stmt->execute([
            'email' => $email,
            'password' => $password,
            'name' => $name,
            'role' => $role
        ]);
        return $pdo->lastInsertId();
    }

    public static function findByEmail($email) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}