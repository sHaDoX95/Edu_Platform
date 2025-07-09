<?php
require_once __DIR__ . '/../models/Course.php';

class CourseController {
    public function index() {
        Auth::requireLogin();

        $courses = Course::all();
        require_once __DIR__ . '/../views/course/index.php';
    }

    public function show() {
        Auth::requireLogin();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "Курс не найден";
            return;
        }

        $course = Course::findWithLessons($id);
        require_once __DIR__ . '/../views/course/show.php';
    }
}
