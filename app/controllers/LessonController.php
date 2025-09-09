<?php
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../core/Auth.php';

class LessonController {
    public function create() {
        Auth::requireLogin();
        $user = Auth::user();

        $courseId = $_GET['course_id'] ?? null;
        $course = Course::find($courseId);

        if (!$course || $course['teacher_id'] != $user['id']) {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        require __DIR__ . '/../views/lesson/create.php';
    }

    public function store() {
        Auth::requireLogin();
        $user = Auth::user();

        $courseId = $_POST['course_id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        $course = Course::find($courseId);

        if (!$course || $course['teacher_id'] != $user['id']) {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        if ($title && $content) {
            Lesson::create($courseId, $title, $content);
            header("Location: /course/show?id=" . $courseId);
            exit;
        }

        echo "Ошибка: все поля должны быть заполнены";
    }

    public function edit() {
        Auth::requireLogin();
        $user = Auth::user();

        $id = $_GET['id'] ?? null;
        $lesson = Lesson::find($id);
        $course = Course::find($lesson['course_id']);

        if (!$lesson || $course['teacher_id'] != $user['id']) {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        require __DIR__ . '/../views/lesson/edit.php';
    }

    public function update() {
        Auth::requireLogin();
        $user = Auth::user();

        $id = $_POST['id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        $lesson = Lesson::find($id);
        $course = Course::find($lesson['course_id']);

        if (!$lesson || $course['teacher_id'] != $user['id']) {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        if ($title && $content) {
            Lesson::update($id, $title, $content);
            header("Location: /course/show?id=" . $course['id']);
            exit;
        }

        echo "Ошибка: все поля должны быть заполнены";
    }

    public function delete() {
        Auth::requireLogin();
        $user = Auth::user();

        $id = $_GET['id'] ?? null;
        $lesson = Lesson::find($id);
        $course = Course::find($lesson['course_id']);

        if (!$lesson || $course['teacher_id'] != $user['id']) {
            http_response_code(403);
            echo "Доступ запрещён";
            return;
        }

        Lesson::delete($id);
        header("Location: /course/show?id=" . $course['id']);
        exit;
    }
}