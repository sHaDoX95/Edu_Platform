<?php
    require_once __DIR__ . '/../../models/Test.php';

    $user = Auth::user();
    $completedCount = 0;
    $totalLessons = count($course['lessons']);

    foreach ($course['lessons'] as $lesson) {
        if (Progress::isCompleted($user['id'], $lesson['id']) && Progress::isTestPassed($user['id'], $lesson['id'])) {
            $completedCount++;
        }
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
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
        <p><strong>Прогресс:</strong> <?= $completedCount ?> из <?= $totalLessons ?> тем пройдено</p>

        <h3>Уроки:</h3>
        <ol>
            <?php foreach ($course['lessons'] as $lesson): ?>
                <li>
                    <?php if ($user['role'] === 'teacher'): ?>
                        <strong><?= htmlspecialchars($lesson['title']) ?></strong>
                        <a href="/teacher/editLesson?id=<?= $lesson['id'] ?>">✏ Редактировать</a> |
                        <a href="/teacher/deleteLesson?id=<?= $lesson['id'] ?>&course_id=<?= $course['id'] ?>" onclick="return confirm('Удалить урок?')">🗑 Удалить</a><br>
                    <?php else: ?>
                        <strong><?= htmlspecialchars($lesson['title']) ?></strong><br>
                    <?php endif; ?>    
                
                    <p><?= nl2br(htmlspecialchars($lesson['content'])) ?></p>

                    <?php if (Progress::isCompleted($user['id'], $lesson['id'])): ?>
                        <p style="color: green;">✅ Пройден</p>
                        <a href="?id=<?= $course['id'] ?>&uncomplete=<?= $lesson['id'] ?>">Отметить как НЕ пройденный</a>
                    <?php else: ?>
                        <a href="?id=<?= $course['id'] ?>&complete=<?= $lesson['id'] ?>">Отметить как пройденный</a>
                    <?php endif; ?>

                    <br><br>

                    <?php if (Test::existsForLesson($lesson['id'])): ?>
                        <?php if (Progress::isTestPassed($user['id'], $lesson['id'])): ?>
                            <p style="color: green;">🧪 Тест пройден</p>
                        <?php else: ?>
                            <p style="color: red;">🧪 Тест не пройден</p>
                        <?php endif; ?>
                        <a href="/test/show?lesson_id=<?= $lesson['id'] ?>">📝 Пройти тест</a>
                    <?php endif; ?>
                    <hr>
                </li>
            <?php endforeach; ?>
        </ol>      
    </div>
</body>
</html>