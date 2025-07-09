<?php

class HomeController {
    public function index() {
        Auth::requireLogin();
        $user = Auth::user();
        require_once __DIR__ . '/../views/home.php';
    }
}
