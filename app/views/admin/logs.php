<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Админка — Логи системы</title>
</head>

<body>
<nav>
    <p>
        Вы вошли как <strong><?= htmlspecialchars(Auth::user()['name'] ?? 'Администратор') ?></strong> |
        <a href="/admin">🛠️ Админ-панель</a> |
        <a href="/admin/users">👥 Пользователи</a> |
        <a href="/auth/logout">🚪 Выйти</a>
    </p>
</nav>

<div class="container">
    <h1 class="hero-title">Системные логи</h1>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flash-message flash-error">
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="flash-message flash-success">
            <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <section class="filter-form">
        <h3 class="admin-form-title">Фильтры</h3>
        <form method="get" class="filter-grid">
            <div>
                <label class="form-label">Пользователь ID:</label>
                <input type="text" name="user_id" value="<?= htmlspecialchars($_GET['user_id'] ?? '') ?>" class="form-input" placeholder="ID пользователя">
            </div>
            <div>
                <label class="form-label">Действие:</label>
                <input type="text" name="action" value="<?= htmlspecialchars($_GET['action'] ?? '') ?>" class="form-input" placeholder="Тип действия">
            </div>
            <div>
                <label class="form-label">От:</label>
                <input type="date" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>" class="form-input">
            </div>
            <div>
                <label class="form-label">До:</label>
                <input type="date" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>" class="form-input">
            </div>
            <div class="form-actions">
                <button type="submit" class="admin-btn btn-primary">🔍 Фильтровать</button>
                <a href="/admin/systemLogs" class="admin-btn btn-secondary">🔄 Сбросить</a>
            </div>
        </form>
    </section>

    <section class="export-section" style="margin-top: 10px;">
        <a href="/admin/exportLogsCsv?<?= http_build_query($_GET) ?>" class="admin-btn btn-primary">📊 Экспорт в CSV</a>
    </section>

    <section>
        <h3 class="admin-form-title">Записи логов</h3>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Пользователь</th>
                    <th>Действие</th>
                    <th>IP</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= $log['id'] ?></td>
                        <td>
                            <?php if ($log['user_id']): ?>
                                <?= htmlspecialchars($log['user_name'] ?? 'Пользователь') ?>
                                <span style="color:#666;font-size:0.9em;">(ID: <?= $log['user_id'] ?>)</span>
                            <?php else: ?>
                                <span class="system-badge">Система</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($log['action']) ?>
                            <?php if (!empty($log['details'])): ?>
                                : <?= htmlspecialchars($log['details']) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($log['ip'] ?? '-') ?></td>
                        <td><?= date('d.m.Y H:i:s', strtotime($log['created_at'])) ?></td>
                        <td>
                            <a href="/admin/deleteLog?id=<?= $log['id'] ?>" 
                               class="admin-btn btn-danger btn-small" 
                               onclick="return confirm('Удалить лог #<?= $log['id'] ?>?');">🗑️ Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (empty($logs)): ?>
            <div style="text-align:center; padding:40px; color:#6c757d;">
                <div style="font-size:3em; margin-bottom:15px; opacity:0.5;">📝</div>
                <h3>Записи не найдены</h3>
                <p>Попробуйте изменить параметры фильтрации</p>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $page): ?>
                    <strong><?= $i ?></strong>
                <?php else: ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                        <?= $i ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>