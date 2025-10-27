<?php
$user = Auth::user();
?>
<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Редактирование чата</title>
</head>

<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <a href="/admin/chats">← Назад к чатам</a> |
            <a href="/admin">🛠️ Админ-панель</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="container">
        <h1 class="hero-title">✏️ Редактирование чата</h1>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="flash-message flash-error"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <section class="admin-form">
            <h3 class="admin-form-title">Редактировать чат "<?= htmlspecialchars($chat['title']) ?>"</h3>
            <form method="POST" action="/admin/chats/update/<?= $chat['id'] ?>" class="admin-form-grid" id="editChatForm">
                <div>
                    <label class="form-label">Название чата:</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($chat['title']) ?>" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Преподаватель:</label>
                    <select name="teacher_id" class="form-input" required>
                        <option value="">— выбрать преподавателя —</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= $teacher['id'] ?>" 
                                    <?= $chat['teacher_id'] == $teacher['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($teacher['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Участники:</label>
                    <select name="participants[]" class="form-input" multiple size="8" required id="participantsSelect">
                        <?php foreach ($students as $student): ?>
                            <option value="<?= $student['id'] ?>"
                                    <?= in_array($student['id'], $currentParticipants) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($student['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Для выбора нескольких участников удерживайте Ctrl (Cmd на Mac)
                    </small>
                </div>
            </form>

            <div class="form-actions">
                <button type="submit" form="editChatForm" class="btn btn-primary">💾 Сохранить изменения</button>
                <a href="/admin/chats" class="btn btn-secondary">❌ Отмена</a>
                
                <!-- Кнопка удаления чата -->
                <form method="POST" action="/admin/chats/delete" style="display: inline;" 
                      onsubmit="return confirm('Внимание! Будут удалены все сообщения этого чата. Вы уверены, что хотите удалить этот чат?');">
                    <input type="hidden" name="chat_id" value="<?= $chat['id'] ?>">
                    <button type="submit" class="btn btn-danger">🗑️ Удалить чат</button>
                </form>
            </div>
        </section>

        <section style="margin-top: 30px;">
            <div class="admin-form">
                <h3 class="admin-form-title">Информация о чате</h3>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                    <p><strong>ID чата:</strong> <?= $chat['id'] ?></p>
                    <p><strong>Создан:</strong> <?= date('d.m.Y H:i', strtotime($chat['created_at'])) ?></p>
                    <p><strong>Создал:</strong> 
                        <?php 
                        $creator = $db->query("SELECT name FROM users WHERE id = {$chat['created_by']}")->fetchColumn();
                        echo htmlspecialchars($creator ?: 'Неизвестно');
                        ?>
                    </p>
                    <p><strong>Количество участников:</strong> <?= count($currentParticipants) ?></p>
                </div>
            </div>
        </section>
    </div>

    <script>
        // Валидация формы редактирования чата
        document.getElementById('editChatForm').addEventListener('submit', function(e) {
            const participantsSelect = document.getElementById('participantsSelect');
            const selectedParticipants = Array.from(participantsSelect.selectedOptions).map(option => option.value);
            
            if (selectedParticipants.length === 0) {
                e.preventDefault();
                alert('Пожалуйста, выберите хотя бы одного участника');
                participantsSelect.focus();
                return false;
            }
        });

        // Обработка flash сообщений
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(message => {
                setTimeout(() => {
                    message.style.transition = 'all 0.3s ease';
                    message.style.opacity = '0';
                    message.style.height = message.offsetHeight + 'px';

                    setTimeout(() => {
                        message.style.height = '0';
                        message.style.padding = '0';
                        message.style.margin = '0';
                    }, 300);

                    setTimeout(() => {
                        if (message.parentNode) {
                            message.remove();
                        }
                    }, 600);
                }, 5000);
            });
        });
    </script>
</body>
</html>