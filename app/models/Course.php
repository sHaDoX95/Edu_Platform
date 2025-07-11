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

    public static function allWithUserProgress($userId) {
        $pdo = Database::connect();
    
        $sql = "
            SELECT 
                c.id,
                c.title,
                c.description,
                COUNT(l.id) AS total_lessons,
                COUNT(lp.id) AS completed_lessons
            FROM courses c
            LEFT JOIN lessons l ON l.course_id = c.id
            LEFT JOIN lesson_progress lp ON lp.lesson_id = l.id AND lp.user_id = :user_id
            GROUP BY c.id
            ORDER BY c.id;
        ";
    
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
