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
        $ticketsCount = (int)$db->query("SELECT COUNT(*) FROM tickets WHERE status = 'open'")->fetchColumn();
        $teachersCount = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();

        require_once __DIR__ . '/../views/admin/index.php';
    }

    public function users() {
        Auth::requireLogin();
        Auth::requireRole('admin');

        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $q = trim($_GET['q'] ?? '');
        
        $limit = 5;
        $currentPage = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($currentPage - 1) * $limit;

        $total = User::countAll($q, $role, $status);
        $pages = ceil($total / $limit);

        $users = User::getAll($q, $role, $status, $limit, $offset);

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

        $created = User::create($name, $email, $hash, $role);

        if (!$created) {
            $_SESSION['flash_error'] = "Пользователь с таким email уже существует";
        } else {
            $_SESSION['flash_success'] = "Пользователь успешно создан";
        }

        header("Location: /admin/users");
        exit;
    }

    public function updateUser() {
        Auth::requireLogin();
        Auth::requireRole('admin');

        $id = $_POST['id'] ?? null;
        $role = $_POST['role'] ?? null;
        $blocked = isset($_POST['blocked']) ? intval($_POST['blocked']) : null; // 0 или 1

        header('Content-Type: application/json'); // ставим JSON сразу

        if (!$id || ($role === null && $blocked === null)) {
            echo json_encode(['success' => false, 'error' => 'Некорректные данные']);
            exit;
        }

        try {
            $db = Database::connect();

            if ($role !== null) {
                $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$role, $id]);
            }

            if ($blocked !== null) {
                $stmt = $db->prepare("UPDATE users SET blocked = ? WHERE id = ?");
                $stmt->execute([$blocked, $id]);
            }

            echo json_encode([
                'success' => true,
                'id' => $id,
                'role' => $role ?? '',
                'blocked' => $blocked ?? ''
            ]);
            exit;

        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    private function isAjax() {
        return (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );
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

    public function courses() {
        Auth::requireRole('admin');
        
        $db = Database::connect();

        $limit = 5;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        $q = trim($_GET['q'] ?? '');
        $teacherId = $_GET['teacher_id'] ?? '';

        $where = [];
        $params = [];

        if ($q !== '') {
            $where[] = "c.title ILIKE ?";
            $params[] = "%$q%";
        }

        if ($teacherId !== '') {
            $where[] = "c.teacher_id = ?";
            $params[] = $teacherId;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmtTotal = $db->prepare("SELECT COUNT(*) FROM courses c $whereSql");
        $stmtTotal->execute($params);
        $total = (int)$stmtTotal->fetchColumn();
        $pages = ceil($total / $limit);

        $sql = "
            SELECT c.*, u.name as teacher_name, 
                (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) as lessons_count,
                (SELECT COUNT(DISTINCT lp.user_id) FROM lesson_progress lp
                    JOIN lessons l ON l.id = lp.lesson_id
                    WHERE l.course_id = c.id) as students_count
            FROM courses c
            LEFT JOIN users u ON u.id = c.teacher_id
            $whereSql
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([...$params, $limit, $offset]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        header('Content-Type: application/json');

        if (!$id || !$title || !$description) {
            echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
            return;
        }

        Course::update($id, $title, $description, $teacherId);

        $this->logAction("Обновлен курс ID: $id, преподаватель: $teacherId");

        echo json_encode(['success' => true]);
    }

    public function updateCourseTeacher() {
        Auth::requireRole('admin');

        $id = $_POST['id'] ?? null;
        $teacherId = $_POST['teacher_id'] ?? null;

        header('Content-Type: application/json');

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
            return;
        }

        Course::updateTeacher($id, $teacherId);

        $this->logAction("Обновлен курс ID: $id, преподаватель: $teacherId");

        echo json_encode(['success' => true]);
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

    public function lessons() {
        Auth::requireRole('admin');

        $db = Database::connect();

        $q = trim($_GET['q'] ?? '');
        $courseId = $_GET['course_id'] ?? '';

        $limit = 10;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        $whereParts = [];
        $params = [];

        if ($q !== '') {
            $whereParts[] = "l.title ILIKE :q";
            $params[':q'] = '%' . $q . '%';
        }

        if ($courseId !== '' && $courseId !== null) {
            $whereParts[] = "l.course_id = :course_id";
            $params[':course_id'] = (int)$courseId;
        }

        $whereSql = $whereParts ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

        $countSql = "SELECT COUNT(*) FROM lessons l $whereSql";
        $countStmt = $db->prepare($countSql);
        foreach ($params as $k => $v) {
            if ($k === ':course_id') $countStmt->bindValue($k, $v, PDO::PARAM_INT);
            else $countStmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();
        $pages = max(1, (int)ceil($total / $limit));

        $sql = "
            SELECT l.*, c.title AS course_title
            FROM lessons l
            LEFT JOIN courses c ON c.id = l.course_id
            $whereSql
            ORDER BY l.id DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            if ($k === ':course_id') $stmt->bindValue($k, $v, PDO::PARAM_INT);
            else $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $courses = $db->query("SELECT id, title FROM courses ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/admin/lessons.php';
    }

    public function createLesson() {
        Auth::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $courseId = $_POST['course_id'] ?? null;

            if (!$title || !$courseId) {
                $_SESSION['flash_error'] = "Заполните название и выберите курс";
                header("Location: /admin/lessons");
                exit;
            }

            $db = Database::connect();
            $stmt = $db->prepare("INSERT INTO lessons (course_id, title, content) VALUES (?, ?, ?)");
            $stmt->execute([(int)$courseId, $title, $content]);

            $this->logAction("Создан урок: $title (курс ID: $courseId)");
        }

        header("Location: /admin/lessons");
        exit;
    }

    public function updateLesson() {
        Auth::requireRole('admin');

        $id = $_POST['id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $courseId = $_POST['course_id'] ?? null;

        if (!$id || !$title || !$courseId) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
                exit;
            }
            $_SESSION['flash_error'] = "Недостаточно данных";
            header("Location: /admin/lessons");
            exit;
        }

        $db = Database::connect();
        $stmt = $db->prepare("UPDATE lessons SET title = ?, content = ?, course_id = ? WHERE id = ?");
        $ok = $stmt->execute([$title, $content, (int)$courseId, (int)$id]);

        $this->logAction("Обновлён урок ID: $id");

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => (bool)$ok, 'id' => $id, 'title' => $title, 'course_title' => $this->getCourseTitle($courseId)]);
            exit;
        }

        header("Location: /admin/lessons");
        exit;
    }

    public function deleteLesson() {
        Auth::requireRole('admin');

        $id = $_GET['id'] ?? null;
        if ($id) {
            $db = Database::connect();
            $stmt = $db->prepare("DELETE FROM lesson_progress WHERE lesson_id = ?");
            $stmt->execute([(int)$id]);

            $stmt = $db->prepare("DELETE FROM lessons WHERE id = ?");
            $stmt->execute([(int)$id]);

            $this->logAction("Удалён урок ID: $id");
        }

        header("Location: /admin/lessons");
        exit;
    }

    private function getCourseTitle($courseId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT title FROM courses WHERE id = ? LIMIT 1");
        $stmt->execute([(int)$courseId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r['title'] ?? '';
    }

    private function jsonSuccess()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    private function jsonError($error)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $error]);
        exit;
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

    public function editUser() {
        Auth::requireLogin();
        Auth::requireRole('admin');

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: /admin/users");
            exit;
        }

        $editUser = User::find($id);
        if (!$editUser) {
            $_SESSION['flash_error'] = "Пользователь не найден";
            header("Location: /admin/users");
            exit;
        }

        require __DIR__ . '/../views/admin/editUser.php';
    }

    public function updateUserData() {
        Auth::requireLogin();
        Auth::requireRole('admin');

        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$id || !$name || !$email) {
            $_SESSION['flash_error'] = "Заполните все поля";
            header("Location: /admin/editUser?id=" . urlencode($id));
            exit;
        }

        $hash = $password ? password_hash($password, PASSWORD_DEFAULT) : null;

        try {
            User::updateData($id, $name, $email, $hash);
            $_SESSION['flash_success'] = "Данные обновлены";
            header("Location: /admin/users");
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                $_SESSION['flash_error'] = "Пользователь с таким email уже существует";
                header("Location: /admin/editUser?id=" . urlencode($id));
            } else {
                throw $e;
            }
        }
        exit;
    }
}