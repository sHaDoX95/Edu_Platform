<?php
require_once __DIR__ . '/../core/Database.php';

class Lesson {
    public static function findByCourse($courseId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM lessons WHERE course_id = ?");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($courseId, $title, $content) {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO lessons (course_id, title, content) VALUES (?, ?, ?)");
        return $stmt->execute([$courseId, $title, $content]);
    }

    public static function update($id, $title, $content) {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE lessons SET title = ?, content = ? WHERE id = ?");
        return $stmt->execute([$title, $content, $id]);
    }

    public static function delete($id) {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM lessons WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function find($id) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM lessons WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
