<?php $user = Auth::user(); ?>

<h2>Личный кабинет</h2>

<p>Добро пожаловать, <strong><?= htmlspecialchars($user['name']) ?></strong>!</p>

<nav>
    <a href="/course">📚 Курсы</a> |
    <a href="/auth/logout">🚪 Выйти</a>
</nav>

<hr>

<section>
    <h3>Ваш прогресс</h3>
    <p>Здесь в будущем будет отображаться прогресс прохождения курсов и тестов.</p>
    <p>Например: "Вы завершили 2 из 5 уроков курса по HTML (40%)"</p>
</section>

<section>
    <h3>Последние действия</h3>
    <p>Сейчас эта часть в разработке.</p>
</section>
