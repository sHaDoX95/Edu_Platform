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
    <h1 class="hero-title">Пользователи</h1>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flash-message flash-error"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <section class="admin-form">
        <h3 class="admin-form-title">Создать пользователя</h3>
        <form method="POST" action="/admin/storeUser" class="admin-form-grid">
            <div>
                <input type="text" name="name" placeholder="Имя" class="form-input" required>
            </div>
            <div>
                <input type="email" name="email" placeholder="Email" class="form-input" required>
            </div>
            <div>
                <input type="password" name="password" placeholder="Пароль" class="form-input" required>
            </div>
            <div>
                <select name="role" class="form-input" required>
                    <option value="student">Студент</option>
                    <option value="teacher">Преподаватель</option>
                    <option value="admin">Админ</option>
                </select>
            </div>
            <div>
                <button type="submit" class="course-action">Добавить пользователя</button>
            </div>
        </form>
    </section>

    <section>
        <h3 class="admin-form-title">Список пользователей</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['id']) ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
                        <td>
                            <span class="status-badge <?= $u['blocked'] ? 'status-blocked' : 'status-active' ?>">
                                <?= $u['blocked'] ? '🚫 Заблокирован' : '✅ Активен' ?>
                            </span>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <form method="POST" action="/admin/updateUser" class="table-form">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    
                                    <select name="role" class="form-input">
                                        <option value="student" <?= $u['role']==='student'?'selected':'' ?>>Студент</option>
                                        <option value="teacher" <?= $u['role']==='teacher'?'selected':'' ?>>Преподаватель</option>
                                        <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>Админ</option>
                                    </select>
                                    
                                    <select name="blocked" class="form-input">
                                        <option value="0" <?= !$u['blocked']?'selected':'' ?>>Активен</option>
                                        <option value="1" <?= $u['blocked']?'selected':'' ?>>Заблокирован</option>
                                    </select>
                                    
                                    <button type="submit" class="admin-btn btn-save btn-small">💾 Сохранить</button>
                                </form>

                                <form method="POST" action="/admin/deleteUser" onsubmit="return confirm('Удалить пользователя?');">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="admin-btn btn-delete btn-small">❌ Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="admin-form">
        <h3 class="admin-form-title">Прикрепить студента к преподавателю</h3>
        <form method="POST" action="/admin/attachStudent" class="admin-form-inline">
            <select name="student_id" class="form-input" required>
                <option value="">— выберите студента —</option>
                <?php foreach ($unassignedStudents as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['email']) ?>)</option>
                <?php endforeach; ?>
            </select>

            <select name="teacher_id" class="form-input" required>
                <option value="">— выберите преподавателя —</option>
                <?php foreach ($teachers as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="course-action">Прикрепить</button>
        </form>
    </section>

    <section style="margin-top: 30px;">
        <a href="/admin" class="course-action">← Вернуться в админку</a>
    </section>
</div>
</body>
</html>