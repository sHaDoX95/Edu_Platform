<?php
$user = Auth::user();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Редактировать курс</title>
</head>
<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <a href="/course">📚 Все курсы</a> |
            <a href="/teacher">👨‍🏫 Личный кабинет</a> |
            <a href="/support">🆘 Поддержка</a> | 
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="form-container">
        <h1 class="form-title">✏️ Редактировать курс</h1>
        <p class="form-subtitle">Обновите информацию о курсе</p>

        <form action="/teacher/update" method="post">
            <input type="hidden" name="id" value="<?= $course['id'] ?>">
            
            <div class="form-group">
                <label for="title" class="form-label">📖 Название курса</label>
                <input type="text" id="title" name="title" class="form-input" 
                       value="<?= htmlspecialchars($course['title']) ?>" 
                       placeholder="Введите название курса" required
                       oninput="updateCharCounter(this, 'title-counter', 100)">
                <div id="title-counter" class="char-counter"><?= mb_strlen($course['title']) ?>/100</div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">📝 Описание курса</label>
                <textarea id="description" name="description" class="form-input form-textarea" 
                          placeholder="Опишите содержание курса..." required
                          oninput="updateCharCounter(this, 'desc-counter', 500)"><?= htmlspecialchars($course['description']) ?></textarea>
                <div id="desc-counter" class="char-counter"><?= mb_strlen($course['description']) ?>/500</div>
            </div>

            <div class="form-actions">
                <button type="submit" class="form-btn">💾 Сохранить изменения</button>
                <a href="/teacher" class="form-btn secondary-btn">↩️ Назад в кабинет</a>
            </div>
        </form>
    </div>

    <script>
    function updateCharCounter(input, counterId, maxLength) {
        const counter = document.getElementById(counterId);
        const length = input.value.length;
        counter.textContent = `${length}/${maxLength}`;
        
        if (length > maxLength * 0.9) {
            counter.className = 'char-counter danger';
        } else if (length > maxLength * 0.7) {
            counter.className = 'char-counter warning';
        } else {
            counter.className = 'char-counter';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const titleInput = document.getElementById('title');
        const descInput = document.getElementById('description');
        
        if (titleInput) updateCharCounter(titleInput, 'title-counter', 100);
        if (descInput) updateCharCounter(descInput, 'desc-counter', 500);
    });
    </script>
</body>
</html>