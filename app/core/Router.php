<?php
require_once __DIR__ . '/../controllers/TeacherController.php';

class Router
{
    public function route($uri)
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $parts = explode('/', trim($path, '/'));

        // --- Support routes ---
        if (isset($parts[0]) && $parts[0] === 'support') {
            require_once __DIR__ . '/../controllers/SupportController.php';
            $ctrl = new SupportController();

            if (isset($parts[1]) && $parts[1] === 'get-replies') {
                $ctrl->getReplies();
                return;
            }

            if (isset($parts[1]) && $parts[1] === 'view') {
                $ctrl->view();
                return;
            }

            if (isset($parts[1]) && $parts[1] === 'create') {
                $ctrl->store();
                return;
            }

            if (isset($parts[1]) && $parts[1] === 'reply') {
                $ctrl->reply();
                return;
            }

            $ctrl->index();
            return;
        }

        if (isset($parts[0]) && $parts[0] === 'admin' && isset($parts[1]) && $parts[1] === 'support') {
            require_once __DIR__ . '/../controllers/AdminSupportController.php';
            $ctrl = new AdminSupportController();

            if (isset($parts[2]) && $parts[2] === 'get-replies') {
                $ctrl->getReplies();
                return;
            }

            $action = $parts[2] ?? 'index';
            if (method_exists($ctrl, $action)) {
                $ctrl->$action();
                return;
            }
            http_response_code(404);
            echo "Метод не найден";
            return;
        }

        // --- Chat routes ---
        if (isset($parts[0]) && $parts[0] === 'chat') {
            require_once __DIR__ . '/../controllers/ChatController.php';
            $ctrl = new ChatController();

            if (isset($parts[1]) && $parts[1] === 'send') {
                $ctrl->sendMessage();
                return;
            }

            if (isset($parts[1]) && $parts[1] === 'messages' && isset($parts[2])) {
                $ctrl->getMessages($parts[2]);
                return;
            }

            if (isset($parts[1]) && $parts[1] === 'view' && isset($parts[2])) {
                $ctrl->view($parts[2]);
                return;
            }

            $ctrl->index();
            return;
        }

        // --- Admin chats routes ---
        if (isset($parts[0]) && $parts[0] === 'admin' && isset($parts[1]) && $parts[1] === 'chats') {
            require_once __DIR__ . '/../controllers/ChatController.php';
            $ctrl = new ChatController();

            if (isset($parts[2]) && $parts[2] === 'store') {
                $ctrl->store();
                return;
            }

            if (isset($parts[2]) && $parts[2] === 'edit' && isset($parts[3])) {
                $ctrl->edit($parts[3]);
                return;
            }

            if (isset($parts[2]) && $parts[2] === 'update' && isset($parts[3])) {
                $ctrl->update($parts[3]);
                return;
            }

            if (isset($parts[2]) && $parts[2] === 'delete') {
                $ctrl->delete();
                return;
            }

            if (isset($parts[2]) && $parts[2] === 'updateTitle') {
                $ctrl->updateTitle();
                return;
            }

            if (isset($parts[2]) && $parts[2] === 'updateTeacher') {
                $ctrl->updateTeacher();
                return;
            }

            $ctrl->index();
            return;
        }

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
