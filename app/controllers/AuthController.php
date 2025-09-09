<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = User::findByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                if ($user['role'] === 'teacher') {
                    header("Location: /teacher");
                } else {
                    header("Location: /user");
                }
                exit;
            } else {
                $error = "Неверный логин или пароль";
            }
        }

        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $password_confirm = $_POST['password_confirm'];
            $name = $_POST['name'];

            if ($password !== $password_confirm) {
                $error = "Пароли не совпадают!";
                require_once __DIR__ . '/../views/auth/register.php';
                return;
            }

            if (User::findByEmail($email)) {
                $error = "Пользователь с таким email уже существует!";
                require_once __DIR__ . '/../views/auth/register.php';
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $userId = User::create($email, $hashedPassword, $name);
            $_SESSION['user_id'] = $userId;

            $pdo = Database::connect();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user['role'] === 'teacher') {
                header("Location: /teacher");
            } else {
                header("Location: /user");
            }
            exit;
        }

        require_once __DIR__ . '/../views/auth/register.php';
    }

    public function logout() {
        session_destroy();
        header('Location: /auth/login');
        exit;
    }
}
