<?php
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../models/SystemSetting.php';

$user = Auth::user();
$platformName = SystemSetting::get('site_name', 'Образовательная платформа');
$platformDescription = SystemSetting::get('site_description', 'Откройте для себя новые знания и развивайте свои навыки');
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title><?= htmlspecialchars($platformName) ?></title>
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

    <div class="container">
        <div class="hero-section">
            <h1 class="hero-title">🎓 <?= htmlspecialchars($platformName) ?></h1>
            <p class="hero-subtitle"><?= htmlspecialchars($platformDescription) ?></p>

            <div class="search-container">
                <input type="text" id="search" class="search-input" placeholder="Поиск курсов...">
                <span class="search-icon">🔍</span>
            </div>
        </div>

        <div class="courses-grid" id="courses-grid">
            <?php if (empty($courses)): ?>
                <div class="empty-state">
                    <div>📚</div>
                    <h3>Курсы не найдены</h3>
                    <p>На данный момент нет доступных курсов</p>
                </div>
            <?php else: ?>
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <?php if ($user['role'] === 'teacher' && $course['teacher_id'] == $user['id']): ?>
                            <span class="course-badge">Ваш курс</span>
                        <?php endif; ?>

                        <h3 class="course-title">
                            <a href="/course/show?id=<?= $course['id'] ?>">
                                <?= htmlspecialchars($course['title']) ?>
                            </a>
                        </h3>

                        <p class="course-description">
                            <?= htmlspecialchars($course['description']) ?>
                        </p>

                        <div class="course-meta">
                            <span style="color: #666; font-size: 0.9em;">
                                📝 <?= $course['lessons_count'] ?> <?= pluralize($course['lessons_count'], 'урок', 'урока', 'уроков') ?>
                            </span>
                            <a href="/course/show?id=<?= $course['id'] ?>" class="course-action">
                                Начать обучение →
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.getElementById('search').addEventListener('input', function() {
            const query = this.value;
            const grid = document.getElementById('courses-grid');

            if (query.length < 1) {
                showAllCourses();
                return;
            }

            grid.innerHTML = '<div class="loading pulse">🔍 Поиск курсов...</div>';

            fetch('/course/search?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    grid.innerHTML = '';

                    if (data.length === 0) {
                        grid.innerHTML = `
                        <div class="empty-state">
                            <div>🔍</div>
                            <h3>Ничего не найдено</h3>
                            <p>Попробуйте изменить поисковый запрос</p>
                        </div>
                    `;
                    } else {
                        data.forEach(course => {
                            const card = document.createElement('div');
                            card.className = 'course-card';

                            function pluralizeJs(number, one, two, five) {
                                const n = Math.abs(number) % 100;
                                const n1 = n % 10;
                                if (n > 10 && n < 20) return five;
                                if (n1 > 1 && n1 < 5) return two;
                                if (n1 == 1) return one;
                                return five;
                            }

                            card.innerHTML = `
                            <h3 class="course-title">
                                <a href="/course/show?id=${course.id}">${course.title}</a>
                            </h3>
                            <p class="course-description">${course.description}</p>
                            <div class="course-meta">
                                <span style="color: #666; font-size: 0.9em;">
                                    📝 ${course.lessons_count || 0} ${pluralizeJs(course.lessons_count || 0, 'урок', 'урока', 'уроков')}
                                </span>
                                <a href="/course/show?id=${course.id}" class="course-action">
                                    Начать обучение →
                                </a>
                            </div>
                        `;
                            grid.appendChild(card);
                        });
                    }
                })
                .catch(error => {
                    grid.innerHTML = `
                    <div class="empty-state">
                        <div>⚠️</div>
                        <h3>Ошибка поиска</h3>
                        <p>Попробуйте позже</p>
                    </div>
                `;
                });
        });

        function showAllCourses() {
            if (document.getElementById('search').value === '') {
                window.location.reload();
            }
        }
    </script>
</body>

</html>