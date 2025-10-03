<?php
$user = Auth::user();
$courseId = $_GET['course_id'] ?? '';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Добавить урок</title>
</head>
<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <a href="/teacher">👨‍🏫 Личный кабинет</a> |
            <a href="/support">🆘 Поддержка</a> | 
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="form-container">
        <h1 class="form-title">➕ Добавить урок</h1>
        <p class="form-subtitle">Создайте новый урок для вашего курса</p>

        <form method="POST" action="/lesson/store">
            <input type="hidden" name="course_id" value="<?= htmlspecialchars($courseId) ?>">

            <div class="form-group">
                <label for="title" class="form-label">📝 Название урока</label>
                <input type="text" id="title" name="title" class="form-input" 
                       placeholder="Введите название урока" required
                       oninput="updateCharCounter(this, 'title-counter', 100)">
                <div id="title-counter" class="char-counter">0/100</div>
            </div>

            <div class="form-group">
                <label for="content" class="form-label">📄 Содержимое урока</label>
                <div class="preview-toggle" onclick="togglePreview()">👁️ Предпросмотр</div>
                
                <textarea id="content" name="content" class="form-input form-textarea" 
                          placeholder="Напишите содержимое урока..." required
                          oninput="updateCharCounter(this, 'content-counter', 2000)"></textarea>
                <div id="content-counter" class="char-counter">0/2000</div>
                
                <div id="preview" class="preview-content"></div>
            </div>

            <div class="form-actions">
                <button type="submit" class="form-btn">💾 Сохранить урок</button>
                <a href="/course/show?id=<?= htmlspecialchars($courseId) ?>" class="form-btn secondary-btn">↩️ Назад</a>
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

    function togglePreview() {
        const preview = document.getElementById('preview');
        const content = document.getElementById('content').value;
        
        preview.innerHTML = content ? nl2br(htmlspecialchars(content)) : '<em>Введите текст для предпросмотра</em>';
        preview.classList.toggle('active');
    }

    function nl2br(str) {
        return str.replace(/\n/g, '<br>');
    }

    function htmlspecialchars(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const titleInput = document.getElementById('title');
        const contentInput = document.getElementById('content');
        
        if (titleInput) updateCharCounter(titleInput, 'title-counter', 100);
        if (contentInput) updateCharCounter(contentInput, 'content-counter', 2000);
    });
    </script>
</body>
</html>