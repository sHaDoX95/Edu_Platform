<?php
require_once __DIR__ . '/../core/Database.php';

class Test {
    public static function getByLesson($lessonId) {
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT q.id AS question_id, q.question, o.id AS option_id, o.text, o.is_correct
            FROM questions q
            JOIN options o ON o.question_id = q.id
            WHERE q.lesson_id = ?
            ORDER BY q.id, o.id
        ");
        $stmt->execute([$lessonId]);
        $rows = $stmt->fetchAll();

        $test = [];
        foreach ($rows as $row) {
            $qid = $row['question_id'];
            if (!isset($test[$qid])) {
                $test[$qid] = [
                    'question' => $row['question'],
                    'options' => []
                ];
            }
            $test[$qid]['options'][] = [
                'id' => $row['option_id'],
                'text' => $row['text'],
                'is_correct' => $row['is_correct']
            ];
        }

        return $test;
    }

    public static function existsForLesson($lessonId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT 1 FROM questions WHERE lesson_id = :lesson_id LIMIT 1");
        $stmt->execute(['lesson_id' => $lessonId]);
        return (bool)$stmt->fetchColumn();
    }
}