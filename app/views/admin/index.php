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
            <a href="/admin/courses">📚 Курсы</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="container">
        <h1 class="hero-title">Админ-панель</h1>

        <div class="admin-stats-grid">
            <div class="admin-stat-card">
                <div class="admin-stat-title">👥 Пользователи</div>
                <div class="admin-stat-number"><?= $usersCount ?? 0 ?></div>
                <a href="/admin/users" class="course-action">Управление пользователями</a>
            </div>

            <div class="admin-stat-card">
                <div class="admin-stat-title">📚 Курсы</div>
                <div class="admin-stat-number"><?= $coursesCount ?? 0 ?></div>
                <a href="/admin/courses" class="course-action">Управление курсами</a>
            </div>

            <div class="admin-stat-card">
                <div class="admin-stat-title">📖 Уроки</div>
                <div class="admin-stat-number"><?= $lessonsCount ?? 0 ?></div>
                <a href="/admin/lessons" class="course-action">Управление уроками</a>
            </div>

            <div class="admin-stat-card">
                <div class="admin-stat-title">💬 Чаты</div>
                <div class="admin-stat-number"><?= $chatsCount ?? 0 ?></div>
                <a href="/admin/chats" class="course-action">Управление чатами</a>
            </div>

            <div class="admin-stat-card">
                <div class="admin-stat-title">🎫 Тикеты</div>
                <div class="admin-stat-number"><?= $ticketsCount ?? 0 ?></div>
                <a href="/admin/support" class="course-action">Поддержка пользователей</a>
            </div>

            <div class="admin-stat-card">
                <div class="admin-stat-title">📊 Логи</div>
                <div class="admin-stat-number"><?= $logsCount ?? 0 ?></div>
                <a href="/admin/systemLogs" class="course-action">Системные логи</a>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-title">Настройки системы</div>
            <div class="admin-stat-number">⚙️</div>
            <a href="/admin/systemSettings" class="course-action">Открыть настройки</a>
        </div>

        <section style="margin-top: 40px; text-align: center;">
            <h3 class="admin-form-title">Быстрые действия</h3>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="/admin/chats/create" class="admin-btn btn-primary">➕ Создать чат</a>
                <a href="/admin/createCourse" class="admin-btn btn-primary">📚 Создать курс</a>
                <a href="/admin/storeUser" class="admin-btn btn-primary">👥 Добавить пользователя</a>
            </div>
        </section>
    </div>
</body>

</html>