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
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('#replyForm');
        const messageInput = form.querySelector('textarea[name="message"]');
        const repliesContainer = document.querySelector('#replies');
        const ticketId = <?= $ticket['id'] ?>;
        
        // Находим максимальный ID сообщения при загрузке страницы
        let lastReplyId = 0;
        const initialMessages = repliesContainer.querySelectorAll('.message-card');
        if (initialMessages.length > 0) {
            initialMessages.forEach(msg => {
                const messageId = msg.dataset.messageId;
                if (messageId && messageId > lastReplyId) {
                    lastReplyId = parseInt(messageId);
                }
            });
        }

        console.log('Initialized admin ticket chat for ticket ID:', ticketId, 'Last reply ID:', lastReplyId);

        // Функция для загрузки новых сообщений
        async function loadNewMessages() {
            try {
                console.log('Loading new messages from ID:', lastReplyId);
                const response = await fetch(`/admin/support/get-replies?ticket_id=${ticketId}&last_reply_id=${lastReplyId}&_=${Date.now()}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('API response:', result);

                if (result.success && result.newMessages && result.newMessages.length > 0) {
                    console.log('New messages found:', result.newMessages.length);
                    
                    let newMessagesAdded = false;
                    
                    // Добавляем новые сообщения
                    result.newMessages.forEach(message => {
                        console.log('Adding new message:', message);
                        const div = document.createElement('div');
                        div.classList.add('message-card');
                        div.dataset.messageId = message.id;

                        let roleClass = 'user-message';
                        let roleBadge = '';

                        if (message.role === 'admin') {
                            roleClass = 'admin-message';
                            roleBadge = '<span class="admin-badge">Администратор</span>';
                        }

                        div.className = `message-card ${roleClass}`;
                        div.innerHTML = `
                            <div class="message-header">
                                <strong class="message-author">
                                    ${message.name}
                                    ${roleBadge}
                                </strong>
                                <span class="message-time">${message.time}</span>
                            </div>
                            <div class="message-content">${message.message}</div>
                        `;

                        // Убираем состояние "пусто", если оно есть
                        const emptyState = repliesContainer.querySelector('.empty-state');
                        if (emptyState) {
                            emptyState.remove();
                        }

                        repliesContainer.appendChild(div);
                        newMessagesAdded = true;
                        
                        // Обновляем lastReplyId
                        if (message.id > lastReplyId) {
                            lastReplyId = message.id;
                        }
                    });

                    if (newMessagesAdded) {
                        // Прокручиваем к новому сообщению
                        repliesContainer.scrollTop = repliesContainer.scrollHeight;
                        console.log('Last reply ID updated to:', lastReplyId);
                    }
                } else if (result.error) {
                    console.error('API error:', result.error);
                }
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }

        // Автообновление каждые 3 секунды
        setInterval(loadNewMessages, 3000);

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);

            try {
                console.log('Sending message...');
                const response = await fetch('/admin/support/reply', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();
                console.log('Send message response:', result);

                if (result.error) {
                    alert(result.error);
                    return;
                }

                if (result.success) {
                    // Очищаем поле ввода
                    messageInput.value = '';
                    messageInput.focus();

                    // Ждем немного и загружаем новые сообщения
                    setTimeout(loadNewMessages, 1000);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ошибка при отправке сообщения');
            }
        });

        // Прокрутка вниз при загрузке страницы
        repliesContainer.scrollTop = repliesContainer.scrollHeight;
        
        // Устанавливаем lastReplyId на основе количества сообщений
        if (initialMessages.length > 0) {
            lastReplyId = Math.max(lastReplyId, initialMessages.length);
        }
        
        console.log('Initial lastReplyId set to:', lastReplyId);
        
        // Первая загрузка сообщений через секунду
        setTimeout(loadNewMessages, 1000);
    });
</script>

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

        <section class="messages-section">
            <h3 class="admin-form-title">💬 История</h3>
            <?php if (empty($replies)): ?>
                <div class="empty-state">
                    <div style="font-size:4em;margin-bottom:20px;opacity:0.5;">💭</div>
                    <h3>Ответов пока нет</h3>
                    <p>Будьте первым, кто ответит пользователю</p>
                </div>
            <?php else: ?>
                <div id="replies" class="messages-container">
                    <?php foreach ($replies as $index => $reply):
                        $isAdmin = isset($reply['role']) && $reply['role'] === 'admin';
                    ?>
                        <div class="message-card <?= $isAdmin ? 'admin-message' : 'user-message' ?>" data-message-id="<?= $reply['id'] ?? ($index + 1) ?>">
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
            <form id="replyForm" method="POST" action="/admin/support/reply" class="admin-form-grid">
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
        </section>
    </div>

    <script>
        (function() {
            const ticketId = <?= json_encode((int)$ticket['id']) ?>;
            const statusSelect = document.getElementById('status-select');
            const statusBadge = document.getElementById('status-badge');
            const statusMap = <?= json_encode($statusLabels, JSON_UNESCAPED_UNICODE) ?>;

            const replyForm = document.getElementById('replyForm');

            function createDeleteFormElement() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/support/delete';
                form.style.display = 'inline-block';
                form.style.marginLeft = '10px';
                form.onsubmit = function() {
                    return confirm('Вы уверены, что хотите удалить этот тикет?');
                };

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'ticket_id';
                hidden.value = String(ticketId);
                form.appendChild(hidden);

                const br = document.createElement('br');
                form.appendChild(br);

                const btn = document.createElement('button');
                btn.type = 'submit';
                btn.className = 'admin-btn btn-delete btn-small';
                btn.style.background = '#dc3545';
                btn.style.color = 'white';
                btn.textContent = '❌ Удалить тикет';
                form.appendChild(btn);

                return form;
            }

            function ensureDeleteButtonPresent() {
                if (document.getElementById('ticket-delete-form')) return;

                const container = document.createElement('div');
                container.id = 'ticket-delete-form';
                container.style.marginTop = '10px';

                const form = createDeleteFormElement();
                container.appendChild(form);

                if (replyForm && replyForm.parentNode) {
                    replyForm.parentNode.insertBefore(container, replyForm.nextSibling);
                } else {
                    const header = document.querySelector('.ticket-header');
                    if (header) header.appendChild(container);
                }
            }

            function removeDeleteButton() {
                const existing = document.getElementById('ticket-delete-form');
                if (existing && existing.parentNode) existing.parentNode.removeChild(existing);
            }

            statusSelect.addEventListener('change', async function() {
                const newStatus = this.value;
                const container = this.closest('.form-input') || this.parentNode;
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

                        if (newStatus === 'closed') {
                            ensureDeleteButtonPresent();
                        } else {
                            removeDeleteButton();
                        }

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

            if (<?= json_encode($ticket['status']) ?> === 'closed') {
                ensureDeleteButtonPresent();
            }
        })();
    </script>
</body>

</html>