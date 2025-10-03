<?php
$user = Auth::user();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Редактировать урок</title>
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
        <h1 class="form-title">✏️ Редактировать урок</h1>
        <p class="form-subtitle">Обновите информацию об уроке</p>

        <form method="POST" action="/lesson/update">
            <input type="hidden" name="id" value="<?= htmlspecialchars($lesson['id']) ?>">

            <div class="form-group">
                <label for="title" class="form-label">📝 Название урока</label>
                <input type="text" id="title" name="title" class="form-input" 
                       value="<?= htmlspecialchars($lesson['title']) ?>" 
                       placeholder="Введите название урока" required
                       oninput="updateCharCounter(this, 'title-counter', 100)">
                <div id="title-counter" class="char-counter"><?= mb_strlen($lesson['title']) ?>/100</div>
            </div>

            <div class="form-group">
                <label for="content" class="form-label">📄 Содержимое урока</label>
                <div class="preview-toggle" onclick="togglePreview()">👁️ Предпросмотр</div>
                
                <textarea id="content" name="content" class="form-input form-textarea" 
                          placeholder="Напишите содержимое урока..." required
                          oninput="updateCharCounter(this, 'content-counter', 2000)"><?= htmlspecialchars($lesson['content']) ?></textarea>
                <div id="content-counter" class="char-counter"><?= mb_strlen($lesson['content']) ?>/2000</div>
                
                <div id="preview" class="preview-content"></div>
            </div>

            <div class="form-actions">
                <button type="submit" class="form-btn">💾 Обновить урок</button>
                <a href="/course/show?id=<?= htmlspecialchars($lesson['course_id']) ?>" class="form-btn secondary-btn">↩️ Назад к курсу</a>
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
        
        preview.innerHTML = content ? content.replace(/\n/g, '<br>') : '<em>Введите текст для предпросмотра</em>';
        preview.classList.toggle('active');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const content = `<?= addslashes($lesson['content']) ?>`;
        document.getElementById('preview').innerHTML = content ? content.replace(/\n/g, '<br>') : '';
    });
    </script>
</body>
</html>