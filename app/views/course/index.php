<?php $user = Auth::user(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Курсы</title>
</head>
<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <?php if ($user['role'] === 'teacher'): ?>
                <a href="/teacher">👨‍🏫 Личный кабинет</a> |
            <?php else: ?>
                <a href="/user">👤 Личный кабинет</a> |
            <?php endif; ?>
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>
    <div class="container">
        <h2>Доступные курсы</h2>

        <input type="text" id="search" placeholder="🔍 Найти курс..." style="width: 100%; padding: 8px; margin-bottom: 15px;">

        <ul class="course-list" id="course-list">
            <?php foreach ($courses as $course): ?>
                <li>
                    <a href="/course/show?id=<?= $course['id'] ?>">
                        <?= htmlspecialchars($course['title']) ?>
                    </a>
                    <small><?= htmlspecialchars($course['description']) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
    document.getElementById('search').addEventListener('input', function () {
        const query = this.value;

        fetch('/course/search?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                const list = document.getElementById('course-list');
                list.innerHTML = '';
                if (data.length === 0) {
                    list.innerHTML = '<li>❌ Ничего не найдено</li>';
                } else {
                    data.forEach(course => {
                        const li = document.createElement('li');
                        li.innerHTML = `<a href="/course/show?id=${course.id}">${course.title}</a>
                                        <small>${course.description}</small>`;
                        list.appendChild(li);
                    });
                }
            });
    });
    </script>
</body>
</html>