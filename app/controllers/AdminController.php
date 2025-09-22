<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../core/Database.php';

class AdminController {
    public function index() {
        Auth::requireLogin();
        Auth::requireRole('admin');

        $db = Database::connect();

        $usersCount = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $coursesCount = (int)$db->query("SELECT COUNT(*) FROM courses")->fetchColumn();
        $lessonsCount = (int)$db->query("SELECT COUNT(*) FROM lessons")->fetchColumn();
        $ticketsCount = (int)$db->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'open'")->fetchColumn();
        $teachersCount = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();

        require_once __DIR__ . '/../views/admin/index.php';
    }

    public function users() {
        Auth::requireLogin();
        Auth::requireRole('admin');

        $users = User::all();
        $teachers = User::getTeachers();
        $unassignedStudents = User::getUnassignedStudents();

        require_once __DIR__ . '/../views/admin/users.php';
    }

    public function storeUser() {
        Auth::requireLogin();
        Auth::requireRole('admin');

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'student';

        if (!$name || !$email || !$password) {
            $_SESSION['flash_error'] = "Заполните все поля";
            header("Location: /admin/users");
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        User::create($name, $email, $hash, $role);

        header("Location: /admin/users");
        exit;
    }

    public function updateUser() {
        Auth::requireLogin();
        Auth::requireRole('admin');

        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$id || !$name || !$email) {
            $_SESSION['flash_error'] = "Заполните обязательные поля";
            header("Location: /admin/users");
            exit;
        }

        $hash = null;
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
        }

        User::update($id, $name, $email, $role, $hash);

        header("Location: /admin/users");
        exit;
    }

    public function deleteUser() {
        Auth::requireLogin();
        Auth::requireRole('admin');

        $id = $_GET['id'] ?? null;
        if ($id) {
            User::delete($id);
        }
        header("Location: /admin/users");
        exit;
    }

    public function attachStudent() {
        Auth::requireLogin();
        Auth::requireRole('admin');

        $student = $_POST['student_id'] ?? null;
        $teacher = $_POST['teacher_id'] ?? null;
        if ($student && $teacher) {
            User::assignStudentToTeacher($student, $teacher);
        }
        header("Location: /admin/users");
        exit;
    }

    public function detachStudent() {
        Auth::requireLogin();
        Auth::requireRole('admin');

        $student = $_POST['student_id'] ?? null;
        $teacher = $_POST['teacher_id'] ?? null;
        if ($student && $teacher) {
            User::detachStudentFromTeacher($student, $teacher);
        }
        header("Location: /admin/users");
        exit;
    }
}