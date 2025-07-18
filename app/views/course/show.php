<?php
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
            <a href="/home">🏠 Личный кабинет</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>
    <div class="container">
        <a href="/course">← Назад к списку курсов</a>
        <h2><?= htmlspecialchars($course['title']) ?></h2>
        <p><?= htmlspecialchars($course['description']) ?></p>
        <p><strong>Прогресс:</strong> <?= $completedCount ?> из <?= $totalLessons ?> тем пройдено</p>

        <h3>Уроки:</h3>
        <ol>
            <?php foreach ($course['lessons'] as $lesson): ?>
                <li>
                    <strong><?= htmlspecialchars($lesson['title']) ?></strong><br>
                    <p><?= nl2br(htmlspecialchars($lesson['content'])) ?></p>

                    <?php if (Progress::isCompleted($user['id'], $lesson['id'])): ?>
                        <p style="color: green;">✅ Пройден</p>
                        <a href="?id=<?= $course['id'] ?>&uncomplete=<?= $lesson['id'] ?>">Отметить как НЕ пройденный</a>
                    <?php else: ?>
                        <a href="?id=<?= $course['id'] ?>&complete=<?= $lesson['id'] ?>">Отметить как пройденный</a>
                    <?php endif; ?>

                    <br><br>
                    <?php if (Progress::isTestPassed($user['id'], $lesson['id'])): ?>
                        <p style="color: green;">🧪 Тест пройден</p>
                    <?php else: ?>
                        <p style="color: red;">🧪 Тест не пройден</p>
                    <?php endif; ?>
                    <a href="/test/show?lesson_id=<?= $lesson['id'] ?>">📝 Пройти тест</a>
                    <hr>
                </li>
            <?php endforeach; ?>
        </ol>      
    </div>
</body>
</html>