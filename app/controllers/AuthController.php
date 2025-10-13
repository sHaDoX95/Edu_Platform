<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../core/Logger.php';

class AuthController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = User::findByEmail($email);
            if ($user) {
                if (!empty($user['blocked']) && $user['blocked'] == true) {
                    Logger::log('Попытка входа заблокированного пользователя', "ID: {$user['id']}, Email: {$user['email']}");
                    $error = "Ваш аккаунт заблокирован. Обратитесь к администратору.";
                    require_once __DIR__ . '/../views/auth/login.php';
                    return;
                }

                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    Logger::log('Вход в систему', "ID: {$user['id']}, Email: {$user['email']}");

                    if ($user['role'] === 'teacher') {
                        header("Location: /teacher");
                    } elseif ($user['role'] === 'admin') {
                        header("Location: /admin");
                    } else {
                        header("Location: /user");
                    }
                    exit;
                }
            }

            Logger::log('Неудачная попытка входа', "Email: {$email}");
            $error = "Неверный логин или пароль";
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
            $userId = User::create($name, $email, $hashedPassword); // поправил порядок параметров
            Logger::log('Регистрация пользователя', "ID: {$userId}, Email: {$email}, Имя: {$name}");

            $_SESSION['user_id'] = $userId;

            $user = User::find($userId);

            if ($user['role'] === 'teacher') {
                header("Location: /teacher");
            } elseif ($user['role'] === 'admin') {
                header("Location: /admin");
            } else {
                header("Location: /user");
            }
            exit;
        }

        require_once __DIR__ . '/../views/auth/register.php';
    }

    public function logout() {
        $user = User::find($_SESSION['user_id'] ?? 0);
        if ($user) {
            Logger::log('Выход из системы', "ID: {$user['id']}, Email: {$user['email']}");
        }
        session_destroy();
        header('Location: /auth/login');
        exit;
    }
}
