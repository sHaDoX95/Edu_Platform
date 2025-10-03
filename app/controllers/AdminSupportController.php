<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';

class AdminSupportController {
    public function index() {
        $user = Auth::user();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo "Доступ запрещён";
            exit;
        }

        $db = Database::connect();
        $tickets = $db->query("SELECT t.*, u.name as user_name 
                               FROM tickets t 
                               JOIN users u ON u.id = t.user_id 
                               ORDER BY t.updated_at DESC")
                      ->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/admin/support/index.php';
    }

    public function view() {
        $user = Auth::user();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo "Доступ запрещён";
            exit;
        }

        $ticket_id = $_GET['id'] ?? null;
        if (!$ticket_id) {
            http_response_code(400);
            echo "Не указан ID тикета";
            exit;
        }

        $db = Database::connect();
        $stmt = $db->prepare("SELECT t.*, u.name as user_name 
                              FROM tickets t 
                              JOIN users u ON u.id = t.user_id 
                              WHERE t.id = ?");
        $stmt->execute([$ticket_id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        $repliesStmt = $db->prepare("SELECT r.*, u.name 
                                     FROM ticket_replies r 
                                     JOIN users u ON u.id = r.user_id 
                                     WHERE ticket_id = ? 
                                     ORDER BY r.created_at ASC");
        $repliesStmt->execute([$ticket_id]);
        $replies = $repliesStmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/admin/support/view.php';
    }

    public function reply() {
        $user = Auth::user();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo "Доступ запрещён";
            exit;
        }

        $ticket_id = $_POST['ticket_id'];
        $message = $_POST['message'];

        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$ticket_id, $user['id'], $message]);

        $db->prepare("UPDATE tickets SET updated_at = NOW() WHERE id = ?")->execute([$ticket_id]);

        header("Location: /admin/support/view?id=" . $ticket_id);
    }
}