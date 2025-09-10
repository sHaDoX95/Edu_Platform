<?php
require_once __DIR__ . '/../core/Database.php';

class Progress {
    public static function isCompleted($userId, $lessonId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT completed_at FROM lesson_progress WHERE user_id = :uid AND lesson_id = :lid LIMIT 1");
        $stmt->execute(['uid' => $userId, 'lid' => $lessonId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && !empty($row['completed_at']);
    }

    public static function markCompleted($userId, $lessonId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            INSERT INTO lesson_progress (user_id, lesson_id, completed_at)
            VALUES (:uid, :lid, CURRENT_TIMESTAMP)
            ON CONFLICT (user_id, lesson_id) DO UPDATE
              SET completed_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute(['uid' => $userId, 'lid' => $lessonId]);
    }

    public static function unmarkCompleted($userId, $lessonId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE lesson_progress SET completed_at = NULL WHERE user_id = :uid AND lesson_id = :lid");
        $stmt->execute(['uid' => $userId, 'lid' => $lessonId]);
    }

    public static function countCompleted($userId, $courseId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT COUNT(lp.id)
            FROM lesson_progress lp
            JOIN lessons l ON lp.lesson_id = l.id
            WHERE lp.user_id = :uid AND l.course_id = :cid AND lp.completed_at IS NOT NULL
        ");
        $stmt->execute(['uid' => $userId, 'cid' => $courseId]);
        return (int)$stmt->fetchColumn();
    }

    public static function isTestPassed($userId, $lessonId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT test_passed FROM lesson_progress WHERE user_id = ? AND lesson_id = ? LIMIT 1");
        $stmt->execute([$userId, $lessonId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;
        $v = $row['test_passed'];
        return $v === true || $v === 't' || $v === '1' || $v === 1;
    }

    public static function saveTestResult($userId, $lessonId, $score, $passed) {
        $pdo = Database::connect();

        $passed = $passed ? 't' : 'f';

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO lesson_progress (user_id, lesson_id, test_score, test_passed)
                VALUES (:user_id, :lesson_id, :score, :passed)
                ON CONFLICT (user_id, lesson_id) DO NOTHING
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':lesson_id' => $lessonId,
                ':score' => $score,
                ':passed' => $passed,
            ]);

            $stmt = $pdo->prepare("
                UPDATE lesson_progress
                SET test_score = :score,
                    test_passed = :passed,
                    completed_at = COALESCE(completed_at, CURRENT_TIMESTAMP)
                WHERE user_id = :user_id AND lesson_id = :lesson_id
            ");
            $stmt->execute([
                ':score' => $score,
                ':passed' => $passed,
                ':user_id' => $userId,
                ':lesson_id' => $lessonId
            ]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}