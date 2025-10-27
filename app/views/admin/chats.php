<?php
$user = Auth::user();

$pages = $pages ?? 1;
$currentPage = $currentPage ?? 1;
$q = $_GET['q'] ?? '';
?>
<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Администрирование — Чаты</title>
    <style>
        .chat-teacher-select {
            transition: all 0.3s ease;
        }

        .chat-teacher-select:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .save-success {
            animation: successAnim 2s ease-in-out;
        }

        @keyframes successAnim {
            0% { background-color: #d4edda; }
            30% { background-color: #e8f5e8; }
            100% { background-color: transparent; }
        }

        .blink {
            animation: blinkAnim 0.6s ease-in-out;
            background-color: #fffbf0 !important;
        }

        .save-error {
            animation: errorAnim 3s ease-in-out;
        }

        @keyframes blinkAnim {
            0% { background-color: #fffbf0; }
            50% { background-color: #fff8e1; }
            100% { background-color: #fffbf0; }
        }

        @keyframes errorAnim {
            0% { background-color: #f8d7da; }
            30% { background-color: #ffe6e6; }
            100% { background-color: transparent; }
        }
    </style>
</head>

<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <a href="/admin">🛠️ Админ-панель</a> |
            <a href="/admin/users">👥 Пользователи</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="container">
        <h1 class="hero-title">💬 Управление чатами</h1>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="flash-message flash-error"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="flash-message" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #28a745;">
                <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>

        <section class="admin-form">
            <h3 class="admin-form-title">Создать чат</h3>
            <form method="POST" action="/admin/chats/store" class="admin-form-grid" id="createChatForm">
                <div>
                    <input type="text" name="title" placeholder="Название чата" class="form-input" required
                        value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                </div>
                <div>
                    <select name="teacher_id" class="form-input" required>
                        <option value="">— выбрать преподавателя —</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= $teacher['id'] ?>"
                                <?= ($_POST['teacher_id'] ?? '') == $teacher['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($teacher['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <select name="participants[]" class="form-input" multiple size="6" required id="participantsSelect">
                        <?php foreach ($students as $student): ?>
                            <option value="<?= $student['id'] ?>"
                                <?= isset($_POST['participants']) && in_array($student['id'], $_POST['participants']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($student['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Для выбора нескольких участников удерживайте Ctrl (Cmd на Mac)
                    </small>
                </div>
                <div>
                    <button type="submit" class="course-action">➕ Создать чат</button>
                </div>
            </form>
        </section>

        <section>
            <h3 class="admin-form-title">Список чатов</h3>

            <form method="get" action="/admin/chats" class="search-form" style="margin-bottom:12px;">
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Поиск по названию чата">
                <button type="submit">Поиск</button>
                <?php if (!empty($q)): ?>
                    <a href="/admin/chats" style="margin-left:8px;">Сбросить</a>
                <?php endif; ?>
            </form>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Преподаватель</th>
                        <th>Участники</th>
                        <th>Создан</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($chats)): ?>
                        <?php foreach ($chats as $chat): ?>
                            <tr id="chat-<?= $chat['id'] ?>">
                                <td><?= $chat['id'] ?></td>
                                <td><?= htmlspecialchars($chat['title']) ?></td>
                                <td>
                                    <form method="POST" action="/admin/chats/updateTeacher" class="table-form">
                                        <input type="hidden" name="chat_id" value="<?= $chat['id'] ?>">
                                        <select name="teacher_id" class="form-input chat-teacher-select" data-chat-id="<?= $chat['id'] ?>" required>
                                            <?php foreach ($teachers as $teacher): ?>
                                                <option value="<?= $teacher['id'] ?>" <?= $chat['teacher_id'] == $teacher['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($teacher['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <span class="status-badge-course" style="background: #e6f7ff; color: #17a2b8;">
                                        участников: <?= $chat['participants_count'] ?? 0 ?>
                                    </span>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($chat['created_at'])) ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="/chat/view/<?= $chat['id'] ?>" class="admin-btn btn-view btn-small">👁️ Просмотр</a>
                                        <a href="/admin/chats/edit/<?= $chat['id'] ?>" class="admin-btn btn-edit btn-small">✏️ Редактировать</a>
                                        <form method="POST" action="/admin/chats/delete" style="display: inline;"
                                            onsubmit="return confirm('Внимание! Будут удалены все сообщения этого чата. Вы уверены, что хотите удалить этот чат?');">
                                            <input type="hidden" name="chat_id" value="<?= $chat['id'] ?>">
                                            <button type="submit" class="admin-btn btn-delete btn-small">🗑️ Удалить</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #6c757d;">
                                <div style="font-size: 3em; margin-bottom: 15px; opacity: 0.5;">💬</div>
                                <h3>Чатов не найдено</h3>
                                <p>Попробуйте изменить параметры поиска или создайте первый чат</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="?page=<?= $i ?>&q=<?= urlencode($q) ?>"
                            class="<?= $i == $currentPage ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </section>

        <section style="margin-top: 30px;">
            <a href="/admin" class="course-action">← Вернуться в админку</a>
        </section>
    </div>

    <script>
        // Валидация формы создания чата
        document.getElementById('createChatForm').addEventListener('submit', function(e) {
            const participantsSelect = document.getElementById('participantsSelect');
            const selectedParticipants = Array.from(participantsSelect.selectedOptions).map(option => option.value);

            if (selectedParticipants.length === 0) {
                e.preventDefault();
                alert('Пожалуйста, выберите хотя бы одного участника');
                participantsSelect.focus();
                return false;
            }
        });

        // Обработка изменения преподавателя
        document.querySelectorAll('.chat-teacher-select').forEach(select => {
            select.setAttribute('data-previous-value', select.value);

            select.addEventListener('change', async function() {
                const chatId = this.getAttribute('data-chat-id');
                const value = this.value;
                const row = document.getElementById(`chat-${chatId}`);

                // Если значение пустое, не отправляем запрос
                if (!value) {
                    this.value = this.getAttribute('data-previous-value');
                    alert('Пожалуйста, выберите преподавателя');
                    return;
                }

                const originalBorder = this.style.border;
                const originalBackground = this.style.backgroundColor;

                this.disabled = true;
                this.style.border = '2px solid #ffc107';
                this.style.backgroundColor = '#fffbf0';

                try {
                    const formData = new FormData();
                    formData.append('chat_id', chatId);
                    formData.append('teacher_id', value);

                    const response = await fetch('/admin/chats/updateTeacher', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.style.border = '2px solid #28a745';
                        this.style.backgroundColor = '#f0fff4';
                        row.classList.add('save-success');

                        setTimeout(() => {
                            this.style.border = originalBorder;
                            this.style.backgroundColor = originalBackground;
                            this.disabled = false;
                            row.classList.remove('save-success');
                            this.setAttribute('data-previous-value', this.value);
                        }, 1500);

                    } else {
                        throw new Error(result.error || 'Ошибка обновления');
                    }

                } catch (err) {
                    console.error(err);
                    this.style.border = '2px solid #dc3545';
                    this.style.backgroundColor = '#fff5f5';
                    this.value = this.getAttribute('data-previous-value');

                    setTimeout(() => {
                        this.style.border = originalBorder;
                        this.style.backgroundColor = originalBackground;
                        this.disabled = false;
                    }, 3000);

                    alert('Не удалось сохранить: ' + err.message);
                }
            });
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