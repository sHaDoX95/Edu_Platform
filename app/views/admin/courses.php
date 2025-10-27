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
            <a href="/admin/users">👥 Пользователи</a> |
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

            <form method="get" action="/admin/courses" class="search-form" style="margin-bottom:12px;">
                <input type="text" name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Поиск по названию курса">
                <select name="teacher_id" style="border-radius: 4px;">
                    <option value="">Все преподаватели</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= (string)($teacherId ?? '') === (string)$t['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Поиск</button>
                <?php if (!empty($q) || !empty($teacherId)): ?>
                    <a href="/admin/courses" style="margin-left:8px;">Сбросить</a>
                <?php endif; ?>
            </form>

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
                        <tr id="course-<?= $c['id'] ?>">
                            <td><?= $c['id'] ?></td>
                            <td><?= htmlspecialchars($c['title']) ?></td>
                            <td>
                                <form method="POST" action="/admin/updateCourseTeacher" class="course-teacher-form">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <select name="teacher_id" class="form-input course-teacher-select">
                                        <option value="">— выбрать преподавателя —</option>
                                        <?php foreach ($teachers as $t): ?>
                                            <option value="<?= $t['id'] ?>" <?= $c['teacher_id'] == $t['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($t['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td><?= $c['lessons_count'] ?></td>
                            <td><?= $c['students_count'] ?></td>
                            <td>
                                <form method="GET" action="/admin/deleteCourse" onsubmit="return confirm('Удалить курс?');">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="admin-btn btn-delete btn-small">❌ Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="?page=<?= $i ?>&q=<?= urlencode($q ?? '') ?>&teacher_id=<?= urlencode($teacherId ?? '') ?>"
                            class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </section>

        <section style="margin-top: 30px;">
            <a href="/admin" class="course-action">← Вернуться в админку</a>
        </section>
    </div>

    <script>
        document.querySelectorAll('.course-teacher-select').forEach(select => {
            select.addEventListener('change', function() {
                const form = this.closest('form');
                const row = form.closest('tr');
                submitForm(form, this, row);
            });
        });

        async function submitForm(form, selectElement, row) {
            const formData = new FormData(form);
            const courseId = formData.get('id');

            row.classList.add('blink');
            selectElement.disabled = true;

            selectElement.style.border = '2px solid #667eea';
            selectElement.style.backgroundColor = '#f8f9ff';

            try {
                const response = await fetch('/admin/updateCourseTeacher', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    row.classList.remove('blink');
                    row.classList.add('save-success');
                    selectElement.style.border = '2px solid #28a745';
                    selectElement.style.backgroundColor = '#f0fff4';

                    setTimeout(() => {
                        row.classList.remove('save-success');
                        selectElement.style.border = '';
                        selectElement.style.backgroundColor = '';
                        selectElement.disabled = false;
                    }, 2000);

                } else {
                    row.classList.remove('blink');
                    row.classList.add('save-error');
                    selectElement.style.border = '2px solid #dc3545';
                    selectElement.style.backgroundColor = '#fff5f5';

                    setTimeout(() => {
                        row.classList.remove('save-error');
                        selectElement.style.border = '';
                        selectElement.style.backgroundColor = '';
                        selectElement.disabled = false;
                    }, 3000);

                    alert('Ошибка: ' + result.error);
                }
            } catch (error) {
                console.error('Ошибка:', error);

                row.classList.remove('blink');
                row.classList.add('save-error');
                selectElement.style.border = '2px solid #dc3545';
                selectElement.style.backgroundColor = '#fff5f5';

                setTimeout(() => {
                    row.classList.remove('save-error');
                    selectElement.style.border = '';
                    selectElement.style.backgroundColor = '';
                    selectElement.disabled = false;
                }, 3000);

                alert('Произошла ошибка при сохранении');
            }
        }

        const style = document.createElement('style');
        style.textContent = `
    .blink {
        animation: blinkAnim 0.6s ease-in-out;
        background-color: #fffbf0 !important;
    }
    
    .save-success {
        animation: successAnim 2s ease-in-out;
    }
    
    .save-error {
        animation: errorAnim 3s ease-in-out;
    }
    
    @keyframes blinkAnim {
        0% { background-color: #fffbf0; }
        50% { background-color: #fff8e1; }
        100% { background-color: #fffbf0; }
    }
    
    @keyframes successAnim {
        0% { background-color: #d4edda; }
        30% { background-color: #e8f5e8; }
        100% { background-color: transparent; }
    }
    
    @keyframes errorAnim {
        0% { background-color: #f8d7da; }
        30% { background-color: #ffe6e6; }
        100% { background-color: transparent; }
    }
    
    .course-teacher-select {
        transition: all 0.3s ease;
    }
    
    .course-teacher-select:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
`;
        document.head.appendChild(style);
    </script>
</body>

</html>