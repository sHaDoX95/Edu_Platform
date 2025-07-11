<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Регистрация</h2>
        <form method="POST">
            <label>Имя: <input type="text" name="name" required></label><br>
            <label>Email: <input type="email" name="email" required></label><br>
            <label>Пароль: <input type="password" name="password" required></label><br>
            <button type="submit">Зарегистрироваться</button>
        </form>
        <a href="/auth/login">Уже есть аккаунт? Войти</a> 
    </div>
</body>
</html>