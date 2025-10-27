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
            // Ищем скрытое поле с ID или получаем из данных
            initialMessages.forEach(msg => {
                const messageId = msg.dataset.messageId;
                if (messageId && messageId > lastReplyId) {
                    lastReplyId = messageId;
                }
            });
        }

        console.log('Initialized ticket chat for ticket ID:', ticketId, 'Last reply ID:', lastReplyId);

        // Функция для загрузки новых сообщений
        async function loadNewMessages() {
            try {
                console.log('Loading new messages from ID:', lastReplyId);
                const response = await fetch(`/support/get-replies?ticket_id=${ticketId}&last_reply_id=${lastReplyId}&_=${Date.now()}`);

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
                        } else if (message.role === 'teacher') {
                            roleClass = 'teacher-message';
                            roleBadge = '<span class="teacher-badge">Преподаватель</span>';
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
                const response = await fetch('/support/reply', {
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

        // Добавляем data-message-id к существующим сообщениям
        const existingMessages = repliesContainer.querySelectorAll('.message-card');
        existingMessages.forEach((msg, index) => {
            // Создаем временный ID на основе порядка сообщений
            msg.dataset.messageId = index + 1;
        });

        // Устанавливаем lastReplyId на основе количества сообщений
        if (existingMessages.length > 0) {
            lastReplyId = existingMessages.length;
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

            <div id="replies" class="messages-container">
                <?php if (empty($replies)): ?>
                    <div class="empty-state">
                        <div style="font-size: 4em; margin-bottom: 20px; opacity: 0.5;">💭</div>
                        <h3>Переписка по тикету пока пуста</h3>
                        <p>Опишите вашу проблему более подробно</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($replies as $index => $r): ?>
                        <?php $isAdmin = isset($r['role']) && $r['role'] === 'admin'; ?>
                        <div class="message-card <?= $isAdmin ? 'admin-message' : 'user-message' ?>" data-message-id="<?= $r['id'] ?? ($index + 1) ?>">
                            <div class="message-header">
                                <strong class="message-author">
                                    <?= htmlspecialchars($r['name']) ?>
                                    <?php if ($isAdmin): ?><span class="admin-badge">Администратор</span><?php endif; ?>
                                </strong>
                                <span class="message-time"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></span>
                            </div>
                            <div class="message-content"><?= nl2br(htmlspecialchars($r['message'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
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
            <form id="replyForm" method="POST" action="/support/reply" class="admin-form-grid">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div style="grid-column: 1 / -1;">
                    <textarea name="message" placeholder="Введите ваш ответ..."
                        class="form-input form-textarea" rows="4" required></textarea>
                </div>
                <div>
                    <button type="submit" class="course-action">📤 Отправить</button>
                </div>
            </form>

            <?php if ($user['id'] === $ticket['user_id']): ?>
                <br>
                <form method="POST" action="/admin/support/delete" style="display:inline-block; margin-top:10px;" onsubmit="return confirm('Вы уверены, что хотите удалить этот тикет?')">
                    <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
                    <button type="submit" class="admin-btn btn-delete btn-small" style="background:#dc3545;color:white;">
                        ❌ Удалить тикет
                    </button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</body>

</html>