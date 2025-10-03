<?php
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Поддержка</title>
</head>
<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <a href="/course">📚 Курсы</a> |
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
        <h1 class="hero-title">Поддержка</h1>

        <section class="admin-form">
            <h3 class="admin-form-title">Создать новый тикет</h3>
            <form method="POST" action="/support/store" class="admin-form-grid">
                <div style="grid-column: 1 / -1;">
                    <input type="text" name="subject" placeholder="Тема обращения" class="form-input" required>
                </div>
                <div style="grid-column: 1 / -1;">
                    <textarea name="message" placeholder="Подробно опишите вашу проблему или вопрос..." 
                              class="form-input form-textarea" rows="5" required></textarea>
                </div>
                <div>
                    <button type="submit" class="course-action">📨 Создать тикет</button>
                </div>
            </form>
        </section>

        <section>
            <h3 class="admin-form-title">Мои тикеты</h3>
            
            <?php if (empty($tickets)): ?>
                <div class="empty-state">
                    <div style="font-size: 4em; margin-bottom: 20px; opacity: 0.5;">📭</div>
                    <h3>У вас пока нет созданных тикетов</h3>
                    <p>Если у вас возникли вопросы или проблемы, создайте первый тикет обратной связи</p>
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Тема</th>
                            <th>Статус</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td>#<?= $t['id'] ?></td>
                                <td>
                                    <a href="/support/view?id=<?= $t['id'] ?>" class="ticket-link">
                                        <?= htmlspecialchars($t['subject']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $t['status'] ?>">
                                        <?= htmlspecialchars($t['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($t['created_at'])) ?></td>
                                <td>
                                    <a href="/support/view?id=<?= $t['id'] ?>" class="admin-btn btn-view btn-small">
                                        👁️ Просмотреть
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>

    <style>
    .ticket-link {
        color: #2c3e50;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .ticket-link:hover {
        color: #667eea;
        text-decoration: underline;
    }

    .status-open {
        background: #e8f5e8;
        color: #28a745;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 500;
    }

    .status-pending {
        background: #fff6d1;
        color: #ffc107;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 500;
    }

    .status-closed {
        background: #f8f9fa;
        color: #6c757d;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 500;
    }

    .status-resolved {
        background: #e6f7ff;
        color: #17a2b8;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 500;
    }

    .admin-form-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    </style>
</body>
</html>