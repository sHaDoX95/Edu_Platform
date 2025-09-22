<?php
$user = Auth::user();
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
<title>Администрирование — Пользователи</title>
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
    <h1>Пользователи</h1>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="auth-error"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <section style="margin-bottom:30px;">
        <h3>Создать пользователя</h3>
        <form method="POST" action="/admin/users/store" style="display:flex; gap:10px; flex-wrap:wrap;">
            <input name="name" placeholder="Имя" required>
            <input name="email" placeholder="Email" type="email" required>
            <input name="password" placeholder="Пароль" type="password" required>
            <select name="role">
                <option value="student">Студент</option>
                <option value="teacher">Преподаватель</option>
                <option value="admin">Админ</option>
            </select>
            <button class="course-action" type="submit">Добавить</button>
        </form>
    </section>

    <section>
        <h3>Список</h3>
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f0f0f0;">
                    <th style="padding:10px;">ID</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td style="padding:10px;"><?= htmlspecialchars($u['id']) ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
                        <td>
                            <form style="display:inline" method="GET" action="/admin/users/delete" onsubmit="return confirm('Удалить пользователя?');">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button class="course-action" style="background:#dc3545; padding:6px 10px; border-radius:8px; color:#fff;">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section style="margin-top:30px;">
        <h3>Прикрепить студента к преподавателю</h3>
        <form method="POST" action="/admin/attach-student" style="display:flex; gap:10px; align-items:center;">
            <select name="student_id" required>
                <option value="">— выберите студента (не прикреплен) —</option>
                <?php foreach ($unassignedStudents as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['email']) ?>)</option>
                <?php endforeach; ?>
            </select>

            <select name="teacher_id" required>
                <option value="">— выберите преподавателя —</option>
                <?php foreach ($teachers as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <button class="course-action" type="submit">Прикрепить</button>
        </form>
    </section>

    <section style="margin-top:30px;">
        <a href="/admin" class="course-action">← Вернуться в админку</a>
    </section>
</div>
</body>
</html>