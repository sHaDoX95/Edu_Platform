<?php
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Progress.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/Test.php';
require_once __DIR__ . '/../core/Auth.php';

class UserController {
    public function index() {
        Auth::requireLogin();
        $user = Auth::user();

        if (!$user || $user['role'] !== 'student') {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }
        
        $courses = Course::allWithUserProgress($user['id']);

        require_once __DIR__ . '/../views/user/index.php';
    }
}