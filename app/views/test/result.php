<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Результаты теста</title>
</head>
<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <?php if ($user['role'] === 'teacher'): ?>
                <a href="/teacher">👨‍🏫 Личный кабинет</a> |
            <?php elseif ($user['role'] === 'admin'): ?>
                <a href="/admin">🛠️ Админ-панель</a> |
            <?php else: ?>
                <a href="/user">👤 Личный кабинет</a> |
            <?php endif; ?>
            <a href="/support">🆘 Поддержка</a> | 
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="result-container">
        <?php
        $percentage = $total > 0 ? round(($correct / $total) * 100) : 0;
        $icon = $percentage >= 80 ? '🎉' : ($percentage >= 60 ? '👍' : '💪');
        $message = $percentage >= 80 ? 'Отличный результат!' : 
                  ($percentage >= 60 ? 'Хороший результат!' : 'Попробуйте еще раз!');
        ?>
        
        <div class="result-icon"><?= $icon ?></div>
        <h1 class="result-title">Результаты теста</h1>
        
        <div class="result-score"><?= $correct ?>/<?= $total ?></div>
        <div class="result-score"><?= $percentage ?>%</div>
        
        <p class="result-text"><?= $message ?></p>
        
        <div class="result-actions">
            <a href="/course" class="result-btn btn-primary">📚 К курсам</a>
            <a href="/course/show?id=<?= $lesson['course_id'] ?? '' ?>" class="result-btn btn-secondary">↻ Повторить урок</a>
            <?php if ($percentage < 80): ?>
                <a href="/test/show?lesson_id=<?= $lessonId ?>" class="result-btn btn-secondary">🔄 Пройти еще раз</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const scoreElements = document.querySelectorAll('.result-score');
        scoreElements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'scale(0.5)';
            setTimeout(() => {
                el.style.transition = 'all 0.8s ease-out';
                el.style.opacity = '1';
                el.style.transform = 'scale(1)';
            }, 300);
        });
    });
    </script>
</body>
</html>