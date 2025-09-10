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
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    
    public static function create($title, $description, $teacherId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO courses (title, description, teacher_id) VALUES (:title, :description, :tid)");
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'tid' => $teacherId
        ]);
    }

    public static function findByTeacher($teacherId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = :tid");
        $stmt->execute(['tid' => $teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function update($id, $title, $description) {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE courses SET title = ?, description = ? WHERE id = ?");
        return $stmt->execute([$title, $description, $id]);
    }

    public static function find($id) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function delete($id) {
        $db = Database::connect();
        
        $stmt = $db->prepare("DELETE FROM lessons WHERE course_id = ?");
        $stmt->execute([$id]);
        
        $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getStudentsWithProgress($courseId) {
        $db = Database::connect();

        $sql = "
            SELECT 
                u.id AS user_id,
                u.name AS user_name,
                COUNT(DISTINCT l.id) AS total_lessons,
                COUNT(DISTINCT lp.id) AS completed_lessons,
                COUNT(DISTINCT lp.id) FILTER (WHERE lp.test_passed = TRUE) AS passed_tests
            FROM users u
            JOIN lesson_progress lp ON lp.user_id = u.id
            JOIN lessons l ON l.id = lp.lesson_id
            WHERE l.course_id = :course_id
            GROUP BY u.id, u.name
            ORDER BY u.name;
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute(['course_id' => $courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function searchByTitle($query) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT id, title, description FROM courses WHERE title ILIKE :query");
        $stmt->execute(['query' => '%' . $query . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}