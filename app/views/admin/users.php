<?php
$user = Auth::user();

$pages = $pages ?? 1;
$currentPage = $currentPage ?? 1;
$q = $_GET['q'] ?? '';
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
<title>Администрирование — Пользователи</title>
<style>
.user-role-select, .user-status-select {
    transition: all 0.3s ease;
}

.user-role-select:disabled, .user-status-select:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.save-indicator {
    margin-left: 10px;
    font-weight: bold;
    font-size: 0.9em;
}

.save-success {
    animation: successAnim 2s ease-in-out;
}

@keyframes successAnim {
    0% { background-color: #d4edda; }
    30% { background-color: #e8f5e8; }
    100% { background-color: transparent; }
}
</style>
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
            <input type="text" name="name" placeholder="Имя" class="form-input" required>
            <input type="email" name="email" placeholder="Email" class="form-input" required>
            <input type="password" name="password" placeholder="Пароль" class="form-input" required>
            <select name="role" class="form-input" required>
                <option value="student">Студент</option>
                <option value="teacher">Преподаватель</option>
                <option value="admin">Админ</option>
            </select>
            <button type="submit" class="course-action">Добавить пользователя</button>
        </form>
    </section>

    <section>
        <h3 class="admin-form-title">Список пользователей</h3>
        <form method="get" action="/admin/users" class="search-form" style="margin-bottom:12px;">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Поиск по имени или email">
            <select name="role" style="border-radius: 4px;">
                <option value="">Все роли</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Администраторы</option>
                <option value="teacher" <?= $role === 'teacher' ? 'selected' : '' ?>>Преподаватели</option>
                <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Студенты</option>
            </select>
            <select name="status" style="border-radius: 4px;">
                <option value="">Все</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Активные</option>
                <option value="blocked" <?= $status === 'blocked' ? 'selected' : '' ?>>Заблокированные</option>
            </select>
            <button type="submit">Поиск</button>
            <?php if (!empty($q) || !empty($role) || !empty($status)): ?>
                <a href="/admin/users" style="margin-left:8px;">Сбросить</a>
            <?php endif; ?>
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
                    <tr id="user-<?= $u['id'] ?>">
                        <td><?= htmlspecialchars($u['id']) ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <select name="role" class="form-input user-role-select" data-user-id="<?= $u['id'] ?>" data-previous-value="<?= $u['role'] ?>">
                                <option value="student" <?= $u['role']==='student'?'selected':'' ?>>Студент</option>
                                <option value="teacher" <?= $u['role']==='teacher'?'selected':'' ?>>Преподаватель</option>
                                <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>Админ</option>
                            </select>
                        </td>
                        <td>
                            <select name="blocked" class="form-input user-status-select" data-user-id="<?= $u['id'] ?>">
                                <option value="0" <?= $u['blocked'] == 0 ? 'selected' : '' ?>>Активен</option>
                                <option value="1" <?= $u['blocked'] == 1 ? 'selected' : '' ?>>Заблокирован</option>
                            </select>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <a href="/admin/editUser?id=<?= $u['id'] ?>" class="admin-btn btn-edit btn-small">✏️ Редактировать</a>
                                <form method="POST" action="/admin/deleteUser" style="display:inline" onsubmit="return confirm('Удалить пользователя?');">
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
</div>

<script>
async function updateUserField(selectElement, fieldName) {
    const userId = selectElement.dataset.userId;
    const value = selectElement.value;
    const indicator = document.getElementById(`${fieldName}-indicator-${userId}`);
    const row = document.getElementById(`user-${userId}`);

    if (!indicator || !row) return;

    const originalBorder = selectElement.style.border;
    const originalBackground = selectElement.style.backgroundColor;

    selectElement.style.border = '2px solid #ffc107';
    selectElement.style.backgroundColor = '#fffbf0';
    selectElement.disabled = true;

    try {
        const formData = new FormData();
        formData.append('id', userId);
        formData.append(fieldName, value);

        const response = await fetch('/admin/updateUser', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const result = await response.json();

        if (result.success) {
            selectElement.style.border = '2px solid #28a745';
            selectElement.style.backgroundColor = '#f0fff4';
            row.classList.add('save-success');

            selectElement.dataset.previousValue = value;

            setTimeout(() => {
                this.style.border = originalBorder;
                this.style.backgroundColor = originalBackground;
                this.disabled = false;
                row.classList.remove('save-success');
                this.setAttribute('data-previous-value', this.value); // обновляем предыдущий
            }, 1500);
        } else {
            throw new Error(result.error || 'Ошибка при обновлении');
        }
    } catch (err) {
        console.error(err);
        selectElement.style.border = '2px solid #dc3545';
        selectElement.style.backgroundColor = '#fff5f5';
        selectElement.value = selectElement.dataset.previousValue;

        setTimeout(() => {
            selectElement.style.border = originalBorder;
            selectElement.style.backgroundColor = originalBackground;
            selectElement.disabled = false;
        }, 2000);
    }
}

document.querySelectorAll('.user-role-select, .user-status-select').forEach(select => {
    select.setAttribute('data-previous-value', select.value);

    select.addEventListener('change', async function() {
        const userId = this.getAttribute('data-user-id');
        const fieldName = this.classList.contains('user-role-select') ? 'role' : 'blocked';
        const value = this.value;
        const indicator = document.getElementById(`${fieldName}-indicator-${userId}`);
        const row = document.getElementById(`user-${userId}`);

        const originalBorder = this.style.border;
        const originalBackground = this.style.backgroundColor;

        this.disabled = true;
        this.style.border = '2px solid #ffc107';
        this.style.backgroundColor = '#fffbf0';

        try {
            const formData = new FormData();
            formData.append('id', userId);
            formData.append(fieldName, fieldName === 'blocked' ? parseInt(value) : value);

            const response = await fetch('/admin/updateUser', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                this.style.border = '2px solid #28a745';
                this.style.backgroundColor = '#f0fff4';
                row.classList.add('save-success');

                setTimeout(() => {
                    this.style.border = originalBorder;
                    this.style.backgroundColor = originalBackground;
                    this.disabled = false;
                    row.classList.remove('save-success');
                    this.setAttribute('data-previous-value', this.value); // обновляем предыдущий
                }, 1500);

            } else {
                throw new Error(result.error || 'Ошибка обновления');
            }

        } catch (err) {
            console.error(err);
            this.style.border = '2px solid #dc3545';
            this.style.backgroundColor = '#fff5f5';
            this.value = this.getAttribute('data-previous-value'); // откат
            setTimeout(() => {
                this.style.border = originalBorder;
                this.style.backgroundColor = originalBackground;
                this.disabled = false;
                indicator.textContent = '';
            }, 3000);
            alert('Не удалось сохранить: ' + err.message);
        }
    });
});
</script>
</body>
</html>