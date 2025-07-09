<?php $user = Auth::user(); ?>

<nav>
    <p>
        Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
        <a href="/home">🏠 Личный кабинет</a> |
        <a href="/auth/logout">🚪 Выйти</a>
    </p>
</nav>

<h2><?= htmlspecialchars($course['title']) ?></h2>
<p><?= htmlspecialchars($course['description']) ?></p>

<h3>Уроки:</h3>
<ol>
<?php foreach ($course['lessons'] as $lesson): ?>
    <li>
        <strong><?= htmlspecialchars($lesson['title']) ?></strong><br>
        <p><?= nl2br(htmlspecialchars($lesson['content'])) ?></p>
    </li>
<?php endforeach; ?>
</ol>

<a href="/course">← Назад к списку курсов</a>