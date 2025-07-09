<?php
require_once __DIR__ . '/../core/Database.php';

class Course {
    public static function all() {
        $pdo = Database::connect();
        return $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findWithLessons($id) {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = :id");
        $stmt->execute(['id' => $id]);
        $course['lessons'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $course;
    }
}
