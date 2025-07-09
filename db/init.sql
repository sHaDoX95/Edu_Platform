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

INSERT INTO courses (title, description) VALUES
('Курс по HTML', 'Базовый курс по HTML-разметке'),
('Курс по SQL', 'Изучение основ работы с базами данных');

INSERT INTO lessons (course_id, title, content) VALUES
(1, 'Введение в HTML', 'HTML — это язык разметки...'),
(1, 'Теги и структура', 'Разбираем базовые теги...'),
(2, 'Основы SQL', 'SQL позволяет взаимодействовать с БД...');