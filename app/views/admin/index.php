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
    <h1 class="hero-title">Админ-панель</h1>

    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="admin-stat-title">Пользователи</div>
            <div class="admin-stat-number"><?= $usersCount ?? 0 ?></div>
            <a href="/admin/users" class="course-action">Управление пользователями</a>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-title">Курсы</div>
            <div class="admin-stat-number"><?= $coursesCount ?? 0 ?></div>
            <a href="/admin/courses" class="course-action">Посмотреть курсы</a>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-title">Уроки</div>
            <div class="admin-stat-number"><?= $lessonsCount ?? 0 ?></div>
            <a href="/admin/lessons" class="course-action">Управление</a>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-title">Тикеты поддержки</div>
            <div class="admin-stat-number"><?= $ticketsCount ?? 0 ?></div>
            <a href="/admin/support" class="course-action">Открыть поддержку</a>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-title">Системные логи</div>
            <div class="admin-stat-number"><?= $logsCount ?? 0 ?></div>
            <a href="/admin/logs" class="course-action">Посмотреть</a>
        </div>
    </div>
</div>
</body>
</html>