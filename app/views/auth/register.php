<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Регистрация</title>
</head>
<body>
    <div class="auth-container">
        <div class="auth-decoration decoration-1"></div>
        <div class="auth-decoration decoration-2"></div>
        
        <h1 class="auth-title">🚀 Регистрация</h1>
        
        <form method="POST" class="auth-form">
            <div class="auth-input-wrapper">
                <span class="auth-input-icon">👤</span>
                <input type="text" name="name" class="auth-input" placeholder="Ваше имя" 
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
            
            <div class="auth-input-wrapper">
                <span class="auth-input-icon">📧</span>
                <input type="email" name="email" class="auth-input" placeholder="Ваш email" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            
            <div class="auth-input-wrapper">
                <span class="auth-input-icon">🔒</span>
                <input type="password" name="password" class="auth-input" placeholder="Придумайте пароль" required>
            </div>
            
            <div class="auth-input-wrapper">
                <span class="auth-input-icon">✅</span>
                <input type="password" name="password_confirm" class="auth-input" placeholder="Подтвердите пароль" required>
            </div>
            
            <button type="submit" class="auth-button">Создать аккаунт</button>
        </form>
        
        <div class="auth-link">
            <p>Уже есть аккаунт? <a href="/auth/login">Войти</a></p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="auth-error">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.querySelector('input[name="password"]');
        const confirm = document.querySelector('input[name="password_confirm"]');
        
        if (password.value !== confirm.value) {
            e.preventDefault();
            alert('Пароли не совпадают!');
            confirm.focus();
        }
        
        if (password.value.length < 6) {
            e.preventDefault();
            alert('Пароль должен содержать минимум 6 символов!');
            password.focus();
        }
    });
    </script>
</body>
</html>