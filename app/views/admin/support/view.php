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
    <title>Тикет #<?= (int)$ticket['id'] ?> — Админ</title>
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
        <div>
            <a href="/admin/support" class="back-link">← Назад к списку тикетов</a>
        </div>

        <div class="ticket-header">
            <h1 class="hero-title"><?= htmlspecialchars($ticket['subject']) ?></h1>
            <div class="ticket-meta">
                <div class="user-info">
                    <strong>👤 Пользователь:</strong>
                    <span><?= htmlspecialchars($ticket['user_name']) ?></span>
                </div>
                <div class="ticket-info">
                    <span class="ticket-id">Тикет #<?= (int)$ticket['id'] ?></span>
                    <span id="status-badge" class="status-badge status-<?= htmlspecialchars($ticket['status']) ?>">
                        <?= htmlspecialchars($statusLabels[$ticket['status']] ?? $ticket['status']) ?>
                    </span>
                    <span class="ticket-date">Создан: <?= htmlspecialchars(date('d.m.Y H:i', strtotime($ticket['created_at']))) ?></span>
                </div>
            </div>
        </div>

        <section class="initial-message">
            <h3 class="admin-form-title">📝 Обращение пользователя</h3>
            <div class="message-card user-message">
                <div class="message-header">
                    <strong class="message-author"><?= htmlspecialchars($ticket['user_name']) ?></strong>
                    <span class="message-time"><?= htmlspecialchars(date('d.m.Y H:i', strtotime($ticket['created_at']))) ?></span>
                </div>
                <div class="message-content"><?= nl2br(htmlspecialchars($ticket['message'])) ?></div>
            </div>
        </section>

        <section class="messages-section">
            <h3 class="admin-form-title">💬 История</h3>
            <?php if (empty($replies)): ?>
                <div class="empty-state">
                    <div style="font-size:4em;margin-bottom:20px;opacity:0.5;">💭</div>
                    <h3>Ответов пока нет</h3>
                    <p>Будьте первым, кто ответит пользователю</p>
                </div>
            <?php else: ?>
                <div class="messages-container">
                    <?php foreach ($replies as $reply):
                        $isAdmin = (isset($reply['role']) && $reply['role'] === 'admin') || ($reply['name'] === $user['name']);
                    ?>
                        <div class="message-card <?= $isAdmin ? 'admin-message' : 'user-message' ?>">
                            <div class="message-header">
                                <strong class="message-author">
                                    <?= htmlspecialchars($reply['name']) ?>
                                    <?php if ($isAdmin): ?><span class="admin-badge">Администратор</span><?php endif; ?>
                                </strong>
                                <span class="message-time"><?= htmlspecialchars(date('d.m.Y H:i', strtotime($reply['created_at']))) ?></span>
                            </div>
                            <div class="message-content"><?= nl2br(htmlspecialchars($reply['message'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="admin-form">
            <h3 class="admin-form-title">✍️ Ответить пользователю</h3>
            <form method="POST" action="/admin/support/reply" class="admin-form-grid">
                <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
                <div style="grid-column: 1 / -1;">
                    <textarea name="message" placeholder="Введите ваш ответ пользователю..." class="form-input form-textarea" rows="5" required></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="course-action">📤 Отправить ответ</button>
                    <div class="status-actions">
                        <label>Изменить статус:</label>
                        <select id="status-select" name="status" class="form-input">
                            <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>><?= $statusLabels['open'] ?></option>
                            <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>><?= $statusLabels['in_progress'] ?></option>
                            <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>><?= $statusLabels['closed'] ?></option>
                        </select>
                    </div>
                </div>
            </form>

            <?php if ($ticket['status'] === 'closed' && ($user['role'] === 'admin' || $user['id'] === $ticket['user_id'])): ?>
                <form method="POST" action="/admin/support/delete" style="display:inline-block;margin-left:10px;" onsubmit="return confirm('Вы уверены, что хотите удалить этот тикет?')">
                    <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
                    <br>
                    <button type="submit" class="admin-btn btn-delete btn-small" style="background:#dc3545;color:white;">
                        ❌ Удалить тикет
                    </button>
                </form>
            <?php endif; ?>
        </section>
    </div>

    <script>
        (function() {
            const ticketId = <?= json_encode((int)$ticket['id']) ?>;
            const statusSelect = document.getElementById('status-select');
            const statusBadge = document.getElementById('status-badge');
            const statusMap = <?= json_encode($statusLabels, JSON_UNESCAPED_UNICODE) ?>;

            statusSelect.addEventListener('change', async function() {
                const newStatus = this.value;
                const container = this.closest('.form-input');
                container.classList.add('blink');
                this.disabled = true;
                this.style.border = '2px solid #667eea';
                this.style.backgroundColor = '#f8f9ff';

                try {
                    const res = await fetch('/admin/support/updateStatus', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            ticket_id: ticketId,
                            status: newStatus
                        })
                    });

                    const result = await res.json();

                    if (result.success) {
                        container.classList.remove('blink');
                        container.classList.add('save-success');

                        statusBadge.textContent = statusMap[newStatus] || newStatus;
                        statusBadge.className = 'status-badge status-' + newStatus;

                        this.style.border = '2px solid #28a745';
                        this.style.backgroundColor = '#f0fff4';

                        setTimeout(() => {
                            container.classList.remove('save-success');
                            this.style.border = '';
                            this.style.backgroundColor = '';
                            this.disabled = false;
                        }, 1600);
                    } else {
                        throw new Error(result.error || 'Ошибка сервера');
                    }
                } catch (err) {
                    console.error(err);
                    container.classList.remove('blink');
                    container.classList.add('save-error');
                    this.style.border = '2px solid #dc3545';
                    this.style.backgroundColor = '#fff5f5';

                    setTimeout(() => {
                        container.classList.remove('save-error');
                        this.style.border = '';
                        this.style.backgroundColor = '';
                        this.disabled = false;
                    }, 3000);

                    alert('Не удалось сохранить статус: ' + (err.message || 'ошибка'));
                }
            });
        })();
    </script>
</body>

</html>