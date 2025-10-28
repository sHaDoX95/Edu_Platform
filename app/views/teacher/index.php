<?php
require_once __DIR__ . '/../../core/helpers.php';

$user = Auth::user();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <title>Личный кабинет преподавателя</title>
</head>

<style>
    .chat-button-container {
        display: flex;
        justify-content: center;
    }

    .chat-button {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 15px 25px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        border: none;
        cursor: pointer;
        min-width: 180px;
        justify-content: center;
    }

    .chat-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: white;
        text-decoration: none;
    }

    .chat-button:active {
        transform: translateY(0);
    }

    .chat-icon {
        font-size: 18px;
    }

    .chat-text {
        font-family: inherit;
    }
</style>

<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <a href="/support">🆘 Поддержка</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="container">
        <div class="teacher-header">
            <div class="teacher-avatar">👨‍🏫</div>
            <h1 class="teacher-title">Кабинет преподавателя</h1>
            <p class="teacher-subtitle">Добро пожаловать, <strong><?= htmlspecialchars($user['name']) ?></strong>!</p>
        </div>

        <div class="chat-button-container" style="margin: 20px 0;">
            <a href="/chat" class="chat-button">
                <span class="chat-icon">💬</span>
                <span class="chat-text">Мои чаты</span>
            </a>
        </div>

        <div class="teacher-actions-index">
            <a href="/teacher/create" class="teacher-action-btn">
                ➕ Создать новый курс
            </a>
            <a href="/course" class="teacher-action-btn" style="background: #6c757d;">
                📚 Все курсы платформы
            </a>
        </div>

        <div class="courses-section">
            <h2 class="section-title">📘 Мои курсы</h2>

            <?php if (count($courses) === 0): ?>
                <div class="empty-courses">
                    <div class="empty-courses-icon">📚</div>
                    <h3>Курсы не найдены</h3>
                    <p>У вас пока нет созданных курсов</p>
                    <a href="/teacher/create" class="teacher-action-btn">Создать первый курс</a>
                </div>
            <?php else: ?>
                <div class="teacher-courses-grid">
                    <?php foreach ($courses as $course): ?>
                        <div class="teacher-course-card">
                            <h3 class="course-title-teacher"><?= htmlspecialchars($course['title']) ?></h3>
                            <p class="course-description-teacher"><?= htmlspecialchars($course['description']) ?></p>

                            <div class="course-stats">
                                <div class="stat-item">
                                    <span>👥</span>
                                    <span class="stat-number"><?= $course['students_count'] ?? 0 ?></span>
                                    <?= pluralize($course['students_count'] ?? 0, 'студент', 'студента', 'студентов') ?>
                                </div>
                                <div class="stat-item">
                                    <span>📝</span>
                                    <span class="stat-number"><?= $course['lessons_count'] ?? 0 ?></span>
                                    <?= pluralize($course['lessons_count'] ?? 0, 'урок', 'урока', 'уроков') ?>
                                </div>
                            </div>

                            <div class="course-actions">
                                <a href="/course/show?id=<?= $course['id'] ?>" class="course-btn btn-view">
                                    👁️ Просмотреть
                                </a>
                                <a href="/teacher/edit?id=<?= $course['id'] ?>" class="course-btn btn-edit">
                                    ✏️ Редактировать
                                </a>
                                <a href="/teacher/students?id=<?= $course['id'] ?>" class="course-btn btn-students">
                                    👥 Студенты
                                </a>
                                <a href="/teacher/delete?id=<?= $course['id'] ?>"
                                    class="course-btn btn-delete"
                                    onclick="return confirm('Удалить курс «<?= addslashes($course['title']) ?>»?')">
                                    🗑️ Удалить
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>