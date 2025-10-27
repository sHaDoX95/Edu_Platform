<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';

class AdminSupportController
{
    public function index()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $db = Database::connect();

        $q = $_GET['q'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $isAdmin = $user['role'] === 'admin';

        $sql = "SELECT t.*, u.name as user_name 
            FROM tickets t 
            JOIN users u ON u.id = t.user_id 
            WHERE 1=1";
        $params = [];

        if (!$isAdmin) {
            $sql .= " AND t.user_id = ?";
            $params[] = $user['id'];
        }

        if ($q !== '') {
            $sql .= " AND t.id = ?";
            $params[] = $q;
        }

        if ($status !== '') {
            $sql .= " AND t.status = ?";
            $params[] = $status;
        }

        $total = 0;
        $pages = 1;
        if ($isAdmin) {
            $countStmt = $db->prepare(str_replace("SELECT t.*, u.name as user_name", "SELECT COUNT(*)", $sql));
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();
            $pages = (int)ceil($total / $perPage);
        }

        $sql .= " ORDER BY t.updated_at DESC LIMIT $perPage OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/admin/support/index.php';
    }

    public function view()
    {
        Auth::requireLogin();
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

        $repliesStmt = $db->prepare("SELECT r.*, u.name, u.role 
                                     FROM ticket_replies r 
                                     JOIN users u ON u.id = r.user_id 
                                     WHERE ticket_id = ? 
                                     ORDER BY r.created_at ASC");
        $repliesStmt->execute([$ticket_id]);
        $replies = $repliesStmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/admin/support/view.php';
    }

    public function reply()
    {
        Auth::requireLogin();
        $user = Auth::user();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo "Доступ запрещён";
            exit;
        }

        $ticket_id = $_POST['ticket_id'];
        $message = $_POST['message'];
        $status = $_POST['status'] ?? 'in_progress';

        $db = Database::connect();

        // Добавляем сообщение
        $stmt = $db->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$ticket_id, $user['id'], $message]);

        // Обновляем статус и время
        $update = $db->prepare("UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$status, $ticket_id]);

        // Если это AJAX запрос, возвращаем JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode([
                'success' => true,
                'name' => htmlspecialchars($user['name']),
                'role' => $user['role'],
                'message' => nl2br(htmlspecialchars($message)),
                'time' => date('d.m.Y H:i')
            ]);
            exit;
        }

        header("Location: /admin/support/view?id=" . $ticket_id);
        exit;
    }

    public function updateStatus()
    {
        Auth::requireLogin();
        $user = Auth::user();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Нет доступа']);
            exit;
        }

        $ticket_id = $_POST['ticket_id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (!$ticket_id || !$status) {
            echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
            return;
        }

        try {
            $db = Database::connect();
            $stmt = $db->prepare("UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $ticket_id]);

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getReplies()
    {
        Auth::requireLogin();
        $user = Auth::user();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Нет доступа']);
            exit;
        }

        if (!isset($_GET['ticket_id'])) {
            echo json_encode(['success' => false, 'error' => 'Ticket ID is required']);
            exit;
        }

        $ticketId = (int)$_GET['ticket_id'];
        $lastReplyId = isset($_GET['last_reply_id']) ? (int)$_GET['last_reply_id'] : 0;

        $db = Database::connect();

        // Проверяем существование тикета
        $stmt = $db->prepare("SELECT * FROM tickets WHERE id = ?");
        $stmt->execute([$ticketId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            echo json_encode(['success' => false, 'error' => 'Ticket not found']);
            exit;
        }

        // Получаем только новые сообщения (те, у которых ID больше lastReplyId)
        $stmt = $db->prepare("
            SELECT r.*, u.name, u.role 
            FROM ticket_replies r 
            JOIN users u ON u.id = r.user_id 
            WHERE ticket_id = ? AND r.id > ?
            ORDER BY r.created_at ASC
        ");
        $stmt->execute([$ticketId, $lastReplyId]);
        $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Форматируем сообщения для ответа
        $formattedReplies = [];
        $maxReplyId = $lastReplyId;
        
        foreach ($replies as $reply) {
            $formattedReplies[] = [
                'id' => $reply['id'],
                'name' => htmlspecialchars($reply['name']),
                'message' => nl2br(htmlspecialchars($reply['message'])),
                'time' => date('d.m.Y H:i', strtotime($reply['created_at'])),
                'role' => $reply['role'] ?? 'user'
            ];
            // Запоминаем максимальный ID для следующего запроса
            if ($reply['id'] > $maxReplyId) {
                $maxReplyId = $reply['id'];
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'newMessages' => $formattedReplies,
            'lastReplyId' => $maxReplyId
        ]);
        exit;
    }

    public function delete()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $ticket_id = $_POST['ticket_id'] ?? null;

        if (!$ticket_id) {
            http_response_code(400);
            echo "Не указан ID тикета";
            exit;
        }

        $db = Database::connect();

        $stmt = $db->prepare("SELECT user_id FROM tickets WHERE id = ?");
        $stmt->execute([$ticket_id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            http_response_code(404);
            echo "Тикет не найден";
            exit;
        }

        if ($user['role'] !== 'admin' && $user['id'] !== $ticket['user_id']) {
            http_response_code(403);
            echo "Нет доступа для удаления этого тикета";
            exit;
        }

        $deleteReplies = $db->prepare("DELETE FROM ticket_replies WHERE ticket_id = ?");
        $deleteReplies->execute([$ticket_id]);

        $deleteTicket = $db->prepare("DELETE FROM tickets WHERE id = ?");
        $deleteTicket->execute([$ticket_id]);

        if ($user['role'] === 'admin') {
            header("Location: /admin/support");
        } else {
            header("Location: /support");
        }
        exit;
    }

    public function deleteClosed()
    {
        Auth::requireLogin();
        $user = Auth::user();

        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo "Нет доступа";
            exit;
        }

        $db = Database::connect();

        try {
            $db->exec("DELETE FROM ticket_replies WHERE ticket_id IN (SELECT id FROM tickets WHERE status='closed')");

            $db->exec("DELETE FROM tickets WHERE status='closed'");

            $_SESSION['flash_success'] = "Все закрытые тикеты успешно удалены.";
            header("Location: /admin/support");
            exit;
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Ошибка при удалении: " . $e->getMessage();
            header("Location: /admin/support");
            exit;
        }
    }
}