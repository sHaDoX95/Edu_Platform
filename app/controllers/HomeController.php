<?php
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Progress.php';
require_once __DIR__ . '/../models/Lesson.php';

class HomeController {
    public function index() {
        Auth::requireLogin();
        $user = Auth::user();

        $courses = Course::allWithUserProgress($user['id']);

        require_once __DIR__ . '/../views/home.php';
    }
}