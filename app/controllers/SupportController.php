<?php
require_once __DIR__ . '/../core/Database.php';

class SupportController
{
    public function index()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/support/index.php';
    }

    public function store()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $db = Database::connect();

        if ($user['role'] !== 'admin') {
            $stmt = $db->prepare("SELECT COUNT(*) FROM tickets WHERE user_id = ? AND status IN ('open','in_progress')");
            $stmt->execute([$user['id']]);
            $ticketCount = (int)$stmt->fetchColumn();

            $maxTickets = 3;
            if ($ticketCount >= $maxTickets) {
                $_SESSION['flash_error'] = "Вы достигли максимального количества тикетов ($maxTickets). Удалите старые тикеты или дождитесь ответа администратора.";
                header("Location: /support");
                exit;
            }
        }

        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$subject || !$message) {
            $_SESSION['flash_error'] = "Тема и сообщение обязательны";
            header("Location: /support");
            exit;
        }

        $stmt = $db->prepare("
            INSERT INTO tickets (user_id, subject, message, status, created_at, updated_at) 
            VALUES (?, ?, ?, 'open', NOW(), NOW())
        ");
        $stmt->execute([$user['id'], $subject, $message]);
        $ticketId = $db->lastInsertId();

        $stmt2 = $db->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmt2->execute([$ticketId, $user['id'], $message]);

        $_SESSION['flash_success'] = "Тикет успешно создан";
        header("Location: /support");
        exit;
    }

    public function view()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM tickets WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['id'], $user['id']]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            http_response_code(404);
            echo "Тикет не найден";
            exit;
        }

        $stmt = $db->prepare("
        SELECT r.*, u.name, u.role 
        FROM ticket_replies r 
        JOIN users u ON u.id = r.user_id 
        WHERE ticket_id = ? 
        ORDER BY r.created_at ASC
        ");
        $stmt->execute([$_GET['id']]);
        $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/support/view.php';
    }

    public function reply()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $db = Database::connect();

        $ticket_id = $_POST['ticket_id'] ?? null;
        $message = trim($_POST['message'] ?? '');

        if (!$ticket_id || !$message) {
            $_SESSION['flash_error'] = "Сообщение не может быть пустым";
            header("Location: /support/view?id=" . $ticket_id);
            exit;
        }

        $stmt = $db->prepare("
            SELECT COUNT(*) AS cnt
            FROM ticket_replies
            WHERE ticket_id = ? 
            AND user_id = ?
            AND created_at > NOW() - INTERVAL '1 minute'
        ");
        $stmt->execute([$ticket_id, $user['id']]);
        $recent = (int)$stmt->fetchColumn();

        if ($recent > 0) {
            $_SESSION['flash_error'] = "Вы можете отправлять сообщения не чаще одного раза в минуту";
            header("Location: /support/view?id=" . $ticket_id);
            exit;
        }

        $stmt = $db->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$ticket_id, $user['id'], $message]);

        $stmt = $db->prepare("UPDATE tickets SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$ticket_id]);

        $_SESSION['flash_success'] = "Сообщение успешно отправлено";
        header("Location: /support/view?id=" . $ticket_id);
        exit;
    }
}
