<?php
$user = Auth::user();
$pages = $pages ?? 1;
$page = $page ?? 1;
$q = $q ?? '';
$courseId = $courseId ?? '';
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
<title>Администрирование — Уроки</title>
<style>
.blink { animation: blinkAnim 0.9s ease-in-out; }
@keyframes blinkAnim { 0% { background-color: #d4edda; } 100% { background-color: transparent; } }
.modal { display:none; position:fixed; z-index:50; left:0; top:0; right:0; bottom:0; background:rgba(0,0,0,0.4); align-items:center; justify-content:center; }
.modal .panel { background:#fff; padding:16px; border-radius:6px; width:520px; max-width:95%; }
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
    <h1 class="hero-title">Уроки</h1>

    <section class="admin-form">
        <h3 class="admin-form-title">Создать урок</h3>
        <form method="POST" action="/admin/createLesson" class="admin-form-grid">
            <div><input type="text" name="title" placeholder="Название урока" class="form-input" required></div>
            <div><textarea name="content" placeholder="Содержимое урока" class="form-input"></textarea></div>
            <div>
                <select name="course_id" class="form-input" required>
                    <option value="">— выбрать курс —</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><button type="submit" class="course-action">Добавить урок</button></div>
        </form>
    </section>

    <section>
        <h3 class="admin-form-title">Список уроков</h3>

        <form method="get" action="/admin/lessons" class="search-form" style="margin-bottom:12px;">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Поиск по названию урока">
            <select name="course_id" style="border-radius: 4px;">
                <option value="">Все курсы</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (string)$courseId === (string)$c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Поиск</button>
            <?php if ($q !== '' || $courseId !== ''): ?>
                <a href="/admin/lessons" style="margin-left:8px;">Сбросить</a>
            <?php endif; ?>
        </form>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название урока</th>
                    <th>Курс</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lessons as $l): ?>
                    <tr id="lesson-<?= $l['id'] ?>"
                        data-title="<?= htmlspecialchars($l['title'], ENT_QUOTES) ?>"
                        data-content="<?= htmlspecialchars($l['content'] ?? '', ENT_QUOTES) ?>"
                        data-course="<?= htmlspecialchars($l['course_id'] ?? '', ENT_QUOTES) ?>">
                        <td><?= $l['id'] ?></td>
                        <td class="lesson-title"><?= htmlspecialchars($l['title']) ?></td>
                        <td class="lesson-course"><?= htmlspecialchars($l['course_title'] ?? '-') ?></td>
                        <td>
                            <button class="admin-btn btn-edit btn-small btn-edit-lesson" data-id="<?= $l['id'] ?>">✏️ Ред.</button>
                            <form method="GET" action="/admin/deleteLesson" style="display:inline" onsubmit="return confirm('Удалить урок?');">
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
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
                <a href="?page=<?= $i ?>&q=<?= urlencode($q) ?>&course_id=<?= urlencode($courseId) ?>"
                   class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </section>

    <section style="margin-top: 30px;">
        <a href="/admin" class="course-action">← Вернуться в админку</a>
    </section>
</div>

<div id="lessonModal" class="modal">
  <div class="panel">
    <h3>Редактировать урок</h3>
    <form id="lessonEditForm">
        <input type="hidden" name="id" id="modalLessonId">
        <div><label>Название</label><input type="text" name="title" id="modalTitleInput" class="form-input" required></div>
        <div><label>Содержимое</label><textarea name="content" id="modalContentInput" class="form-input"></textarea></div>
        <div><label>Курс</label>
            <select name="course_id" id="modalCourseSelect" class="form-input" required>
                <option value="">— выбрать курс —</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="margin-top:10px; display:flex; gap:8px;">
            <button type="submit" class="course-action">Сохранить</button>
            <button type="button" id="modalCancel" class="admin-btn btn-small">Отмена</button>
        </div>
    </form>
  </div>
</div>

<script>
document.querySelectorAll('.btn-edit-lesson').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const row = document.getElementById('lesson-' + id);
        document.getElementById('modalLessonId').value = id;
        document.getElementById('modalTitleInput').value = row.dataset.title || '';
        document.getElementById('modalContentInput').value = row.dataset.content || '';
        document.getElementById('modalCourseSelect').value = row.dataset.course || '';
        document.getElementById('lessonModal').style.display = 'flex';
    });
});

document.getElementById('modalCancel').addEventListener('click', () => {
    document.getElementById('lessonModal').style.display = 'none';
});

document.getElementById('lessonEditForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.currentTarget;
    const data = new FormData(form);

    try {
        const resp = await fetch('/admin/updateLesson', {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await resp.json();

        if (json.success) {
            const id = data.get('id');
            const row = document.getElementById('lesson-' + id);

            row.querySelector('.lesson-title').textContent = data.get('title');
            row.dataset.title = data.get('title');
            row.dataset.content = data.get('content');
            row.dataset.course = data.get('course_id');

            if (json.course_title) row.querySelector('.lesson-course').textContent = json.course_title;

            row.classList.add('blink');
            row.addEventListener('animationend', () => row.classList.remove('blink'), { once: true });

            document.getElementById('lessonModal').style.display = 'none';
        } else {
            alert('Ошибка: ' + (json.error || 'Неизвестная ошибка'));
        }
    } catch (err) {
        console.error(err);
        alert('Произошла ошибка при сохранении');
    }
});
</script>
</body>
</html>
