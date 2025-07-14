<?php $user = Auth::user(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Курсы</title>
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
        <h2>Доступные курсы</h2>
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
    </div>
</body>
</html>