<?php
$user = Auth::user();
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
<title>Администрирование — Курсы</title>
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
    <h1 class="hero-title">Курсы</h1>

    <section class="admin-form">
        <h3 class="admin-form-title">Создать курс</h3>
        <form method="POST" action="/admin/createCourse" class="admin-form-grid">
            <div>
                <input type="text" name="title" placeholder="Название курса" class="form-input" required>
            </div>
            <div>
                <textarea name="description" placeholder="Описание курса" class="form-input" required></textarea>
            </div>
            <div>
                <select name="teacher_id" class="form-input">
                    <option value="">— выбрать преподавателя —</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="course-action">Добавить курс</button>
            </div>
        </form>
    </section>

    <section>
        <h3 class="admin-form-title">Список курсов</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Преподаватель</th>
                    <th>Уроки</th>
                    <th>Студенты</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['title']) ?></td>
                        <td>
                            <span class="teacher-name"></span>
                            <form method="POST" action="/admin/updateCourseTeacher" class="course-teacher-form">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <select name="teacher_id" class="form-input">
                                    <option value="">— выбрать преподавателя —</option>
                                    <?php foreach ($teachers as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= $c['teacher_id']==$t['id']?'selected':'' ?>>
                                            <?= htmlspecialchars($t['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="admin-btn btn-save btn-small">💾</button>
                            </form>
                        </td>
                        <td><?= $c['lessons_count'] ?></td>
                        <td><?= $c['students_count'] ?></td>
                        <td>
                            <form method="GET" action="/admin/deleteCourse" onsubmit="return confirm('Удалить курс?');">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="admin-btn btn-delete btn-small">❌</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i == $currentPage ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </section>

    <section style="margin-top: 30px;">
        <a href="/admin" class="course-action">← Вернуться в админку</a>
    </section>
</div>

<script>
document.querySelectorAll('.course-teacher-form').forEach(form => {
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

            const select = form.querySelector('select[name="teacher_id"]');
            const teacherName = select.options[select.selectedIndex].text;
            
            row.classList.add('blink');
            row.addEventListener('animationend', () => row.classList.remove('blink'), { once: true });
        } else {
            alert("Ошибка: " + result.error);
        }
    });
});
</script>
</body>
</html>