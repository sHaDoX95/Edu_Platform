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
            'tid' => $teacherId !== '' ? $teacherId : null
        ]);
    }

    public static function update($id, $title, $description, $teacherId = null) {
        $db = Database::connect();
        $query = "UPDATE courses SET title = ?, description = ?";
        $params = [$title, $description];

        if ($teacherId !== null) {
            $query .= ", teacher_id = ?";
            $params[] = $teacherId;
        }

        $query .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $db->prepare($query);
        return $stmt->execute($params);
    }

    public static function updateTeacher($id, $teacherId = null) {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE courses SET teacher_id = ? WHERE id = ?");
        return $stmt->execute([$teacherId !== '' ? $teacherId : null, $id]);
    }


    public static function find($id) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function delete($id) {
        $db = Database::connect();

        $stmt = $db->prepare("
            DELETE FROM lesson_progress
            WHERE lesson_id IN (SELECT id FROM lessons WHERE course_id = ?)
        ");
        $stmt->execute([$id]);

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
                (SELECT COUNT(*) FROM lessons WHERE course_id = :course_id) AS total_lessons,
                COUNT(lp.id) AS completed_lessons,
                COUNT(lp.id) FILTER (WHERE lp.test_passed = TRUE) AS passed_tests
            FROM users u
            LEFT JOIN lesson_progress lp 
                ON lp.user_id = u.id
                AND lp.lesson_id IN (SELECT id FROM lessons WHERE course_id = :course_id)
            WHERE u.id IN (
                SELECT DISTINCT user_id FROM lesson_progress lp2
                JOIN lessons l2 ON lp2.lesson_id = l2.id
                WHERE l2.course_id = :course_id
            )
            GROUP BY u.id, u.name
            ORDER BY u.name
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

    public static function allWithLessonsCount() {
        $courses = self::all();
        foreach ($courses as $k => $course) {
            $courses[$k]['lessons_count'] = Lesson::countByCourse($course['id']);
        }
        return $courses;
    }

    public static function getByTeacherWithStats($teacherId) {
        $db = Database::connect();
        
        $query = "
            SELECT 
                c.*,
                COUNT(DISTINCT l.id) as lessons_count,
                COUNT(DISTINCT lp.user_id) as students_count
            FROM courses c
            LEFT JOIN lessons l ON l.course_id = c.id
            LEFT JOIN lesson_progress lp ON lp.lesson_id = l.id
            WHERE c.teacher_id = ?
            GROUP BY c.id
            ORDER BY c.id DESC
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}