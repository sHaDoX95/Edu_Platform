<?php
require_once __DIR__ . '/../models/Test.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Progress.php';

class TestController {
    public function show() {
        Auth::requireLogin();

        $lessonId = $_GET['lesson_id'] ?? null;
        if (!$lessonId) {
            echo "Урок не найден";
            return;
        }

        $test = Test::getByLesson($lessonId);
        require __DIR__ . '/../views/test/show.php';
    }

    public function submit() {
        Auth::requireLogin();
        $user = Auth::user();
        $userId = $user['id'];
        $lessonId = $_POST['lesson_id'] ?? null;

        if (!$lessonId) {
            echo "Урок не найден";
            return;
        }

        $test = Test::getByLesson($lessonId);
        $correct = 0;
        $total = count($test);

        foreach ($test as $qid => $data) {
            $selected = $_POST["q$qid"] ?? null;
            foreach ($data['options'] as $opt) {
                if ($opt['id'] == $selected && $opt['is_correct']) {
                    $correct++;
                }
            }
        }

        $score = $total > 0 ? round(($correct / $total) * 100) : 0;
        $passed = $score >= 70;

        Progress::saveTestResult($userId, $lessonId, $score, $passed);

        require __DIR__ . '/../views/test/result.php';
    }
}