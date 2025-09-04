<?php
$user = Auth::user();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=1">
    <title>Редактировать курс</title>
</head>
<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <a href="/course">📚 Все курсы</a> |
            <a href="/teacher">👨‍🏫 Личный кабинет</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="container">
        <h2>✏️ Редактировать курс</h2>

        <form action="/teacher/update" method="post">
            <input type="hidden" name="id" value="<?= $course['id'] ?>">
            
            <label>
                📖 Название курса:
                <input type="text" name="title" value="<?= htmlspecialchars($course['title']) ?>" required>
            </label>

            <label>
                📝 Описание курса:
                <textarea name="description" rows="5" required style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;"><?= htmlspecialchars($course['description']) ?></textarea>
            </label>

            <button type="submit">💾 Сохранить изменения</button>
        </form>
    </div>
</body>
</html>
