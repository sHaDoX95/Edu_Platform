<?php
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($chat['title']) ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .chat-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .chat-title {
            font-size: 1.4em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .chat-meta {
            color: #666;
            font-size: 0.9em;
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .message {
            margin-bottom: 15px;
            padding: 12px 15px;
            border-radius: 12px;
            max-width: 80%;
        }

        .message.admin {
            background: #ffe6e6;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }

        .message.teacher {
            background: #e6f3ff;
            margin-right: auto;
            border-bottom-left-radius: 4px;
        }

        .message.student {
            background: #f0f0f0;
            margin-right: auto;
            border-bottom-left-radius: 4px;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.85em;
        }

        .sender-name {
            font-weight: bold;
        }

        .sender-role {
            color: #666;
            font-size: 0.8em;
            margin-left: 8px;
        }

        .message-time {
            color: #999;
            font-size: 0.8em;
        }

        .message-text {
            line-height: 1.4;
        }

        .message-form {
            display: flex;
            gap: 10px;
        }

        .message-input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            font-size: 1em;
        }

        .send-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            cursor: pointer;
            font-weight: 500;
        }

        .send-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #667eea;
            text-decoration: none;
            margin-bottom: 15px;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <?php if ($user['role'] === 'teacher'): ?>
                <a href="/teacher">👨‍🏫 Личный кабинет</a> |
                <a href="/support">🆘 Поддержка</a> |
            <?php elseif ($user['role'] === 'admin'): ?>
                <a href="/chat">← Назад к чатам</a> |
                <a href="/admin">🛠️ Админ-панель</a> |
            <?php else: ?>
                <a href="/user">👤 Личный кабинет</a> |
                <a href="/support">🆘 Поддержка</a> |
            <?php endif; ?>
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="chat-container">
        <div class="chat-header">
            <div class="chat-title"><?= htmlspecialchars($chat['title']) ?></div>
            <div class="chat-meta">
                Преподаватель: <?= htmlspecialchars($chat['teacher_name'] ?? 'Не назначен') ?>
            </div>
        </div>

        <div class="messages-container" id="messagesContainer">
            <?php foreach ($messages as $msg): ?>
                <div class="message <?= $msg['role'] ?>">
                    <div class="message-header">
                        <div>
                            <span class="sender-name"><?= htmlspecialchars($msg['name']) ?></span>
                            <span class="sender-role">(<?= $msg['role'] === 'admin' ? 'Администратор' : ($msg['role'] === 'teacher' ? 'Преподаватель' : 'Студент') ?>)</span>
                        </div>
                        <span class="message-time"><?= date('H:i', strtotime($msg['created_at'])) ?></span>
                    </div>
                    <div class="message-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <form class="message-form" id="messageForm">
            <input type="hidden" name="chat_id" value="<?= $chat['id'] ?>">
            <input type="text" name="message" class="message-input" placeholder="Введите сообщение..." required>
            <button type="submit" class="send-button">📤 Отправить</button>
        </form>
    </div>

    <script>
        const messagesContainer = document.getElementById('messagesContainer');
        const messageForm = document.getElementById('messageForm');
        const chatId = <?= $chat['id'] ?>;

        // Прокрутка вниз при загрузке
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        // Отправка сообщения
        messageForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(messageForm);
            const messageInput = messageForm.querySelector('input[name="message"]');

            try {
                const response = await fetch('/chat/send', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    messageInput.value = '';
                    loadMessages();
                } else {
                    alert('Ошибка: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ошибка отправки сообщения');
            }
        });

        // Загрузка новых сообщений
        async function loadMessages() {
            try {
                const response = await fetch(`/chat/messages/${chatId}?last_id=0`);
                const messages = await response.json();

                // Обновляем контейнер сообщений
                const container = document.getElementById('messagesContainer');
                container.innerHTML = '';

                messages.forEach(msg => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `message ${msg.role}`;
                    messageDiv.innerHTML = `
                        <div class="message-header">
                            <div>
                                <span class="sender-name">${msg.name}</span>
                                <span class="sender-role">(${getRoleText(msg.role)})</span>
                            </div>
                            <span class="message-time">${formatTime(msg.created_at)}</span>
                        </div>
                        <div class="message-text">${escapeHtml(msg.message).replace(/\n/g, '<br>')}</div>
                    `;
                    container.appendChild(messageDiv);
                });

                // Прокрутка вниз
                container.scrollTop = container.scrollHeight;
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }

        function getRoleText(role) {
            switch (role) {
                case 'admin':
                    return 'Администратор';
                case 'teacher':
                    return 'Преподаватель';
                default:
                    return 'Студент';
            }
        }

        function formatTime(dateString) {
            return new Date(dateString).toLocaleTimeString('ru-RU', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        setInterval(loadMessages, 3000);
    </script>
</body>

</html>