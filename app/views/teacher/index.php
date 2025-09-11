<?php
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
<body>
    <nav>
        <p>
            Вы вошли как <strong><?= htmlspecialchars($user['name']) ?></strong> |
            <a href="/course">📚 Все курсы</a> |
            <a href="/teacher/create">➕ Создать курс</a> |
            <a href="/auth/logout">🚪 Выйти</a>
        </p>
    </nav>

    <div class="container">
        <div class="teacher-header">
            <div class="teacher-avatar">👨‍🏫</div>
            <h1 class="teacher-title">Кабинет преподавателя</h1>
            <p class="teacher-subtitle">Добро пожаловать, <strong><?= htmlspecialchars($user['name']) ?></strong>!</p>
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
                                    <span class="stat-number">0</span> студентов
                                </div>
                                <div class="stat-item">
                                    <span>📝</span>
                                    <span class="stat-number">0</span> уроков
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