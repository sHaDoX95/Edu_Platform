<?php
require_once __DIR__ . '/../../models/Test.php';
require_once __DIR__ . '/../../models/Progress.php';
require_once __DIR__ . '/../../models/Course.php';

$user = Auth::user();

$completedCount = 0;
$totalLessons = count($course['lessons']);
foreach ($course['lessons'] as $lesson) {
    $lessonDone = Progress::isCompleted($user['id'], $lesson['id']);
    $hasTest = Test::existsForLesson($lesson['id']);
    $testPassed = $hasTest ? Progress::isTestPassed($user['id'], $lesson['id']) : true;
    if ($lessonDone && $testPassed) $completedCount++;
}
$percent = $totalLessons > 0 ? round(($completedCount / $totalLessons) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/css/style.css?v=1">
    <title><?= htmlspecialchars($course['title']) ?></title>
</head>
<body>
<nav>
    <p>
        Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
        <?php if ($user['role'] === 'teacher'): ?>
            <a href="/teacher">👨‍🏫 Личный кабинет</a> |
            <a href="/lesson/create?course_id=<?= $course['id'] ?>">➕ Добавить урок</a> |
        <?php else: ?>
            <a href="/user">👤 Личный кабинет</a> |
        <?php endif; ?>
        <a href="/auth/logout">🚪 Выйти</a>
    </p>
</nav>

<div class="container">
    <a href="/course">← К списку курсов</a>
    <h2><?= htmlspecialchars($course['title']) ?></h2>
    <p><?= htmlspecialchars($course['description']) ?></p>

    <p id="course-progress"><strong>Прогресс:</strong> <span id="course-progress-text"><?= $completedCount ?> из <?= $totalLessons ?> тем пройдено</span></p>
    <div class="progress-bar-container" style="margin-bottom: 15px; width: 300px;">
        <div id="course-progress-bar" class="progress-bar" style="width: <?= $percent ?>%"></div>
    </div>

    <h3>Уроки:</h3>
    <ol id="lessons-list">
        <?php foreach ($course['lessons'] as $lesson): ?>
            <?php
                $lessonId = (int)$lesson['id'];
                $lessonDone = Progress::isCompleted($user['id'], $lessonId);
                $hasTest = Test::existsForLesson($lessonId);
                $testPassed = $hasTest ? Progress::isTestPassed($user['id'], $lessonId) : true;
            ?>
            <li class="lesson-row">
                <div class="lesson-header" data-lesson="<?= $lessonId ?>" style="cursor: pointer; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <strong><?= htmlspecialchars($lesson['title']) ?></strong>
                    </div>
                    <div style="font-size:0.9em;">
                        <?php if ($user['role'] === 'teacher'): ?>
                            <a href="/teacher/editLesson?id=<?= $lessonId ?>">✏</a>
                            &nbsp;|&nbsp;
                            <a href="/teacher/deleteLesson?id=<?= $lessonId ?>&course_id=<?= $course['id'] ?>" onclick="return confirm('Удалить урок?')">🗑</a>
                        <?php endif; ?>
                        &nbsp;&nbsp;
                        <span id="lesson-badge-<?= $lessonId ?>">
                            <?php
                            if ($lessonDone && ($hasTest ? $testPassed : true)) {
                                echo '<span style="color:#28a745">●</span>';
                            } elseif ($lessonDone || ($hasTest && $testPassed)) {
                                echo '<span style="color:#ffc107">●</span>';
                            } else {
                                echo '<span style="color:#dc3545">●</span>';
                            }
                            ?>
                        </span>
                    </div>
                </div>

                <div id="lesson-<?= $lessonId ?>" class="lesson-content" style="display: none; margin-top:10px;">
                    <p><?= nl2br(htmlspecialchars($lesson['content'])) ?></p>

                    <div id="progress-<?= $lessonId ?>">
                        <?php if ($lessonDone): ?>
                            <p style="color: green;">✅ Урок пройден</p>
                            <button onclick="toggleProgress(<?= $course['id'] ?>, <?= $lessonId ?>, false)">
                                Отметить как НЕ пройденный
                            </button>
                        <?php else: ?>
                            <button onclick="toggleProgress(<?= $course['id'] ?>, <?= $lessonId ?>, true)">
                                Отметить как пройденный
                            </button>
                        <?php endif; ?>
                    </div>

                    <br>

                    <?php if ($hasTest): ?>
                        <div id="test-<?= $lessonId ?>">
                            <?php if ($testPassed): ?>
                                <p style="color: green;">🧪 Тест пройден</p>
                            <?php else: ?>
                                <p style="color: red;">🧪 Тест не пройден</p>
                            <?php endif; ?>
                            <a href="/test/show?lesson_id=<?= $lessonId ?>">📝 Пройти тест</a>
                        </div>
                    <?php endif; ?>
                </div>

                <hr>
            </li>
        <?php endforeach; ?>
    </ol>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const headers = document.querySelectorAll('.lesson-header');
    headers.forEach(h => {
        h.addEventListener('click', () => {
            const lessonId = h.getAttribute('data-lesson');
            const contentId = 'lesson-' + lessonId;
            const content = document.getElementById(contentId);

            const willOpen = content.style.display === 'none' || content.style.display === '';
            document.querySelectorAll('.lesson-content').forEach(el => {
                el.style.display = 'none';
            });

            if (willOpen) {
                content.style.display = 'block';
                window.location.hash = '';
            } else {
                content.style.display = 'none';
            }
        });
    });
});

function toggleProgress(courseId, lessonId, complete) {
    fetch('/course/progress', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ course_id: courseId, lesson_id: lessonId, complete: !!complete })
    })
    .then(r => r.json())
    .then(data => {
        if (!data || !data.success) {
            alert('Ошибка при сохранении прогресса' + (data && data.message ? ': ' + data.message : ''));
            return;
        }

        document.getElementById('progress-' + lessonId).innerHTML = data.lessonHtml;

        const badge = document.getElementById('lesson-badge-' + lessonId);
        if (data.topicDone) {
            badge.innerHTML = '<span style="color:#28a745">●</span>';
        } else if (data.partial) {
            badge.innerHTML = '<span style="color:#ffc107">●</span>';
        } else {
            badge.innerHTML = '<span style="color:#dc3545">●</span>';
        }

        document.getElementById('course-progress-text').textContent = data.completedCount + ' из ' + data.totalLessons + ' тем пройдено';
        document.getElementById('course-progress-bar').style.width = data.percent + '%';
    })
    .catch(err => {
        console.error(err);
        alert('Сетевая ошибка при сохранении прогресса');
    });
}
</script>
</body>
</html>