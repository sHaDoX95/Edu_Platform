<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../models/SystemSetting.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = Auth::user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['settings'])) {
    $settings = $_POST['settings'];
    foreach ($settings as $k => $v) {
        if ($v === 'true' || $v === 'false') $settings[$k] = $v;
    }
    SystemSetting::updateMany($settings);

    $_SESSION['flash_success'] = '✅ Настройки успешно обновлены';
    header('Location: /admin/settings');
    exit;
}

$settingsMap = SystemSetting::all();
?>
<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Настройки системы — Админка</title>
    <style>
        .flash-message {
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
            opacity: 1;
            transition: opacity 0.5s ease;
        }

        .flash-success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>

<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name'] ?? 'Администратор') ?></strong> |
            <a href="/admin">🏠 Админ-панель</a> |
            <a href="/admin/systemLogs">📜 Логи</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="container">
        <h1 class="hero-title">⚙️ Системные настройки</h1>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="flash-message flash-success" id="flash-success">
                <?= $_SESSION['flash_success'];
                unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($settingsMap)): ?>
            <p>Настройки ещё не заданы. Добавьте их вручную в таблицу <code>system_settings</code>.</p>
        <?php else: ?>
            <form method="post" class="admin-form">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Ключ</th>
                            <th>Значение</th>
                            <th>Описание</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($settingsMap as $key => $setting): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($key) ?></strong></td>
                                <td>
                                    <?php if ($key === 'registration_enabled'): ?>
                                        <select name="settings[<?= htmlspecialchars($key) ?>]" class="form-input">
                                            <option value="true" <?= $setting['value'] === 'true' ? 'selected' : '' ?>>Разрешена</option>
                                            <option value="false" <?= $setting['value'] === 'false' ? 'selected' : '' ?>>Запрещена</option>
                                        </select>
                                    <?php else: ?>
                                        <input type="text"
                                            name="settings[<?= htmlspecialchars($key) ?>]"
                                            value="<?= htmlspecialchars($setting['value']) ?>"
                                            class="form-input"
                                            style="width: 100%">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($setting['description'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="margin-top:20px;">
                    <button type="submit" class="admin-btn btn-primary">💾 Сохранить изменения</button>
                    <a href="/admin" class="admin-btn btn-secondary">↩ Назад</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        const flash = document.getElementById('flash-success');
        if (flash) {
            setTimeout(() => {
                flash.style.opacity = '0';
                setTimeout(() => flash.remove(), 500);
            }, 2000);
        }
    </script>
</body>

</html>