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
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Личный кабинет</title>
</head>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.course-progress-bar').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.width = width;
                bar.style.transition = 'width 1s ease-in-out';
            }, 100);
        });
    });
</script>

<style>
    .chat-button-container {
        display: flex;
        justify-content: center;
    }

    .chat-button {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 15px 25px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        border: none;
        cursor: pointer;
        min-width: 180px;
        justify-content: center;
    }

    .chat-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: white;
        text-decoration: none;
    }

    .chat-button:active {
        transform: translateY(0);
    }

    .chat-icon {
        font-size: 18px;
    }

    .chat-text {
        font-family: inherit;
    }
</style>

<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <a href="/course">📚 Курсы</a> |
            <a href="/support">🆘 Поддержка</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="container">
        <div class="user-profile">
            <div class="user-avatar">👤</div>
            <h1 class="user-name"><?= htmlspecialchars($user['name']) ?></h1>
            <p class="user-welcome">Добро пожаловать в ваш личный кабинет!</p>
        </div>

        <div class="chat-button-container" style="margin: 20px 0;">
            <a href="/chat" class="chat-button">
                <span class="chat-icon">💬</span>
                <span class="chat-text">Мои чаты</span>
            </a>
        </div>

        <div class="courses-progress">
            <h2 class="section-title">📈 Прогресс по курсам</h2>

            <?php if (count($courses) === 0): ?>
                <div class="empty-courses">
                    <div>📚</div>
                    <h3>Курсы не найдены</h3>
                    <p>У вас пока нет доступных курсов</p>
                    <a href="/course" class="course-action">Найти курсы</a>
                </div>
            <?php else: ?>
                <div class="courses-grid">
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
                        <div class="course-card-profile">
                            <h3 class="course-title-profile"><?= htmlspecialchars($course['title']) ?></h3>
                            <p class="course-description-profile"><?= htmlspecialchars($course['description']) ?></p>

                            <div class="course-progress-info">
                                <span class="course-progress-text">
                                    📊 <?= $stepsDone ?> из <?= $stepsTotal ?> шагов
                                </span>
                                <span class="course-progress-percent"><?= $percent ?>%</span>
                            </div>

                            <div class="course-progress-bar-container">
                                <div class="course-progress-bar" style="width: <?= $percent ?>%"></div>
                            </div>

                            <a href="/course/show?id=<?= $course['id'] ?>" class="course-action">
                                📓 Перейти к курсу
                            </a>

                            <?php if (count($lessons) > 0): ?>
                                <div class="lessons-list">
                                    <h4>Уроки курса:</h4>
                                    <?php foreach ($lessons as $lesson): ?>
                                        <?php
                                        $lessonDone = Progress::isCompleted($user['id'], $lesson['id']);
                                        $hasTest = Test::existsForLesson($lesson['id']);
                                        $testPassed = $hasTest && Progress::isTestPassed($user['id'], $lesson['id']);

                                        $statusClass = '';
                                        if ($lessonDone && (!$hasTest || $testPassed)) {
                                            $statusClass = 'status-done';
                                        } elseif ($lessonDone || $testPassed) {
                                            $statusClass = 'status-partial';
                                        } else {
                                            $statusClass = 'status-not-done';
                                        }
                                        ?>
                                        <div class="lesson-item-profile">
                                            <div class="lesson-status <?= $statusClass ?>"></div>
                                            <div class="lesson-info">
                                                <div class="lesson-title"><?= htmlspecialchars($lesson['title']) ?></div>
                                                <div class="lesson-details">
                                                    <span class="lesson-detail">
                                                        <span class="icon-emoji">📖</span>
                                                        <?= $lessonDone ? 'Пройден' : 'Не пройден' ?>
                                                    </span>
                                                    <span class="lesson-detail">
                                                        <span class="icon-emoji">🧪</span>
                                                        <?= $hasTest ? ($testPassed ? 'Пройден' : 'Не пройден') : 'Нет теста' ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>