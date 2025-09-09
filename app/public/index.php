<?php
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';

session_start();

$router = new Router();
$router->route($_SERVER['REQUEST_URI']);
