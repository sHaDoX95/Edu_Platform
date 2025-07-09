<h2>Вход</h2>
<form method="POST">
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Пароль: <input type="password" name="password" required></label><br>
    <button type="submit">Войти</button>
</form>
<a href="/auth/register">Нет аккаунта? Регистрация</a>
<?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
