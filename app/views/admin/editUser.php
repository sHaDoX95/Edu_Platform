<?php $user = Auth::user(); ?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
<title>Редактирование пользователя</title>
</head>
<body>
<nav>
    <p>
        Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
        <a href="/admin">🛠️ Админ-панель</a> |
        <a href="/auth/logout">🚪 Выйти</a>
    </p>
</nav>

<div class="container">
    <h1 class="hero-title">Редактирование пользователя</h1>

    <form method="POST" action="/admin/updateUserData" class="admin-form-grid">
        <input type="hidden" name="id" value="<?= htmlspecialchars($editUser['id']) ?>">

        <div>
            <input type="text" name="name" value="<?= htmlspecialchars($editUser['name']) ?>" placeholder="Имя" class="form-input" required>
        </div>
        <div>
            <input type="email" name="email" value="<?= htmlspecialchars($editUser['email']) ?>" placeholder="Email" class="form-input" required>
        </div>
        <div>
            <input type="password" name="password" placeholder="Новый пароль (оставьте пустым, если не менять)" class="form-input">
        </div>

        <div>
            <button type="submit" class="course-action">💾 Сохранить изменения</button>
        </div>
    </form>

    <section style="margin-top: 30px;">
        <a href="/admin/users" class="course-action">← Назад к пользователям</a>
    </section>
</div>
</body>
</html>