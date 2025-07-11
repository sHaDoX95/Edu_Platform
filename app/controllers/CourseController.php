<?php
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Progress.php';

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
    
        $user = Auth::user();
    
        if (isset($_GET['complete'])) {
            $lessonId = (int)$_GET['complete'];
            Progress::markCompleted($user['id'], $lessonId);
            header("Location: /course/show?id=" . $id);
            exit;
        }

        if (isset($_GET['uncomplete'])) {
            $lessonId = (int)$_GET['uncomplete'];
            Progress::unmarkCompleted($user['id'], $lessonId);
            header("Location: /course/show?id=" . $id);
            exit;
        }
    
        $course = Course::findWithLessons($id);
    
        $completedCount = Progress::countCompleted($user['id'], $id);
        $totalLessons = count($course['lessons']);
    
        require_once __DIR__ . '/../views/course/show.php';
    }
}
