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

        $role = $_GET['role'] ?? null;
        $status = $_GET['status'] ?? null;
        $q = trim($_GET['q'] ?? '');

        $users = User::filter($role, $status, $q);
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
        $role = $_POST['role'] ?? null;
        $blocked = isset($_POST['blocked']) ? (int)$_POST['blocked'] : 0;

        if (!$id || !$role) {
            $_SESSION['flash_error'] = "Невозможно обновить пользователя";
            header("Location: /admin/users");
            exit;
        }

        User::updateRoleAndStatus($id, $role, $blocked);

        header("Location: /admin/users");
        exit;
    }

    public function deleteUser() {
        Auth::requireLogin();
        Auth::requireRole('admin');

        $id = $_POST['id'] ?? null;
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

    public function dashboard() {
        Auth::requireRole('admin');
        
        $db = Database::connect();
        
        $stats = [
            'users' => [
                'total' => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                'students' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
                'teachers' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn(),
                'admins' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(),
                'active' => (int)$db->query("SELECT COUNT(*) FROM users WHERE blocked = false")->fetchColumn(),
                'blocked' => (int)$db->query("SELECT COUNT(*) FROM users WHERE blocked = true")->fetchColumn()
            ],
            'courses' => [
                'total' => (int)$db->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
                'with_lessons' => (int)$db->query("SELECT COUNT(DISTINCT course_id) FROM lessons")->fetchColumn(),
                'active' => (int)$db->query("SELECT COUNT(*) FROM courses WHERE teacher_id IS NOT NULL")->fetchColumn()
            ],
            'lessons' => [
                'total' => (int)$db->query("SELECT COUNT(*) FROM lessons")->fetchColumn(),
                'with_tests' => (int)$db->query("SELECT COUNT(DISTINCT lesson_id) FROM questions")->fetchColumn()
            ],
            'progress' => [
                'completed_lessons' => (int)$db->query("SELECT COUNT(*) FROM lesson_progress")->fetchColumn(),
                'test_attempts' => (int)$db->query("SELECT COUNT(*) FROM lesson_progress WHERE test_score IS NOT NULL")->fetchColumn()
            ]
        ];
        
        $recentActions = $db->query("
            SELECT u.name, sl.action, sl.details, sl.created_at 
            FROM system_logs sl 
            LEFT JOIN users u ON u.id = sl.user_id 
            ORDER BY sl.created_at DESC 
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $openTickets = $db->query("
            SELECT COUNT(*) as count FROM support_tickets WHERE status = 'open'
        ")->fetchColumn();

        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    public function coursesManagement() {
        Auth::requireRole('admin');
        
        $db = Database::connect();
        
        $courses = $db->query("
            SELECT c.*, u.name as teacher_name, 
                   COUNT(l.id) as lessons_count,
                   COUNT(DISTINCT lp.user_id) as students_count
            FROM courses c
            LEFT JOIN users u ON u.id = c.teacher_id
            LEFT JOIN lessons l ON l.course_id = c.id
            LEFT JOIN lesson_progress lp ON lp.lesson_id = l.id
            GROUP BY c.id, u.name
            ORDER BY c.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $teachers = $db->query("SELECT id, name FROM users WHERE role = 'teacher'")->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/admin/courses.php';
    }

    public function createCourse() {
        Auth::requireRole('admin');
        
        if ($_POST) {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $teacherId = $_POST['teacher_id'] ?? null;
            
            if ($title && $description) {
                Course::create($title, $description, $teacherId);
                $this->logAction("Создан курс: $title");
                header("Location: /admin/courses");
                exit;
            }
        }
        
        header("Location: /admin/courses?error=empty_fields");
        exit;
    }

    public function updateCourse() {
        Auth::requireRole('admin');
        
        $id = $_POST['id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $teacherId = $_POST['teacher_id'] ?? null;
        
        if ($id && $title && $description) {
            Course::update($id, $title, $description, $teacherId);
            $this->logAction("Обновлен курс ID: $id");
            header("Location: /admin/courses");
            exit;
        }
        
        header("Location: /admin/courses?error=update_failed");
        exit;
    }

    public function deleteCourse() {
        Auth::requireRole('admin');
        
        $id = $_GET['id'] ?? null;
        if ($id) {
            Course::delete($id);
            $this->logAction("Удален курс ID: $id");
        }
        
        header("Location: /admin/courses");
        exit;
    }

    public function lessonsManagement() {
        Auth::requireRole('admin');
        
        $courseId = $_GET['course_id'] ?? null;
        $db = Database::connect();
        
        if ($courseId) {
            $lessons = $db->prepare("
                SELECT l.*, c.title as course_title,
                       (SELECT COUNT(*) FROM questions q WHERE q.lesson_id = l.id) as questions_count
                FROM lessons l
                JOIN courses c ON c.id = l.course_id
                WHERE l.course_id = ?
                ORDER BY l.id
            ");
            $lessons->execute([$courseId]);
            $lessons = $lessons->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $lessons = $db->query("
                SELECT l.*, c.title as course_title,
                       (SELECT COUNT(*) FROM questions q WHERE q.lesson_id = l.id) as questions_count
                FROM lessons l
                JOIN courses c ON c.id = l.course_id
                ORDER BY l.course_id, l.id
            ")->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $courses = $db->query("SELECT id, title FROM courses ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/admin/lessons.php';
    }

    public function supportTickets() {
        Auth::requireRole('admin');
        
        $db = Database::connect();
        $status = $_GET['status'] ?? 'open';
        
        $where = "WHERE status = ?";
        $params = [$status];
        
        if ($status === 'all') {
            $where = "";
            $params = [];
        }
        
        $tickets = $db->prepare("
            SELECT st.*, u.name as user_name, u.email as user_email
            FROM support_tickets st
            LEFT JOIN users u ON u.id = st.user_id
            $where
            ORDER BY st.created_at DESC
        ");
        $tickets->execute($params);
        $tickets = $tickets->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/admin/support.php';
    }

    public function updateTicketStatus() {
        Auth::requireRole('admin');
        
        $ticketId = $_POST['ticket_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $response = trim($_POST['response'] ?? '');
        
        if ($ticketId && $status) {
            $db = Database::connect();
            $stmt = $db->prepare("UPDATE support_tickets SET status = ? WHERE id = ?");
            $stmt->execute([$status, $ticketId]);
            
            if ($response) {
                $this->logAction("Обновлен тикет ID: $ticketId, статус: $status");
            }
        }
        
        header("Location: /admin/support");
        exit;
    }

    public function systemSettings() {
        Auth::requireRole('admin');
        
        $db = Database::connect();
        
        if ($_POST) {
            foreach ($_POST['settings'] as $key => $value) {
                $stmt = $db->prepare("
                    INSERT INTO system_settings (key, value) 
                    VALUES (?, ?) 
                    ON CONFLICT (key) DO UPDATE SET value = EXCLUDED.value
                ");
                $stmt->execute([$key, $value]);
            }
            
            $this->logAction("Обновлены настройки системы");
            header("Location: /admin/settings?success=1");
            exit;
        }
        
        $settings = $db->query("SELECT key, value, description FROM system_settings")->fetchAll(PDO::FETCH_ASSOC);
        $settingsMap = [];
        foreach ($settings as $setting) {
            $settingsMap[$setting['key']] = $setting;
        }

        require_once __DIR__ . '/../views/admin/settings.php';
    }

    public function systemLogs() {
        Auth::requireRole('admin');
        
        $db = Database::connect();
        $page = max(1, $_GET['page'] ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $total = $db->query("SELECT COUNT(*) FROM system_logs")->fetchColumn();
        $totalPages = ceil($total / $perPage);
        
        $logs = $db->prepare("
            SELECT sl.*, u.name as user_name 
            FROM system_logs sl 
            LEFT JOIN users u ON u.id = sl.user_id 
            ORDER BY sl.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $logs->execute([$perPage, $offset]);
        $logs = $logs->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/admin/logs.php';
    }

    public function userDetail($userId) {
        Auth::requireRole('admin');
        
        $db = Database::connect();
        
        $user = User::find($userId);
        if (!$user) {
            header("Location: /admin/users");
            exit;
        }
        
        $progress = $db->prepare("
            SELECT c.title as course_title, l.title as lesson_title,
                   lp.completed_at, lp.test_score, lp.test_passed
            FROM lesson_progress lp
            JOIN lessons l ON l.id = lp.lesson_id
            JOIN courses c ON c.id = l.course_id
            WHERE lp.user_id = ?
            ORDER BY lp.completed_at DESC
        ");
        $progress->execute([$userId]);
        $progress = $progress->fetchAll(PDO::FETCH_ASSOC);
        
        $userCourses = $db->prepare("
            SELECT c.* FROM courses c
            JOIN user_courses uc ON uc.course_id = c.id
            WHERE uc.user_id = ?
        ");
        $userCourses->execute([$userId]);
        $userCourses = $userCourses->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/admin/user_detail.php';
    }

    public function blockUser() {
        Auth::requireRole('admin');
        
        $userId = $_POST['user_id'] ?? null;
        $reason = trim($_POST['reason'] ?? '');
        
        if ($userId) {
            $db = Database::connect();
            $stmt = $db->prepare("UPDATE users SET blocked = true WHERE id = ?");
            $stmt->execute([$userId]);
            
            $this->logAction("Заблокирован пользователь ID: $userId. Причина: $reason");
        }
        
        header("Location: /admin/users");
        exit;
    }

    public function unblockUser() {
        Auth::requireRole('admin');
        
        $userId = $_POST['user_id'] ?? null;
        
        if ($userId) {
            $db = Database::connect();
            $stmt = $db->prepare("UPDATE users SET blocked = false WHERE id = ?");
            $stmt->execute([$userId]);
            
            $this->logAction("Разблокирован пользователь ID: $userId");
        }
        
        header("Location: /admin/users");
        exit;
    }

    public function sendNotification() {
        Auth::requireRole('admin');
        
        $userId = $_POST['user_id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $type = $_POST['type'] ?? 'info';
        
        if ($userId && $title && $message) {
            $db = Database::connect();
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, title, message, type) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $title, $message, $type]);
            
            $this->logAction("Отправлено уведомление пользователю ID: $userId");
        }
        
        header("Location: /admin/users");
        exit;
    }

    private function logAction($action, $details = '') {
        $db = Database::connect();
        $stmt = $db->prepare("
            INSERT INTO system_logs (user_id, action, details, ip_address) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            Auth::user()['id'] ?? null,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
}