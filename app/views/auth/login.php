<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Вход в систему</title>
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