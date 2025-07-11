<?php $user = Auth::user(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <a href="/home">🏠 Личный кабинет</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <h2>Курсы</h2>
    <ul>
    <?php foreach ($courses as $course): ?>
        <li>
            <a href="/course/show?id=<?= $course['id'] ?>">
                <?= htmlspecialchars($course['title']) ?>
            </a><br>
            <small><?= htmlspecialchars($course['description']) ?></small>
        </li>
    <?php endforeach; ?>
    </ul>
</body>
</html>