<?php
require_once __DIR__ . '/../core/Database.php';

class SupportController {
    public function index() {
        $user = Auth::user();
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/support/index.php';
    }

    public function store() {
        $user = Auth::user();
        $db = Database::connect();

        $stmt = $db->prepare("INSERT INTO tickets (user_id, subject, message) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $_POST['subject'], $_POST['message']]);

        header("Location: /support");
    }

    public function view() {
        $user = Auth::user();
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM tickets WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['id'], $user['id']]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("
            SELECT r.*, u.name 
            FROM ticket_replies r 
            JOIN users u ON u.id = r.user_id 
            WHERE ticket_id = ? 
            ORDER BY r.created_at ASC
        ");
        $stmt->execute([$_GET['id']]);
        $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/support/view.php';
    }

    public function reply() {
        $user = Auth::user();
        $db = Database::connect();

        $stmt = $db->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['ticket_id'], $user['id'], $_POST['message']]);

        $stmt = $db->prepare("UPDATE tickets SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$_POST['ticket_id']]);

        header("Location: /support/view?id=" . $_POST['ticket_id']);
    }
}