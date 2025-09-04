<?php
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../core/Auth.php';

class TeacherController {
    public function index() {
        Auth::requireLogin();
        $user = Auth::user();

        if (!$user || $user['role'] !== 'teacher') {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        $teacherId = $user['id'];
        $courses = Course::findByTeacher($teacherId);

        require_once __DIR__ . '/../views/teacher/index.php';
    }

    public function create() {
        Auth::requireLogin();
        $user = Auth::user();

        if (!$user || $user['role'] !== 'teacher') {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        require_once __DIR__ . '/../views/teacher/create.php';
    }

    public function store() {
        Auth::requireLogin();
        $user = Auth::user();

        if (!$user || $user['role'] !== 'teacher') {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';

        if ($title && $description) {
            Course::create($title, $description, $user['id']);
            header("Location: /teacher");
            exit;
        }

        echo "Ошибка: все поля должны быть заполнены";
    }

    public function edit() {
        Auth::requireLogin();
        $user = Auth::user();

        if (!$user || $user['role'] !== 'teacher') {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "Ошибка: не указан ID курса";
            return;
        }

        $course = Course::find($id);

        if (!$course || $course['teacher_id'] != $user['id']) {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        require_once __DIR__ . '/../views/teacher/edit.php';
    }

    public function update() {
        Auth::requireLogin();
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
    
            if (empty($id) || empty($title) || empty($description)) {
                die("Ошибка: все поля должны быть заполнены!");
            }
    
            Course::update($id, $title, $description);
    
            header("Location: /teacher");
            exit;
        }
    }

    public function delete() {
        Auth::requireLogin();
        $user = Auth::user();
    
        if (!$user || $user['role'] !== 'teacher') {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }
    
        $id = $_GET['id'] ?? null;
    
        if (empty($id)) {
            die("Ошибка: не указан ID курса!");
        }
    
        $course = Course::find($id);
        if (!$course || $course['teacher_id'] != $user['id']) {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }
    
        Course::delete($id);
    
        header("Location: /teacher");
        exit;
    }
}