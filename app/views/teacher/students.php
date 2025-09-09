<?php $user = Auth::user(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Прогресс студентов</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <a href="/teacher">👨‍🏫 Личный кабинет</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="container">
        <h2>📊 Прогресс студентов по курсу "<?= htmlspecialchars($course['title']) ?>"</h2>

        <?php if (empty($students)): ?>
            <p>Пока никто не начал проходить этот курс.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Студент</th>
                    <th>Уроки</th>
                    <th>Тесты</th>
                    <th>Прогресс (%)</th>
                </tr>
                <?php foreach ($students as $s): ?>
                    <?php
                        $progress = $s['total_lessons'] > 0
                            ? round(($s['completed_lessons'] / $s['total_lessons']) * 100)
                            : 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($s['user_name']) ?></td>
                        <td><?= $s['completed_lessons'] ?> / <?= $s['total_lessons'] ?></td>
                        <td><?= $s['passed_tests'] ?></td>
                        <td><?= $progress ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>