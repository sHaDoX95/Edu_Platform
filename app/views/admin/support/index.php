<?php
$user = Auth::user();

$statusLabels = [
    'open' => 'Открыт',
    'in_progress' => 'В работе',
    'closed' => 'Закрыт'
];

$q = $_GET['q'] ?? '';
$filterStatus = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
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
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="flash-message flash-success"><?= htmlspecialchars($_SESSION['flash_success']);
                                                        unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="flash-message flash-error"><?= htmlspecialchars($_SESSION['flash_error']);
                                                    unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <h1 class="hero-title">Поддержка пользователей</h1>

        <section>
            <h3 class="admin-form-title">Все тикеты пользователей</h3>

            <form method="get" action="/admin/support" class="search-form" style="margin-bottom:12px;">
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Поиск по ID">
                <select name="status" style="border-radius: 4px;">
                    <option value="">Все статусы</option>
                    <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $filterStatus === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Поиск</button>
                <?php if (!empty($q) || !empty($filterStatus)): ?>
                    <a href="/admin/support" style="margin-left:8px;">Сбросить</a>
                <?php endif; ?>
            </form>

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
                                <td>#<?= (int)$ticket['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($ticket['user_name']) ?></strong>
                                    <?php if (!empty($ticket['user_email'])): ?>
                                        <br><small><?= htmlspecialchars($ticket['user_email']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/admin/support/view?id=<?= (int)$ticket['id'] ?>" class="ticket-link">
                                        <?= htmlspecialchars($ticket['subject']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php $s = $ticket['status'] ?? 'open'; ?>
                                    <span class="status-badge status-<?= htmlspecialchars($s) ?>">
                                        <?= htmlspecialchars($statusLabels[$s] ?? $s) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($ticket['updated_at'] ?? $ticket['created_at'] ?? 'now'))) ?></td>
                                <td>
                                    <a href="/admin/support/view?id=<?= (int)$ticket['id'] ?>" class="admin-btn btn-view btn-small">
                                        👁️ Открыть
                                    </a>
                                    <?php if ($ticket['status'] === 'closed' && ($user['role'] === 'admin' || $user['id'] === $ticket['user_id'])): ?>
                                        <br>
                                        <form method="POST" action="/admin/support/delete" style="display:inline-block; margin-top:10px;" onsubmit="return confirm('Вы уверены, что хотите удалить этот тикет?')">
                                            <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
                                            <button type="submit" class="admin-btn btn-delete btn-small" style="background:#dc3545;color:white;">
                                                ❌ Удалить
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($pages > 1 && $user['role'] === 'admin'): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <a href="?page=<?= $i ?>&q=<?= urlencode($q ?? '') ?>&status=<?= urlencode($status ?? '') ?>"
                                class="<?= $i == $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <form method="POST" action="/admin/support/deleteClosed" onsubmit="return confirm('Вы уверены, что хотите удалить все закрытые тикеты?');" style="margin-bottom:15px;">
                        <button type="submit" class="admin-btn btn-delete">❌ Удалить все закрытые тикеты</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <section style="margin-top: 30px;">
            <a href="/admin" class="course-action">← Вернуться в админку</a>
        </section>
    </div>
</body>

</html>