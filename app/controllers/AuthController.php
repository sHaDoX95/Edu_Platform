<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../core/Logger.php';
require_once __DIR__ . '/../models/SystemSetting.php';

class AuthController
{
    public function login()
    {
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

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (SystemSetting::get('registration_enabled', 'true') !== 'true') {
                $error = "Регистрация временно закрыта";
                require_once __DIR__ . '/../views/auth/register.php';
                return;
            }

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

    public function logout()
    {
        $user = User::find($_SESSION['user_id'] ?? 0);
        if ($user) {
            Logger::log('Выход из системы', "ID: {$user['id']}, Email: {$user['email']}");
        }
        session_destroy();
        header('Location: /auth/login');
        exit;
    }

    private $client_id = '65a76be2b4e743479ce7ffc0cb750bd8';
    private $client_secret = '9784a60efdfd40389a52290f43f36f4c';
    private $redirect_uri = 'https://localhost:8443/auth/yandex/callback';

    public function yandexLogin()
    {
        $url = 'https://oauth.yandex.com/authorize?response_type=code'
            . '&client_id=' . $this->client_id
            . '&redirect_uri=' . urlencode($this->redirect_uri);
        header('Location: ' . $url);
        exit;
    }

    public function yandexCallback()
    {
        if (!isset($_GET['code'])) {
            die('Ошибка авторизации');
        }

        $code = $_GET['code'];

        $token = $this->getAccessToken($code);
        if (!$token) die('Не удалось получить токен');

        $userInfo = $this->getUserInfo($token);
        if (!$userInfo) die('Не удалось получить информацию о пользователе');

        $email = $userInfo['default_email'] ?? '';
        $user = User::findByEmail($email);

        if ($user) {
            Logger::log('Вход пользователя через Yandex OAuth', "ID: {$user['id']}, Email: {$user['email']}");
        } else {
            $userId = User::createFromOAuth($userInfo['display_name'] ?? $userInfo['first_name'], $email);
            $user = User::find($userId);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        header('Location: /user');
        exit;
    }

    private function getAccessToken($code)
    {
        $ch = curl_init('https://oauth.yandex.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        ]));
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

    private function getUserInfo($token)
    {
        $ch = curl_init('https://login.yandex.ru/info?format=json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: OAuth ' . $token]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
}
