<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тест</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h2>📝 Тест по уроку</h2>
        <form method="POST" action="/test/submit">
            <input type="hidden" name="lesson_id" value="<?= htmlspecialchars($lessonId) ?>">

            <?php foreach ($test as $qid => $data): ?>
                <div class="question-block">
                    <p><strong><?= htmlspecialchars($data['question']) ?></strong></p>
                    <?php foreach ($data['options'] as $opt): ?>
                        <label>
                            <input type="radio" name="q<?= $qid ?>" value="<?= $opt['id'] ?>" required>
                            <?= htmlspecialchars($opt['text']) ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>
                <hr>
            <?php endforeach; ?>

            <button type="submit">Отправить ответы</button>
        </form>
    </div>
</body>
</html>
