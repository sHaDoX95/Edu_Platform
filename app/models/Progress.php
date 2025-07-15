<?php
require_once __DIR__ . '/../core/Database.php';

class Progress {
    public static function isCompleted($userId, $lessonId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT id FROM lesson_progress WHERE user_id = :uid AND lesson_id = :lid");
        $stmt->execute(['uid' => $userId, 'lid' => $lessonId]);
        return $stmt->fetch() !== false;
    }

    public static function markCompleted($userId, $lessonId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO lesson_progress (user_id, lesson_id)
                               VALUES (:uid, :lid) ON CONFLICT DO NOTHING");
        $stmt->execute(['uid' => $userId, 'lid' => $lessonId]);
    }

    public static function unmarkCompleted($userId, $lessonId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM lesson_progress WHERE user_id = :uid AND lesson_id = :lid");
        $stmt->execute(['uid' => $userId, 'lid' => $lessonId]);
    }

    public static function countCompleted($userId, $courseId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("
            SELECT COUNT(lp.id)
            FROM lesson_progress lp
            JOIN lessons l ON lp.lesson_id = l.id
            WHERE lp.user_id = :uid AND l.course_id = :cid
        ");
        $stmt->execute(['uid' => $userId, 'cid' => $courseId]);
        return (int)$stmt->fetchColumn();
    }

    public static function isTestPassed($userId, $lessonId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT test_passed FROM lesson_progress WHERE user_id = ? AND lesson_id = ?");
        $stmt->execute([$userId, $lessonId]);
        $result = $stmt->fetch();
    
        return $result ? (bool)$result['test_passed'] : false;
    }
    
    public static function saveTestResult($userId, $lessonId, $score, $passed) {
        $pdo = Database::connect();
    
        $stmt = $pdo->prepare("
            INSERT INTO lesson_progress (user_id, lesson_id, test_score, test_passed)
            VALUES (:user_id, :lesson_id, :score, :passed)
            ON CONFLICT (user_id, lesson_id) DO UPDATE 
            SET test_score = EXCLUDED.test_score,
                test_passed = EXCLUDED.test_passed
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':lesson_id' => $lessonId,
            ':score' => $score,
            ':passed' => $passed ? 'true' : 'false',
        ]);
    }
}
