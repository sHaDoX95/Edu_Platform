<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Тестирование</title>
</head>
<body>
    <nav>
        <p>
            <a href="/course/show?id=<?= htmlspecialchars($courseId) ?>">← Назад к курсу</a>
        </p>
    </nav>

    <div class="test-container">
        <div class="test-header">
            <h1 class="test-title">🧪 Тестирование</h1>
            <p class="test-subtitle">Проверьте свои знания по уроку</p>
        </div>

        <form method="POST" action="/test/submit">
            <input type="hidden" name="lesson_id" value="<?= htmlspecialchars($lessonId) ?>">

            <?php foreach ($test as $qid => $data): ?>
                <div class="question-block">
                    <h3 class="question-text"><?= htmlspecialchars($data['question']) ?></h3>
                    
                    <div class="options-list">
                        <?php foreach ($data['options'] as $opt): ?>
                            <label class="option-label">
                                <input type="radio" name="q<?= $qid ?>" value="<?= $opt['id'] ?>" required>
                                <span class="option-text"><?= htmlspecialchars($opt['text']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="test-submit-btn">📤 Отправить ответы</button>
        </form>
    </div>

    <script>
    document.querySelectorAll('.option-label input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const questionBlock = this.closest('.question-block');
            questionBlock.querySelectorAll('.option-label').forEach(label => {
                label.style.borderColor = '#e9ecef';
                label.style.background = 'white';
            });
            
            const selectedLabel = this.closest('.option-label');
            selectedLabel.style.borderColor = '#667eea';
            selectedLabel.style.background = '#f8f9ff';
        });
    });
    </script>
</body>
</html>