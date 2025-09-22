<?php
$user = Auth::user();
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
<title>Админ — панель</title>
</head>
<body>
<nav>
    <p>
        Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
        <a href="/course">📚 Курсы</a> |
        <a href="/auth/logout">🚪 Выйти</a>
    </p>
</nav>

<div class="container">
    <h1>Админ-панель</h1>

    <div style="display:grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-top:20px;">
        <div class="course-card">
            <h3>Пользователи</h3>
            <p style="font-size: 1.6em; font-weight:700;"><?= $usersCount ?? 0 ?></p>
            <a href="/admin/users" class="course-action">Управление пользователями</a>
        </div>

        <div class="course-card">
            <h3>Курсы</h3>
            <p style="font-size: 1.6em; font-weight:700;"><?= $coursesCount ?? 0 ?></p>
            <a href="/course" class="course-action">Посмотреть курсы</a>
        </div>

        <div class="course-card">
            <h3>Уроки</h3>
            <p style="font-size: 1.6em; font-weight:700;"><?= $lessonsCount ?? 0 ?></p>
            <a href="/course" class="course-action">Управление</a>
        </div>

        <div class="course-card">
            <h3>Тикеты поддержки</h3>
            <p style="font-size: 1.6em; font-weight:700;"><?= $ticketsCount ?? 0 ?></p>
            <a href="/admin/support" class="course-action">Открыть поддержку</a>
        </div>
    </div>
</div>
</body>
</html>