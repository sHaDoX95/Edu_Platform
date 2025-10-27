<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Вход в систему</title>
    <style>
        .oauth-section {
            text-align: center;
        }

        .oauth-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
            color: #666;
            font-size: 0.9em;
        }

        .oauth-divider::before,
        .oauth-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e9ecef;
        }

        .oauth-divider span {
            padding: 0 15px;
        }

        /* Стили для кнопки Яндекс ID как на скриншоте */
        .yandex-id-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: #000;
            border: 1px solid #d9d9d9;
            border-radius: 4px;
            padding: 10px 16px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 1.1em;
            font-weight: 400;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            min-width: 200px;
            box-sizing: border-box;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .yandex-id-button:hover {
            background: #f8f9fa;
            border-color: #b3b3b3;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        }

        .yandex-id-button:active {
            background: #f0f0f0;
            transform: translateY(0);
        }

        .yandex-id-button__icon {
            margin-right: 12px;
            width: 18px;
            height: 18px;
            background: #fc3f1d;
            border-radius: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            color: #fff;
            font-family: Arial, sans-serif;
        }

        .oauth-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
        }

        /* Дополнительные стили для соответствия вашему дизайну */
        .auth-form {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <div class="auth-decoration decoration-1"></div>
        <div class="auth-decoration decoration-2"></div>

        <h1 class="auth-title">🔐 Вход</h1>

        <form method="POST" class="auth-form">
            <div class="auth-input-wrapper">
                <span class="auth-input-icon">📧</span>
                <input type="email" name="email" class="auth-input" placeholder="Ваш email" required>
            </div>

            <div class="auth-input-wrapper">
                <span class="auth-input-icon">🔒</span>
                <input type="password" name="password" class="auth-input" placeholder="Ваш пароль" required>
            </div>

            <button type="submit" class="auth-button">Войти в систему</button>
        </form>

        <div class="oauth-divider">
            <span>или войти с помощью</span>
        </div>

        <div class="oauth-section">
            <div class="oauth-buttons">
                <a href="/auth/yandex/login" class="yandex-id-button">
                    <span class="yandex-id-button__icon">Я</span>
                    Войти через Яндекс ID
                </a>
            </div>
        </div>

        <div class="auth-link">
            <p>Нет аккаунта? <a href="/auth/register">Зарегистрироваться</a></p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="auth-error">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="auth-success">
                ✅ <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>