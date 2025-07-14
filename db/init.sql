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

-- === Курс по HTML ===

-- Урок 1: Что такое HTML?
INSERT INTO questions (lesson_id, question) VALUES
(1, 'Что такое HTML?');
INSERT INTO options (question_id, text, is_correct) VALUES
(1, 'Язык программирования', FALSE),
(1, 'Язык разметки гипертекста', TRUE),
(1, 'База данных', FALSE);

INSERT INTO questions (lesson_id, question) VALUES
(1, 'Для чего используется HTML?');
INSERT INTO options (question_id, text, is_correct) VALUES
(2, 'Для стилизации страницы', FALSE),
(2, 'Для создания структуры веб-страницы', TRUE),
(2, 'Для работы с сервером', FALSE);

-- Урок 2: Основные теги
INSERT INTO questions (lesson_id, question) VALUES
(2, 'Что делает тег <p>?');
INSERT INTO options (question_id, text, is_correct) VALUES
(3, 'Создает изображение', FALSE),
(3, 'Определяет абзац текста', TRUE),
(3, 'Делает текст жирным', FALSE);

INSERT INTO questions (lesson_id, question) VALUES
(2, 'Какой тег используется для ссылок?');
INSERT INTO options (question_id, text, is_correct) VALUES
(4, '<a>', TRUE),
(4, '<link>', FALSE),
(4, '<href>', FALSE);

-- Урок 3: HTML-форма
INSERT INTO questions (lesson_id, question) VALUES
(3, 'Какой тег создаёт форму?');
INSERT INTO options (question_id, text, is_correct) VALUES
(5, '<form>', TRUE),
(5, '<input>', FALSE),
(5, '<action>', FALSE);

INSERT INTO questions (lesson_id, question) VALUES
(3, 'Какой атрибут нужен для отправки формы?');
INSERT INTO options (question_id, text, is_correct) VALUES
(6, 'action', TRUE),
(6, 'value', FALSE),
(6, 'method', FALSE);

-- === Курс по CSS ===

-- Урок 4: CSS-селекторы
INSERT INTO questions (lesson_id, question) VALUES
(4, 'Как выбрать все элементы <p>?');
INSERT INTO options (question_id, text, is_correct) VALUES
(7, 'p', TRUE),
(7, '.p', FALSE),
(7, '#p', FALSE);

INSERT INTO questions (lesson_id, question) VALUES
(4, 'Что делает селектор .box?');
INSERT INTO options (question_id, text, is_correct) VALUES
(8, 'Выбирает элемент с id "box"', FALSE),
(8, 'Выбирает элементы с классом "box"', TRUE),
(8, 'Создает элемент', FALSE);

-- Урок 5: Цвета и фон
INSERT INTO questions (lesson_id, question) VALUES
(5, 'Как задать цвет текста?');
INSERT INTO options (question_id, text, is_correct) VALUES
(9, 'color', TRUE),
(9, 'text-color', FALSE),
(9, 'font-color', FALSE);

INSERT INTO questions (lesson_id, question) VALUES
(5, 'Как задать фон для элемента?');
INSERT INTO options (question_id, text, is_correct) VALUES
(10, 'background', TRUE),
(10, 'fill', FALSE),
(10, 'bg', FALSE);

-- Урок 6: Flexbox
INSERT INTO questions (lesson_id, question) VALUES
(6, 'Свойство для включения флекс-контейнера:');
INSERT INTO options (question_id, text, is_correct) VALUES
(11, 'display: flex;', TRUE),
(11, 'flex: true;', FALSE),
(11, 'position: flex;', FALSE);

INSERT INTO questions (lesson_id, question) VALUES
(6, 'Свойство для выравнивания по центру:');
INSERT INTO options (question_id, text, is_correct) VALUES
(12, 'justify-content: center;', TRUE),
(12, 'align-center', FALSE),
(12, 'text-align: center;', FALSE);

-- === Курс по SQL ===

-- Урок 7: Введение в SQL
INSERT INTO questions (lesson_id, question) VALUES
(7, 'Что такое SQL?');
INSERT INTO options (question_id, text, is_correct) VALUES
(13, 'Язык программирования', FALSE),
(13, 'Язык структурированных запросов', TRUE),
(13, 'Операционная система', FALSE);

INSERT INTO questions (lesson_id, question) VALUES
(7, 'Для чего используется SQL?');
INSERT INTO options (question_id, text, is_correct) VALUES
(14, 'Создание стилей', FALSE),
(14, 'Работа с базами данных', TRUE),
(14, 'Создание приложений', FALSE);

-- Урок 8: SELECT-запросы
INSERT INTO questions (lesson_id, question) VALUES
(8, 'Что делает SELECT-запрос?');
INSERT INTO options (question_id, text, is_correct) VALUES
(15, 'Удаляет данные', FALSE),
(15, 'Получает данные', TRUE),
(15, 'Изменяет таблицу', FALSE);

INSERT INTO questions (lesson_id, question) VALUES
(8, 'Как выбрать все строки таблицы users?');
INSERT INTO options (question_id, text, is_correct) VALUES
(16, 'SELECT * FROM users;', TRUE),
(16, 'GET users;', FALSE),
(16, 'SELECT ALL users;', FALSE);

-- Урок 9: JOIN и связи
INSERT INTO questions (lesson_id, question) VALUES
(9, 'Что делает INNER JOIN?');
INSERT INTO options (question_id, text, is_correct) VALUES
(17, 'Соединяет таблицы по совпадающим значениям', TRUE),
(17, 'Создает новую таблицу', FALSE),
(17, 'Удаляет лишние поля', FALSE);

INSERT INTO questions (lesson_id, question) VALUES
(9, 'Как записывается соединение двух таблиц?');
INSERT INTO options (question_id, text, is_correct) VALUES
(18, 'FROM a JOIN b ON ...', TRUE),
(18, 'LINK a TO b', FALSE),
(18, 'COMBINE a WITH b', FALSE);

-- === Курс по PHP ===

-- Урок 10: Основы PHP
INSERT INTO questions (lesson_id, question) VALUES
(10, 'Что такое PHP?');
INSERT INTO options (question_id, text, is_correct) VALUES
(19, 'Фреймворк для JavaScript', FALSE),
(19, 'Серверный язык программирования', TRUE),
(19, 'Язык разметки', FALSE);

INSERT INTO questions (lesson_id, question) VALUES
(10, 'Где обычно выполняется PHP-код?');
INSERT INTO options (question_id, text, is_correct) VALUES
(20, 'В браузере пользователя', FALSE),
(20, 'На сервере', TRUE),
(20, 'В редакторе кода', FALSE);

-- Урок 11: Формы и $_POST
INSERT INTO questions (lesson_id, question) VALUES
(11, 'Что такое $_POST?');
INSERT INTO options (question_id, text, is_correct) VALUES
(21, 'Форма ввода', FALSE),
(21, 'Глобальный массив с отправленными данными', TRUE),
(21, 'Команда SQL', FALSE);

INSERT INTO questions (lesson_id, question) VALUES
(11, 'Какой метод формы нужен для $_POST?');
INSERT INTO options (question_id, text, is_correct) VALUES
(22, 'method="get"', FALSE),
(22, 'method="post"', TRUE),
(22, 'action="post"', FALSE);

-- Урок 12: Работа с БД через PDO
INSERT INTO questions (lesson_id, question) VALUES
(12, 'Что такое PDO в PHP?');
INSERT INTO options (question_id, text, is_correct) VALUES
(23, 'Формат документа', FALSE),
(23, 'Интерфейс для работы с базами данных', TRUE),
(23, 'Функция для загрузки файлов', FALSE);

INSERT INTO questions (lesson_id, question) VALUES
(12, 'Как подключиться к PostgreSQL через PDO?');
INSERT INTO options (question_id, text, is_correct) VALUES
(24, 'new PDO("mysql:...")', FALSE),
(24, 'new PDO("pgsql:...")', TRUE),
(24, 'connect_pgsql()', FALSE);
