<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Регистрация</title>
</head>
<body>
    <div class="container">
        <h2>Регистрация</h2>
        <form method="POST">
            <label>Имя:
                <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </label>
            <label>Email:
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </label>
            <label>Пароль:
                <input type="password" name="password" required>
            </label>
            <label>Подтвердите пароль:
                <input type="password" name="password_confirm" required>
            </label>
            <button type="submit">Зарегистрироваться</button>
        </form>
        <p><a href="/auth/login">Уже есть аккаунт? Войти</a></p>
        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>