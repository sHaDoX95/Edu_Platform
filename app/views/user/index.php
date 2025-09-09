<?php
require_once __DIR__ . '/../../models/Lesson.php';
require_once __DIR__ . '/../../models/Test.php';

$user = Auth::user();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=1">
    <title>Личный кабинет</title>
</head>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.progress-bar').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });
    });
</script>
<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <a href="/course">📚 Курсы</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="container">
        <h2>👤 Личный кабинет</h2>
        <p>Добро пожаловать, <strong><?= htmlspecialchars($user['name']) ?></strong>!</p>

        <h3>📈 Прогресс по курсам:</h3>

        <?php if (count($courses) === 0): ?>
            <p>У вас пока нет доступных курсов.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($courses as $course): ?>
                    <?php
                        $lessons = Lesson::findByCourse($course['id']);

                        $stepsTotal = 0;
                        $stepsDone = 0;

                        foreach ($lessons as $lesson) {
                            $lessonDone = Progress::isCompleted($user['id'], $lesson['id']);
                            $hasTest = Test::existsForLesson($lesson['id']);
                            $testPassed = $hasTest && Progress::isTestPassed($user['id'], $lesson['id']);

                            $stepsTotal++;
                            if ($lessonDone) $stepsDone++;

                            if ($hasTest) {
                                $stepsTotal++;
                                if ($testPassed) $stepsDone++;
                            }
                        }

                        $percent = $stepsTotal > 0 ? round(($stepsDone / $stepsTotal) * 100) : 0;
                    ?>
                    <li style="margin-bottom: 30px;">
                        <strong><?= htmlspecialchars($course['title']) ?></strong><br>
                        <small><?= htmlspecialchars($course['description']) ?></small><br>

                        📊 Общий прогресс: <?= $stepsDone ?> из <?= $stepsTotal ?> шагов (<?= $percent ?>%)

                        <div class="progress-bar-container" style="margin-top: 5px;">
                            <div class="progress-bar" style="width: <?= $percent ?>%"></div>
                        </div>

                        <a href="/course/show?id=<?= $course['id'] ?>">📓 Перейти к курсу</a>

                        <?php if (count($lessons) > 0): ?>
                            <ul style="margin-top: 10px;">
                                <?php foreach ($lessons as $lesson): ?>
                                    <?php
                                        $lessonDone = Progress::isCompleted($user['id'], $lesson['id']);
                                        $hasTest = Test::existsForLesson($lesson['id']);
                                        $testPassed = $hasTest && Progress::isTestPassed($user['id'], $lesson['id']);

                                        $class = '';
                                        if ($lessonDone && (!$hasTest || $testPassed)) {
                                            $class = 'done';
                                        } elseif ($lessonDone || $testPassed) {
                                            $class = 'partial';
                                        } else {
                                            $class = 'not-done';
                                        }
                                    ?>
                                    <li class="lesson-item <?= $class ?>">
                                        <strong><?= htmlspecialchars($lesson['title']) ?></strong><br>
                                        📖 Урок: <?= $lessonDone ? 'Пройден' : 'Не пройден' ?><br>
                                        🧪 Тест: <?= $hasTest ? ($testPassed ? 'Пройден' : 'Не пройден') : 'Нет теста' ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>