<?php
$user = Auth::user();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=1">
    <title>Редактировать урок</title>
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
        <h2>✏ Редактировать урок</h2>

        <form method="POST" action="/lesson/update">
            <input type="hidden" name="id" value="<?= htmlspecialchars($lesson['id']) ?>">

            <label for="title">Название урока:</label><br>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($lesson['title']) ?>" required><br><br>

            <label for="content">Содержимое урока:</label><br>
            <textarea id="content" name="content" rows="8" required><?= htmlspecialchars($lesson['content']) ?></textarea><br><br>

            <button type="submit">💾 Обновить</button>
        </form>
    </div>
</body>
</html>
