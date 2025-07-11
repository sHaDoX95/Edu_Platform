<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h2>Вход</h2>
    <form method="POST">
        <label>Email: <input type="email" name="email" required></label><br>
        <label>Пароль: <input type="password" name="password" required></label><br>
        <button type="submit">Войти</button>
    </form>
    <a href="/auth/register">Нет аккаунта? Регистрация</a>
    <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
</body>
</html>