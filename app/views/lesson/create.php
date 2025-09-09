<?php
$user = Auth::user();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=1">
    <title>Добавить урок</title>
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
        <h2>➕ Добавить урок</h2>

        <form method="POST" action="/lesson/store">
            <input type="hidden" name="course_id" value="<?= htmlspecialchars($_GET['course_id']) ?>">

            <label for="title">Название урока:</label><br>
            <input type="text" id="title" name="title" required><br><br>

            <label for="content">Содержимое урока:</label><br>
            <textarea id="content" name="content" rows="8" required></textarea><br><br>

            <button type="submit">💾 Сохранить</button>
        </form>
    </div>
</body>
</html>
