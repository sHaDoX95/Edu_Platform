<?php $user = Auth::user(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Прогресс студентов - <?= htmlspecialchars($course['title']) ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        .students-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .students-table th {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 18px;
            text-align: left;
            font-weight: 600;
            font-size: 1.1em;
        }
        
        .students-table td {
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .students-table tr:hover {
            background-color: #f8f9ff;
            transform: translateX(4px);
            transition: all 0.2s ease;
        }
        
        .students-table tr:last-child td {
            border-bottom: none;
        }
        
        .progress-cell {
            min-width: 120px;
        }
        
        .mini-progress {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 8px 0;
        }
        
        .mini-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            border-radius: 10px;
            transition: width 0.6s ease;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .students-table {
                font-size: 0.9em;
            }
            
            .students-table th,
            .students-table td {
                padding: 12px 8px;
            }
        }
    </style>
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

    <div class="container">
        <div class="action-buttons">
            <a href="/teacher" class="btn btn-secondary">← Назад к курсам</a>
            <a href="/course/show?id=<?= $course['id'] ?>" class="btn btn-primary">📚 Посмотреть курс</a>
        </div>

        <h2>📊 Прогресс студентов</h2>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">
            Курс: <strong>"<?= htmlspecialchars($course['title']) ?>"</strong>
        </p>

        <?php if (empty($students)): ?>
            <div class="empty-state">
                <div>📝</div>
                <h3>Пока нет данных о прогрессе</h3>
                <p>Студенты еще не начали проходить этот курс</p>
            </div>
        <?php else: ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Всего студентов</div>
                    <div class="stat-number"><?= count($students) ?></div>
                </div>
                
                <?php
                $avgProgress = 0;
                $completedCount = 0;
                foreach ($students as $s) {
                    $progress = $s['total_lessons'] > 0 
                        ? round(($s['completed_lessons'] / $s['total_lessons']) * 100)
                        : 0;
                    $avgProgress += $progress;
                    if ($progress == 100) $completedCount++;
                }
                $avgProgress = count($students) > 0 ? round($avgProgress / count($students)) : 0;
                ?>
                
                <div class="stat-card">
                    <div class="stat-label">Средний прогресс</div>
                    <div class="stat-number"><?= $avgProgress ?>%</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-label">Завершили курс</div>
                    <div class="stat-number"><?= $completedCount ?></div>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Студент</th>
                            <th>Уроки</th>
                            <th>Тесты</th>
                            <th>Прогресс</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s): ?>
                            <?php
                                $progress = $s['total_lessons'] > 0
                                    ? round(($s['completed_lessons'] / $s['total_lessons']) * 100)
                                    : 0;
                                
                                $progressClass = '';
                                if ($progress == 100) {
                                    $progressClass = 'badge-success';
                                } elseif ($progress >= 50) {
                                    $progressClass = 'badge-warning';
                                } else {
                                    $progressClass = 'badge-info';
                                }
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($s['user_name']) ?></strong>
                                    <?php if ($progress == 100): ?>
                                        <span class="badge badge-success">Завершено</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $s['completed_lessons'] ?> / <?= $s['total_lessons'] ?>
                                </td>
                                <td>
                                    <?= $s['passed_tests'] ?> пройдено
                                </td>
                                <td class="progress-cell">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-weight: bold; min-width: 40px;"><?= $progress ?>%</span>
                                        <div class="mini-progress">
                                            <div class="mini-progress-bar" style="width: <?= $progress ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.mini-progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 300);
            });
        });
    </script>
</body>
</html>