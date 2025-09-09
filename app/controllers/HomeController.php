<?php
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Progress.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/Test.php';
require_once __DIR__ . '/../core/Auth.php';

class HomeController {
    public function index() {
        Auth::requireLogin();
        $user = Auth::user();

        $courses = Course::allWithUserProgress($user['id']);

        if ($user['role'] === 'teacher') {
            header("Location: /teacher");
            exit;
        }

        $courses = Course::all();
        require_once __DIR__ . '/../views/home.php';
    }
}