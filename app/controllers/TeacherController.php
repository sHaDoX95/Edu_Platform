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

    public function lessons() {
        Auth::requireLogin();
        $user = Auth::user();

        if (!$user || $user['role'] !== 'teacher') {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        $courseId = $_GET['course_id'] ?? null;
        if (!$courseId) {
            echo "Ошибка: курс не найден";
            return;
        }

        $lessons = Lesson::findWithCourse($courseId);
        require_once __DIR__ . '/../views/teacher/lessons/index.php';
    }

    public function createLesson() {
        Auth::requireLogin();
        $user = Auth::user();

        if (!$user || $user['role'] !== 'teacher') {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        $courseId = $_GET['course_id'] ?? null;
        require_once __DIR__ . '/../views/teacher/lessons/create.php';
    }

    public function storeLesson() {
        Auth::requireLogin();
        $user = Auth::user();

        if (!$user || $user['role'] !== 'teacher') {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        $courseId = $_POST['course_id'] ?? null;
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        if ($courseId && $title && $content) {
            Lesson::create($courseId, $title, $content);
            header("Location: /teacher/lessons?course_id=$courseId");
            exit;
        }

        echo "Ошибка: все поля должны быть заполнены";
    }

    public function editLesson() {
        Auth::requireLogin();
        $user = Auth::user();

        if (!$user || $user['role'] !== 'teacher') {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        $lessonId = $_GET['id'] ?? null;

        if (!$lessonId) {
            echo "Ошибка: не указан урок";
            return;
        }

        require_once __DIR__ . '/../models/Lesson.php';
        $lesson = Lesson::findWithCourse($lessonId);

        if (!$lesson) {
            echo "Ошибка: урок не найден";
            return;
        }

        if ($lesson['teacher_id'] != $user['id']) {
            http_response_code(403);
            echo "Доступ запрещён: вы не владелец курса";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';

            if ($title && $content) {
                Lesson::update($lessonId, $title, $content);
                header("Location: /course/show?id=" . $lesson['course_id']);
                exit;
            } else {
                $error = "Заполните все поля";
            }
        }

        require_once __DIR__ . '/../views/lesson/edit.php';
    }

    public function updateLesson() {
        Auth::requireLogin();
        $user = Auth::user();

        if (!$user || $user['role'] !== 'teacher') {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        $id = $_POST['id'] ?? null;
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        if ($id && $title && $content) {
            Lesson::update($id, $title, $content);
            header("Location: /teacher/lessons?course_id=" . $_POST['course_id']);
            exit;
        }

        echo "Ошибка: все поля должны быть заполнены";
    }

    public function deleteLesson() {
        Auth::requireLogin();
        $user = Auth::user();

        if (!$user || $user['role'] !== 'teacher') {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        $lessonId = $_GET['id'] ?? null;

        if (!$lessonId) {
            echo "Ошибка: не указан урок";
            return;
        }

        require_once __DIR__ . '/../models/Lesson.php';
        $lesson = Lesson::findWithCourse($lessonId);

        if (!$lesson) {
            echo "Ошибка: урок не найден";
            return;
        }

        if ($lesson['teacher_id'] != $user['id']) {
            http_response_code(403);
            echo "Доступ запрещён: вы не владелец курса";
            return;
        }

        Lesson::delete($lessonId);
        header("Location: /course/show?id=" . $lesson['course_id']);
        exit;
    }

    public function courses() {
        Auth::requireRole('teacher');

        $teacherId = Auth::user()['id'];
        $courses = Course::findByTeacher($teacherId);

        require __DIR__ . '/../views/teacher/courses.php';
    }
}