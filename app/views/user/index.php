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
    
    .no-started-courses {
        text-align: center;
        padding: 40px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin: 20px 0;
    }
    
    .no-started-courses-icon {
        font-size: 4em;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .start-course-button {
        display: inline-block;
        margin-top: 15px;
        padding: 12px 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .start-course-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
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

            <?php
            // Фильтруем курсы: оставляем только те, которые пользователь начал
            $startedCourses = array_filter($courses, function($course) use ($user) {
                $lessons = Lesson::findByCourse($course['id']);
                foreach ($lessons as $lesson) {
                    // Если есть хотя бы один начатый урок (пройден или есть запись в lesson_progress)
                    if (Progress::isCompleted($user['id'], $lesson['id']) || 
                        Progress::hasProgress($user['id'], $lesson['id'])) {
                        return true;
                    }
                }
                return false;
            });
            ?>

            <?php if (count($startedCourses) === 0): ?>
                <div class="no-started-courses">
                    <div class="no-started-courses-icon">📚</div>
                    <h3>Вы еще не начали изучать курсы</h3>
                    <p>Начните изучение любого курса, и он появится здесь</p>
                    <a href="/course" class="start-course-button">Начать изучение курсов</a>
                </div>
            <?php else: ?>
                <div class="courses-grid">
                    <?php foreach ($startedCourses as $course): ?>
                        <?php
                        $lessons = Lesson::findByCourse($course['id']);
                        $stepsTotal = 0;
                        $stepsDone = 0;
                        $hasAnyProgress = false;

                        foreach ($lessons as $lesson) {
                            $lessonDone = Progress::isCompleted($user['id'], $lesson['id']);
                            $hasTest = Test::existsForLesson($lesson['id']);
                            $testPassed = $hasTest && Progress::isTestPassed($user['id'], $lesson['id']);
                            $hasProgress = Progress::hasProgress($user['id'], $lesson['id']);

                            $stepsTotal++;
                            if ($lessonDone) $stepsDone++;

                            if ($hasTest) {
                                $stepsTotal++;
                                if ($testPassed) $stepsDone++;
                            }
                            
                            // Отмечаем, что курс начат если есть прогресс по любому уроку
                            if ($lessonDone || $hasProgress) {
                                $hasAnyProgress = true;
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
                                <?= $percent > 0 ? '📓 Продолжить обучение' : '📓 Начать обучение' ?>
                            </a>

                            <?php if (count($lessons) > 0): ?>
                                <div class="lessons-list">
                                    <h4>Уроки курса:</h4>
                                    <?php foreach ($lessons as $lesson): ?>
                                        <?php
                                        $lessonDone = Progress::isCompleted($user['id'], $lesson['id']);
                                        $hasTest = Test::existsForLesson($lesson['id']);
                                        $testPassed = $hasTest && Progress::isTestPassed($user['id'], $lesson['id']);
                                        $hasProgress = Progress::hasProgress($user['id'], $lesson['id']);

                                        $statusClass = '';
                                        if ($lessonDone && (!$hasTest || $testPassed)) {
                                            $statusClass = 'status-done';
                                        } elseif ($lessonDone || $testPassed || $hasProgress) {
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
                                                        <?php if ($lessonDone): ?>
                                                            Пройден
                                                        <?php elseif ($hasProgress): ?>
                                                            В процессе
                                                        <?php else: ?>
                                                            Не начат
                                                        <?php endif; ?>
                                                    </span>
                                                    <?php if ($hasTest): ?>
                                                        <span class="lesson-detail">
                                                            <span class="icon-emoji">🧪</span>
                                                            <?php if ($testPassed): ?>
                                                                Пройден
                                                            <?php elseif ($hasProgress): ?>
                                                                В процессе
                                                            <?php else: ?>
                                                                Не пройден
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php endif; ?>
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