<?php
$user = Auth::user();
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
        <div class="back-link">
            <a href="/support">← Назад к списку тикетов</a>
        </div>

        <div class="ticket-header">
            <h1 class="hero-title"><?= htmlspecialchars($ticket['subject']) ?></h1>
            <div class="ticket-meta">
                <span class="ticket-id">Тикет #<?= $ticket['id'] ?></span>
                <span class="status-badge status-<?= $ticket['status'] ?>">
                    <?= htmlspecialchars($ticket['status']) ?>
                </span>
                <span class="ticket-date">Создан: <?= date('d.m.Y H:i', strtotime($ticket['created_at'])) ?></span>
            </div>
        </div>

        <section class="messages-section">
            <h3 class="admin-form-title">💬 История переписки</h3>
            
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
                        // Определяем, является ли сообщение от админа
                        // Если у вас есть поле role в users, можно использовать его
                        // Пока сделаем простую проверку по имени или другому полю
                        $isAdmin = isset($r['role']) && $r['role'] === 'admin';
                        ?>
                        <div class="message-card <?= $isAdmin ? 'admin-message' : 'user-message' ?>">
                            <div class="message-header">
                                <strong class="message-author">
                                    <?= htmlspecialchars($r['name']) ?>
                                    <?php if ($isAdmin): ?>
                                        <span class="admin-badge">👑 Админ</span>
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
        </section>
    </div>

    <style>
    .ticket-header {
        background: white;
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        border-left: 4px solid #667eea;
    }

    .ticket-meta {
        display: flex;
        gap: 20px;
        align-items: center;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    .ticket-id {
        background: #f8f9fa;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 500;
        color: #6c757d;
    }

    .ticket-date {
        color: #666;
        font-size: 0.95em;
    }

    .messages-section {
        margin-bottom: 40px;
    }

    .messages-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .message-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }

    .message-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    }

    .admin-message {
        border-left-color: #28a745;
        background: #f8fff9;
    }

    .user-message {
        border-left-color: #667eea;
        background: #f8f9ff;
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }

    .message-author {
        color: #2c3e50;
        font-size: 1.1em;
    }

    .admin-badge {
        background: #28a745;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.8em;
        margin-left: 8px;
    }

    .message-time {
        color: #6c757d;
        font-size: 0.9em;
    }

    .message-content {
        color: #333;
        line-height: 1.6;
        font-size: 1em;
    }

    .admin-form-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    </style>
</body>
</html>