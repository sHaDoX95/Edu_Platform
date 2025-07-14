<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Вход</title>
</head>
<body>
    <div class="container">
        <h2>Вход</h2>
        <form method="POST">
            <label>Email:
                <input type="email" name="email" required>
            </label>
            <label>Пароль:
                <input type="password" name="password" required>
            </label>
            <button type="submit">Войти</button>
        </form>
        <p><a href="/auth/register">Нет аккаунта? Зарегистрироваться</a></p>
        <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
    </div>
</body>
</html>