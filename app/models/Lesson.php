<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Logger.php';

class Lesson
{
    public static function findByCourse($courseId)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM lessons WHERE course_id = ?");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findWithCourse($lessonId)
    {
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

    public static function create($courseId, $title, $content)
    {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO lessons (course_id, title, content) VALUES (?, ?, ?)");
        $result = $stmt->execute([$courseId, $title, $content]);

        if ($result) {
            Logger::log(
                'Создан урок',
                "Урок '$title' добавлен в курс ID: $courseId"
            );
        }

        return $result;
    }

    public static function update($id, $title, $content)
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE lessons SET title = :title, content = :content WHERE id = :id");
        $result = $stmt->execute([
            'id' => $id,
            'title' => $title,
            'content' => $content
        ]);

        if ($result) {
            Logger::log(
                'Обновлён урок',
                "Урок ID $id обновлён. Новое название: '$title'"
            );
        }
    }

    public static function delete($id)
    {
        $pdo = Database::connect();

        $lesson = self::find($id);
        $title = $lesson['title'] ?? 'неизвестно';

        $stmt = $pdo->prepare("DELETE FROM lesson_progress WHERE lesson_id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            Logger::log(
                'Удалён урок',
                "Урок '$title' удалён (ID: $id)"
            );
        }

        return $result;
    }

    public static function find($id)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM lessons WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function countByCourse($courseId)
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
        $stmt->execute([$courseId]);
        return (int)$stmt->fetchColumn();
    }

    public static function paginate($page, $perPage)
    {
        $offset = ($page - 1) * $perPage;
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT l.*, c.title AS course_title
            FROM lessons l
            LEFT JOIN courses c ON l.course_id = c.id
            ORDER BY l.id DESC
            LIMIT :perPage OFFSET :offset
        ");
        $stmt->bindValue(':perPage', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function count()
    {
        $db = Database::connect();
        return $db->query("SELECT COUNT(*) FROM lessons")->fetchColumn();
    }
}
