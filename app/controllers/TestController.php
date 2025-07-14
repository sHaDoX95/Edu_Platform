<?php
require_once __DIR__ . '/../models/Test.php';
require_once __DIR__ . '/../core/Auth.php';

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

        $lessonId = $_POST['lesson_id'];
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

        require __DIR__ . '/../views/test/result.php';
    }
}
