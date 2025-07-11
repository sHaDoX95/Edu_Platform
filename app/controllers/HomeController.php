<?php
require_once __DIR__ . '/../models/Course.php';

class HomeController {
    public function index() {
        Auth::requireLogin();
        $user = Auth::user();

        $courses = Course::allWithUserProgress($user['id']);

        require_once __DIR__ . '/../views/home.php';
    }
}