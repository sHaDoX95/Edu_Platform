<?php
require_once __DIR__ . '/../core/Database.php';

class Lesson {
    public static function findByCourse($courseId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM lessons WHERE course_id = ?");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findWithCourse($lessonId) {
        $pdo = Database::connect();
        $sql = "
            SELECT l.*, c.teacher_id, c.id as course_id
            FROM lessons l
            JOIN courses c ON l.course_id = c.id
            WHERE l.id = :id
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $lessonId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($courseId, $title, $content) {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO lessons (course_id, title, content) VALUES (?, ?, ?)");
        return $stmt->execute([$courseId, $title, $content]);
    }

    public static function update($id, $title, $content) {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("UPDATE lessons SET title = :title, content = :content WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'title' => $title,
            'content' => $content
        ]);
    }

    public static function delete($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM lesson_progress WHERE lesson_id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function find($id) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM lessons WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function countByCourse($courseId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
        $stmt->execute([$courseId]);
        return (int)$stmt->fetchColumn();
    }
}