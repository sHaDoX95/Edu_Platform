<?php

class Router {
    public function route($uri) {
        $path = parse_url($uri, PHP_URL_PATH);
        $parts = explode('/', trim($path, '/'));

        $controller = $parts[0] ?? 'home';
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