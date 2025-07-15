<?php

class Lesson {
    public static function getByCourse($courseId) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM lessons WHERE course_id = ?");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}