CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  email TEXT UNIQUE NOT NULL,
  password TEXT NOT NULL,
  name TEXT
);

CREATE TABLE IF NOT EXISTS courses (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT
);

CREATE TABLE IF NOT EXISTS lessons (
  id SERIAL PRIMARY KEY,
  course_id INTEGER REFERENCES courses(id),
  title TEXT NOT NULL,
  content TEXT
);

CREATE TABLE IF NOT EXISTS lesson_progress (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    lesson_id INTEGER REFERENCES lessons(id),
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, lesson_id)
);

CREATE TABLE questions (
    id SERIAL PRIMARY KEY,
    lesson_id INT REFERENCES lessons(id) ON DELETE CASCADE,
    question TEXT NOT NULL
);

CREATE TABLE options (
    id SERIAL PRIMARY KEY,
    question_id INT REFERENCES questions(id) ON DELETE CASCADE,
    text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE
);

INSERT INTO courses (title, description) VALUES
('Курс по HTML', 'Основы HTML: теги, структура и верстка'),
('Курс по CSS', 'Оформление страниц, стили, макеты'),
('Курс по SQL', 'Работа с базами данных на SQL'),
('Курс по PHP', 'Основы серверного программирования');

INSERT INTO lessons (course_id, title, content) VALUES
(1, 'Что такое HTML?', 'HTML — язык разметки для создания страниц'),
(1, 'Основные теги', 'Разберём теги: div, h1-h6, p, a, img'),
(1, 'HTML-форма', 'Создание форм и элементов ввода'),

(2, 'CSS-селекторы', 'Как выбрать нужный элемент и применить стиль'),
(2, 'Цвета и фон', 'Работа с цветами, изображениями фона'),
(2, 'Flexbox', 'Макеты на флексах — современный способ выравнивания'),

(3, 'Введение в SQL', 'Что такое SQL и зачем он нужен'),
(3, 'SELECT-запросы', 'Получение данных из таблиц'),
(3, 'JOIN и связи', 'Объединение таблиц через JOIN'),

(4, 'Основы PHP', 'Как устроен PHP-код, синтаксис'),
(4, 'Формы и $_POST', 'Получение данных из форм'),
(4, 'Работа с БД через PDO', 'Подключение к PostgreSQL с помощью PDO');