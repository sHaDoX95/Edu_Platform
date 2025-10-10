<?php
$user = Auth::user();

$statusLabels = [
    'open' => 'Открыт',
    'in_progress' => 'В работе',
    'closed' => 'Закрыт'
];
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Тикет #<?= $ticket['id'] ?></title>
</head>

<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <?php if ($user['role'] === 'teacher'): ?>
                <a href="/teacher">👨‍🏫 Личный кабинет</a> |
            <?php elseif ($user['role'] === 'admin'): ?>
                <a href="/admin">🛠️ Админ-панель</a> |
            <?php else: ?>
                <a href="/user">👤 Личный кабинет</a> |
            <?php endif; ?>
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="container">
        <div>
            <a href="/support" class="back-link">← Назад к списку тикетов</a>
        </div>

        <div class="ticket-header">
            <h1 class="hero-title"><?= htmlspecialchars($ticket['subject']) ?></h1>
            <?php
            $s = $ticket['status'] ?? 'open';
            $label = $statusLabels[$s] ?? $s;
            ?>
            <div class="ticket-meta">
                <span class="ticket-id">Тикет #<?= $ticket['id'] ?></span>
                <span class="status-badge status-<?= htmlspecialchars($s) ?>">
                    <?= htmlspecialchars($label) ?>
                </span>
                <span class="ticket-date">Создан: <?= date('d.m.Y H:i', strtotime($ticket['created_at'])) ?></span>
            </div>
        </div>

        <section class="messages-section">
            <h3 class="admin-form-title">💬 История</h3>

            <?php if (empty($replies)): ?>
                <div class="empty-state">
                    <div style="font-size: 4em; margin-bottom: 20px; opacity: 0.5;">💭</div>
                    <h3>Переписка по тикету пока пуста</h3>
                    <p>Опишите вашу проблему более подробно</p>
                </div>
            <?php else: ?>
                <div class="messages-container">
                    <?php foreach ($replies as $r): ?>
                        <?php
                        $isAdmin = isset($r['role']) && $r['role'] === 'admin';
                        ?>
                        <div class="message-card <?= $isAdmin ? 'admin-message' : 'user-message' ?>">
                            <div class="message-header">
                                <strong class="message-author">
                                    <?= htmlspecialchars($r['name']) ?>
                                    <?php if ($isAdmin): ?>
                                        <span class="admin-badge">Администратор</span>
                                    <?php endif; ?>
                                </strong>
                                <span class="message-time"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></span>
                            </div>
                            <div class="message-content">
                                <?= nl2br(htmlspecialchars($r['message'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="admin-form">
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="flash-message flash-error">
                    <?= htmlspecialchars($_SESSION['flash_error']) ?>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="flash-message flash-success">
                    <?= htmlspecialchars($_SESSION['flash_success']) ?>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>
            <h3 class="admin-form-title">✍️ Добавить ответ</h3>
            <form method="POST" action="/support/reply" class="admin-form-grid">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div style="grid-column: 1 / -1;">
                    <textarea name="message" placeholder="Введите ваш ответ..."
                        class="form-input form-textarea" rows="4" required></textarea>
                </div>
                <div>
                    <button type="submit" class="course-action">📤 Отправить ответ</button>
                </div>
            </form>

            <?php if ($user['id'] === $ticket['user_id']): ?>
                <br>
                <form method="POST" action="/admin/support/delete" style="display:inline-block; margin-top:10px;" onsubmit="return confirm('Вы уверены, что хотите удалить этот тикет?')">
                    <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
                    <button type="submit" class="admin-btn btn-delete btn-small" style="background:#dc3545;color:white;">
                        ❌ Удалить
                    </button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</body>

</html>