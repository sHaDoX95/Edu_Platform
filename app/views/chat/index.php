<?php
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои чаты</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .chats-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }

        .chats-grid {
            display: grid;
            gap: 15px;
            margin-top: 20px;
        }

        .chat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .chat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .chat-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 8px;
            color: #2c3e50;
        }

        .chat-meta {
            color: #666;
            font-size: 0.9em;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
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
            <a href="/support">🆘 Поддержка</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="chats-container">
        <h1 class="hero-title">💬 Мои чаты</h1>

        <?php if (!empty($chats)): ?>
            <div class="chats-grid">
                <?php foreach ($chats as $chat): ?>
                    <a href="/chat/view/<?= $chat['id'] ?>" class="chat-card">
                        <div class="chat-title"><?= htmlspecialchars($chat['title']) ?></div>
                        <div class="chat-meta">
                            Преподаватель: <?= htmlspecialchars($chat['teacher_name'] ?? 'Не назначен') ?> |
                            Создан: <?= date('d.m.Y H:i', strtotime($chat['created_at'])) ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div>💬</div>
                <h3>У вас пока нет чатов</h3>
                <p>Ожидайте, когда администратор добавит вас в чат</p>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>