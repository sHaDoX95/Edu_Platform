<?php
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Админ — Поддержка</title>
</head>
<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <a href="/admin">🛠️ Админ-панель</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="container">
        <h1 class="hero-title">Поддержка пользователей</h1>

        <section>
            <h3 class="admin-form-title">Все тикеты пользователей</h3>
            
            <?php if (empty($tickets)): ?>
                <div class="empty-state">
                    <div style="font-size: 4em; margin-bottom: 20px; opacity: 0.5;">🎉</div>
                    <h3>Нет активных тикетов</h3>
                    <p>Все обращения пользователей обработаны</p>
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Пользователь</th>
                            <th>Тема</th>
                            <th>Статус</th>
                            <th>Обновлён</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>#<?= $ticket['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($ticket['user_name']) ?></strong>
                                    <?php if (isset($ticket['user_email'])): ?>
                                        <br><small><?= htmlspecialchars($ticket['user_email']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/admin/support/view?id=<?= $ticket['id'] ?>" class="ticket-link">
                                        <?= htmlspecialchars($ticket['subject']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $ticket['status'] ?>">
                                        <?= htmlspecialchars($ticket['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($ticket['updated_at'])) ?></td>
                                <td>
                                    <a href="/admin/support/view?id=<?= $ticket['id'] ?>" class="admin-btn btn-view btn-small">
                                        👁️ Открыть
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section style="margin-top: 30px;">
            <a href="/admin" class="course-action">← Вернуться в админку</a>
        </section>
    </div>
</body>
</html>