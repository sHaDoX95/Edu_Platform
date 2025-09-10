<?php
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Progress.php';

class CourseController {
    public function index() {
        Auth::requireLogin();

        $courses = Course::allWithLessonsCount();

        require_once __DIR__ . '/../views/course/index.php';
    }

    public function show() {
        Auth::requireLogin();
    
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "Курс не найден";
            return;
        }
    
        $user = Auth::user();
    
        if (isset($_GET['complete'])) {
            $lessonId = (int)$_GET['complete'];
            Progress::markCompleted($user['id'], $lessonId);
            header("Location: /course/show?id=" . $id);
            exit;
        }

        if (isset($_GET['uncomplete'])) {
            $lessonId = (int)$_GET['uncomplete'];
            Progress::unmarkCompleted($user['id'], $lessonId);
            header("Location: /course/show?id=" . $id);
            exit;
        }
    
        $course = Course::findWithLessons($id);
    
        $completedCount = Progress::countCompleted($user['id'], $id);
        $totalLessons = count($course['lessons']);
    
        require_once __DIR__ . '/../views/course/show.php';
    }

    public function search() {
        $query = $_GET['q'] ?? '';
        $results = Course::searchByTitle($query);

        foreach ($results as $k => $course) {
            $results[$k]['lessons_count'] = Lesson::countByCourse($course['id']);
        }

        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }

    public function progress() {
        Auth::requireLogin();

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid payload']);
            exit;
        }

        $user = Auth::user();
        $userId = $user['id'];
        $lessonId = (int)($data['lesson_id'] ?? 0);
        $courseId = (int)($data['course_id'] ?? 0);
        $complete = isset($data['complete']) ? (bool)$data['complete'] : null;

        if (!$lessonId || !is_int($lessonId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Lesson id missing']);
            exit;
        }

        if ($complete) {
            Progress::markCompleted($userId, $lessonId);
        } else {
            Progress::unmarkCompleted($userId, $lessonId);
        }

        require_once __DIR__ . '/../models/Test.php';
        $lessonDone = Progress::isCompleted($userId, $lessonId);
        $hasTest = Test::existsForLesson($lessonId);
        $testPassed = $hasTest ? Progress::isTestPassed($userId, $lessonId) : true;

        ob_start();
        if ($lessonDone) {
            echo '<p style="color: green;">✅ Урок пройден</p>';
            printf('<button onclick="toggleProgress(%d, %d, false)">Отметить как НЕ пройденный</button>', $courseId, $lessonId);
        } else {
            printf('<button onclick="toggleProgress(%d, %d, true)">Отметить как пройденный</button>', $courseId, $lessonId);
        }
        $lessonHtml = ob_get_clean();

        require_once __DIR__ . '/../models/Course.php';
        $course = Course::findWithLessons($courseId);
        $completedCount = 0;
        $totalLessons = count($course['lessons']);
        foreach ($course['lessons'] as $ls) {
            $ld = Progress::isCompleted($userId, $ls['id']);
            $hasT = Test::existsForLesson($ls['id']);
            $tp = $hasT ? Progress::isTestPassed($userId, $ls['id']) : true;
            if ($ld && $tp) $completedCount++;
        }
        $percent = $totalLessons > 0 ? round(($completedCount / $totalLessons) * 100) : 0;

        $topicDone = ($lessonDone && ($hasTest ? $testPassed : true));
        $partial = (!$topicDone) && ($lessonDone || ($hasTest && $testPassed));

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'lessonHtml' => $lessonHtml,
            'completedCount' => $completedCount,
            'totalLessons' => $totalLessons,
            'percent' => $percent,
            'topicDone' => $topicDone,
            'partial' => $partial
        ]);
        exit;
    }
}
