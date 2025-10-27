<?php
require_once __DIR__ . '/../controllers/TeacherController.php';

class Router {
    public function route($uri) {
        $path = parse_url($uri, PHP_URL_PATH);
        $parts = explode('/', trim($path, '/'));

        // --- Admin support ---
        if (isset($parts[0]) && $parts[0] === 'admin' && isset($parts[1]) && $parts[1] === 'support') {
            require_once __DIR__ . '/../controllers/AdminSupportController.php';
            $ctrl = new AdminSupportController();
            $action = $parts[2] ?? 'index';
            if (method_exists($ctrl, $action)) {
                $ctrl->$action();
                return;
            }
            http_response_code(404);
            echo "Метод не найден";
            return;
        }

        // --- Yandex OAuth ---
        if (isset($parts[0]) && $parts[0] === 'auth' && isset($parts[1]) && $parts[1] === 'yandex') {
            require_once __DIR__ . '/../controllers/AuthController.php';
            $ctrl = new AuthController();
            $action = $parts[2] ?? 'login';

            if ($action === 'login' && method_exists($ctrl, 'yandexLogin')) {
                $ctrl->yandexLogin();
                return;
            } elseif ($action === 'callback' && method_exists($ctrl, 'yandexCallback')) {
                $ctrl->yandexCallback();
                return;
            }

            http_response_code(404);
            echo "Метод Yandex не найден";
            return;
        }

        // --- Обычные контроллеры ---
        $controller = $parts[0] ?? 'user';
        $action = $parts[1] ?? 'index';

        $controllerFile = __DIR__ . "/../controllers/" . ucfirst($controller) . "Controller.php";

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $className = ucfirst($controller) . "Controller";
            $ctrl = new $className();
            if (method_exists($ctrl, $action)) {
                $ctrl->$action();
                return;
            }
        }

        http_response_code(404);
        echo "Страница не найдена";
    }
}