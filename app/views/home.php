<?php $user = Auth::user(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h2>👤 Личный кабинет</h2>

        <nav>
            <a href="/course">📚 Курсы</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </nav>

        <hr>

        <p>Добро пожаловать, <strong><?= htmlspecialchars($user['name']) ?></strong>!</p>

        <h3>📈 Прогресс по курсам:</h3>

        <?php if (count($courses) === 0): ?>
            <p>У вас пока нет доступных курсов.</p>
        <?php else: ?>
            <ul>
            <?php foreach ($courses as $course): ?>
                <?php
                    $total = (int)$course['total_lessons'];
                    $done = (int)$course['completed_lessons'];
                    $percent = $total > 0 ? round(($done / $total) * 100) : 0;
                ?>
                <li style="margin-bottom: 10px;">
                    <strong><?= htmlspecialchars($course['title']) ?></strong><br>
                    <small><?= htmlspecialchars($course['description']) ?></small><br>
                    ✅ Пройдено <?= $done ?> из <?= $total ?> уроков (<?= $percent ?>%)
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?= $percent ?>%"></div>
                    </div>
                    <a href="/course/show?id=<?= $course['id'] ?>">📖 Перейти к курсу</a>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
