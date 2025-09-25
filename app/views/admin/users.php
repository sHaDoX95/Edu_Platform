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
        <form method="get" action="/admin/users" class="search-form">
            <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Поиск по имени или email">
            <select name="role" style="border-radius: 4px;">
                <option value="">Все роли</option>
                <option value="admin" <?= ($_GET['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Администраторы</option>
                <option value="teacher" <?= ($_GET['role'] ?? '') === 'teacher' ? 'selected' : '' ?>>Преподаватели</option>
                <option value="student" <?= ($_GET['role'] ?? '') === 'student' ? 'selected' : '' ?>>Студенты</option>
            </select>
            <select name="status" style="border-radius: 4px;">
                <option value="">Все</option>
                <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Активные</option>
                <option value="blocked" <?= ($_GET['status'] ?? '') === 'blocked' ? 'selected' : '' ?>>Заблокированные</option>
            </select>
            <button type="submit">Поиск</button>
        </form>
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
                                <?= $u['blocked'] ? 'Заблокирован' : 'Активен' ?>
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
                                    <a href="/admin/editUser?id=<?= $u['id'] ?>" class="admin-btn btn-edit btn-small">✏️ Редактировать</a>
                                </form>

                                <form method="POST" action="/admin/deleteUser"  class="table-form" onsubmit="return confirm('Удалить пользователя?');">
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

    <?php if ($pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="?page=<?= $i ?>&q=<?= urlencode($q) ?>&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>"
            class="<?= $i == $currentPage ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

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

<script>
document.querySelectorAll('.table-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const result = await response.json();

        if (result.success) {
            const row = form.closest('tr');

            row.querySelector('td:nth-child(4)').textContent = result.role;

            const statusCell = row.querySelector('td:nth-child(5) .status-badge');
            if (result.blocked === "1" || result.blocked === 1) {
                statusCell.textContent = "Заблокирован";
                statusCell.classList.remove("status-active");
                statusCell.classList.add("status-blocked");
            } else {
                statusCell.textContent = "Активен";
                statusCell.classList.remove("status-blocked");
                statusCell.classList.add("status-active");
            }

            row.style.backgroundColor = "#d4edda";
            setTimeout(() => row.style.backgroundColor = "", 800);
        } else {
            alert("Ошибка: " + result.error);
        }
    });
});
</script>
</body>
</html>