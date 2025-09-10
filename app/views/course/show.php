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
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
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
    <a href="/course" class="back-link">← Назад к курсам</a>
    
    <div class="course-header">
        <h1 class="course-title"><?= htmlspecialchars($course['title']) ?></h1>
        <p class="course-description"><?= htmlspecialchars($course['description']) ?></p>
    </div>

    <div class="progress-section">
        <div class="progress-info">
            <span class="progress-text" id="course-progress-text"><?= $completedCount ?> из <?= $totalLessons ?> уроков завершено</span>
            <span class="progress-percent"><?= $percent ?>%</span>
        </div>
        <div class="course-progress-bar-container">
            <div id="course-progress-bar" class="course-progress-bar" style="width: <?= $percent ?>%"></div>
        </div>
    </div>

    <div class="lessons-section">
        <h2 class="section-title">📚 Уроки курса</h2>
        
        <ul class="lessons-list" id="lessons-list">
            <?php foreach ($course['lessons'] as $lesson): ?>
                <?php
                    $lessonId = (int)$lesson['id'];
                    $lessonDone = Progress::isCompleted($user['id'], $lessonId);
                    $hasTest = Test::existsForLesson($lessonId);
                    $testPassed = $hasTest ? Progress::isTestPassed($user['id'], $lessonId) : true;
                    
                    $statusClass = '';
                    if ($lessonDone && ($hasTest ? $testPassed : true)) {
                        $statusClass = 'status-complete';
                    } elseif ($lessonDone || ($hasTest && $testPassed)) {
                        $statusClass = 'status-partial';
                    } else {
                        $statusClass = 'status-pending';
                    }
                ?>
                <li class="lesson-item-card">
                    <div class="lesson-header" data-lesson="<?= $lessonId ?>">
                        <h3 class="lesson-title"><?= htmlspecialchars($lesson['title']) ?></h3>
                        <div class="lesson-status">
                            <?php if ($user['role'] === 'teacher'): ?>
                                <div class="teacher-actions">
                                    <a href="/teacher/editLesson?id=<?= $lessonId ?>" class="action-btn edit-btn">✏️</a>
                                    <a href="/teacher/deleteLesson?id=<?= $lessonId ?>&course_id=<?= $course['id'] ?>" 
                                       class="action-btn delete-btn" 
                                       onclick="return confirm('Удалить урок?')">🗑️</a>
                                </div>
                            <?php endif; ?>
                            <div class="status-badge <?= $statusClass ?>"></div>
                        </div>
                    </div>

                    <div id="lesson-<?= $lessonId ?>" class="lesson-content">
                        <div class="lesson-text">
                            <?= nl2br(htmlspecialchars($lesson['content'])) ?>
                        </div>

                        <div class="lesson-actions">
                            <div id="progress-<?= $lessonId ?>" class="progress-container">
                                <?php if ($lessonDone): ?>
                                    <button class="btn btn-secondary" onclick="toggleProgress(<?= $course['id'] ?>, <?= $lessonId ?>, false)">
                                        ❌ Отменить завершение
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-success" onclick="toggleProgress(<?= $course['id'] ?>, <?= $lessonId ?>, true)">
                                        ✅ Завершить урок
                                    </button>
                                <?php endif; ?>
                            </div>

                            <?php if ($hasTest): ?>
                                <div class="test-container">
                                    <a href="/test/show?lesson_id=<?= $lessonId ?>" class="btn btn-primary">
                                        🧪 Пройти тест
                                        <?php if ($testPassed): ?>
                                            (Пройден)
                                        <?php endif; ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($hasTest): ?>
                            <div class="test-info">
                                <p class="test-status <?= $testPassed ? 'status-passed' : 'status-failed' ?>">
                                    <?= $testPassed ? '✅ Тест пройден успешно' : '❌ Тест не пройден' ?>
                                </p>
                                <p>Для завершения урока необходимо пройти тестирование</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const headers = document.querySelectorAll('.lesson-header');
    headers.forEach(header => {
        header.addEventListener('click', (e) => {
            if (e.target.closest('.teacher-actions') || e.target.closest('.action-btn')) {
                return;
            }
            
            const lessonId = header.getAttribute('data-lesson');
            const content = document.getElementById('lesson-' + lessonId);
            
            document.querySelectorAll('.lesson-content').forEach(item => {
                if (item !== content) {
                    item.classList.remove('active');
                }
            });
            
            content.classList.toggle('active');
        });
    });
});

async function toggleProgress(courseId, lessonId, complete) {
    try {
        const response = await fetch('/course/progress', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ course_id: courseId, lesson_id: lessonId, complete: complete })
        });
        
        const data = await response.json();
        
        if (!data || !data.success) {
            throw new Error(data?.message || 'Ошибка сервера');
        }

        // Обновляем кнопку прогресса - используем новый формат
        const progressContainer = document.getElementById('progress-' + lessonId);
        if (complete) {
            progressContainer.innerHTML = `
                <button class="btn btn-secondary" onclick="toggleProgress(${courseId}, ${lessonId}, false)">
                    ❌ Отменить завершение
                </button>
            `;
        } else {
            progressContainer.innerHTML = `
                <button class="btn btn-success" onclick="toggleProgress(${courseId}, ${lessonId}, true)">
                    ✅ Завершить урок
                </button>
            `;
        }
        
        const statusBadge = document.querySelector(`[data-lesson="${lessonId}"] .status-badge`);
        if (data.topicDone) {
            statusBadge.className = 'status-badge status-complete';
        } else if (data.partial) {
            statusBadge.className = 'status-badge status-partial';
        } else {
            statusBadge.className = 'status-badge status-pending';
        }
        
        document.getElementById('course-progress-text').textContent = 
            data.completedCount + ' из ' + data.totalLessons + ' уроков завершено';
        
        const progressBar = document.getElementById('course-progress-bar');
        progressBar.style.width = data.percent + '%';
        
        progressBar.style.transition = 'width 0.8s ease-in-out';
        
    } catch (error) {
        console.error('Ошибка:', error);
        alert('Ошибка при сохранении прогресса: ' + error.message);
    }
}
</script>
</body>
</html>