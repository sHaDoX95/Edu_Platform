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
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="flash-error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="flash-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>
            
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
                            <?php
                            $s = $t['status'] ?? 'open';
                            $label = $statusLabels[$s] ?? $s;
                            ?>
                            <tr>
                                <td>#<?= (int)$t['id'] ?></td>
                                <td>
                                    <a href="/support/view?id=<?= (int)$t['id'] ?>" class="ticket-link">
                                        <?= htmlspecialchars($t['subject']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= htmlspecialchars($s) ?>">
                                        <?= htmlspecialchars($label) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($t['created_at'] ?? 'now'))) ?></td>
                                <td>
                                    <a href="/support/view?id=<?= (int)$t['id'] ?>" class="admin-btn btn-view btn-small">
                                        👁️ Просмотреть
                                    </a>
                                    <?php if ($user['id'] === $t['user_id']): ?>
                                        <br>
                                        <form method="POST" action="/admin/support/delete" style="display:inline-block; margin-top:10px;" onsubmit="return confirm('Вы уверены, что хотите удалить этот тикет?')">
                                            <input type="hidden" name="ticket_id" value="<?= (int)$t['id'] ?>">
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
            <?php endif; ?>
        </section>
    </div>
</body>

</html>