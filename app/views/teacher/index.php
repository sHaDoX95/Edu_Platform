<?php
$user = Auth::user();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=1">
    <title>Личный кабинет преподавателя</title>
</head>
<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <a href="/course">📚 Все курсы</a> |
            <a href="/teacher/create">➕ Создать курс</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="container">
        <h2>👨‍🏫 Личный кабинет преподавателя</h2>
        <p>Добро пожаловать, <strong><?= htmlspecialchars($user['name']) ?></strong>!</p>

        <h3>📘 Мои курсы:</h3>

        <?php if (count($courses) === 0): ?>
            <p>У вас пока нет созданных курсов.</p>
        <?php else: ?>
            <ul class="course-list">
                <?php foreach ($courses as $course): ?>
                    <li>
                        <a href="/course/show?id=<?= $course['id'] ?>">
                            <?= htmlspecialchars($course['title']) ?>
                        </a>
                        <a href="/teacher/students?id=<?= $course['id'] ?>">👥 Студенты</a>
                        <small><?= htmlspecialchars($course['description']) ?></small>
                        <br>
                        <a href="/course/show?id=<?= $course['id'] ?>" class="btn">Просмотреть</a> |
                        <a href="/teacher/edit?id=<?= $course['id'] ?>">✏ Редактировать</a> |
                        <a href="/teacher/delete?id=<?= $course['id'] ?>" onclick="return confirm('Удалить курс?')">🗑 Удалить</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>