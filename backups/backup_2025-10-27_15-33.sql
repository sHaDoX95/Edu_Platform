--
-- PostgreSQL database dump
--

\restrict GdNHwYKUAdRDVsi30oKruQFxgqSdbMmzk7qTekoPgDHogFtU7VNk0bKXNM0gWaG

-- Dumped from database version 16.10 (Debian 16.10-1.pgdg13+1)
-- Dumped by pg_dump version 16.10 (Debian 16.10-1.pgdg13+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: courses; Type: TABLE; Schema: public; Owner: user
--

CREATE TABLE public.courses (
    id integer NOT NULL,
    title text NOT NULL,
    description text,
    teacher_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.courses OWNER TO "user";

--
-- Name: courses_id_seq; Type: SEQUENCE; Schema: public; Owner: user
--

CREATE SEQUENCE public.courses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.courses_id_seq OWNER TO "user";

--
-- Name: courses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: user
--

ALTER SEQUENCE public.courses_id_seq OWNED BY public.courses.id;


--
-- Name: lesson_progress; Type: TABLE; Schema: public; Owner: user
--

CREATE TABLE public.lesson_progress (
    id integer NOT NULL,
    user_id integer,
    lesson_id integer,
    completed_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    test_score integer,
    test_passed boolean DEFAULT false
);


ALTER TABLE public.lesson_progress OWNER TO "user";

--
-- Name: lesson_progress_id_seq; Type: SEQUENCE; Schema: public; Owner: user
--

CREATE SEQUENCE public.lesson_progress_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lesson_progress_id_seq OWNER TO "user";

--
-- Name: lesson_progress_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: user
--

ALTER SEQUENCE public.lesson_progress_id_seq OWNED BY public.lesson_progress.id;


--
-- Name: lessons; Type: TABLE; Schema: public; Owner: user
--

CREATE TABLE public.lessons (
    id integer NOT NULL,
    course_id integer,
    title text NOT NULL,
    content text
);


ALTER TABLE public.lessons OWNER TO "user";

--
-- Name: lessons_id_seq; Type: SEQUENCE; Schema: public; Owner: user
--

CREATE SEQUENCE public.lessons_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lessons_id_seq OWNER TO "user";

--
-- Name: lessons_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: user
--

ALTER SEQUENCE public.lessons_id_seq OWNED BY public.lessons.id;


--
-- Name: options; Type: TABLE; Schema: public; Owner: user
--

CREATE TABLE public.options (
    id integer NOT NULL,
    question_id integer,
    text text NOT NULL,
    is_correct boolean DEFAULT false
);


ALTER TABLE public.options OWNER TO "user";

--
-- Name: options_id_seq; Type: SEQUENCE; Schema: public; Owner: user
--

CREATE SEQUENCE public.options_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.options_id_seq OWNER TO "user";

--
-- Name: options_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: user
--

ALTER SEQUENCE public.options_id_seq OWNED BY public.options.id;


--
-- Name: questions; Type: TABLE; Schema: public; Owner: user
--

CREATE TABLE public.questions (
    id integer NOT NULL,
    lesson_id integer,
    question text NOT NULL
);


ALTER TABLE public.questions OWNER TO "user";

--
-- Name: questions_id_seq; Type: SEQUENCE; Schema: public; Owner: user
--

CREATE SEQUENCE public.questions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.questions_id_seq OWNER TO "user";

--
-- Name: questions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: user
--

ALTER SEQUENCE public.questions_id_seq OWNED BY public.questions.id;


--
-- Name: system_logs; Type: TABLE; Schema: public; Owner: user
--

CREATE TABLE public.system_logs (
    id integer NOT NULL,
    user_id integer,
    action text NOT NULL,
    ip text,
    details text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.system_logs OWNER TO "user";

--
-- Name: system_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: user
--

CREATE SEQUENCE public.system_logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.system_logs_id_seq OWNER TO "user";

--
-- Name: system_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: user
--

ALTER SEQUENCE public.system_logs_id_seq OWNED BY public.system_logs.id;


--
-- Name: system_settings; Type: TABLE; Schema: public; Owner: user
--

CREATE TABLE public.system_settings (
    id integer NOT NULL,
    key text NOT NULL,
    value text NOT NULL,
    description text,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.system_settings OWNER TO "user";

--
-- Name: system_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: user
--

CREATE SEQUENCE public.system_settings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.system_settings_id_seq OWNER TO "user";

--
-- Name: system_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: user
--

ALTER SEQUENCE public.system_settings_id_seq OWNED BY public.system_settings.id;


--
-- Name: teacher_student; Type: TABLE; Schema: public; Owner: user
--

CREATE TABLE public.teacher_student (
    id integer NOT NULL,
    teacher_id integer NOT NULL,
    student_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.teacher_student OWNER TO "user";

--
-- Name: teacher_student_id_seq; Type: SEQUENCE; Schema: public; Owner: user
--

CREATE SEQUENCE public.teacher_student_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.teacher_student_id_seq OWNER TO "user";

--
-- Name: teacher_student_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: user
--

ALTER SEQUENCE public.teacher_student_id_seq OWNED BY public.teacher_student.id;


--
-- Name: ticket_replies; Type: TABLE; Schema: public; Owner: user
--

CREATE TABLE public.ticket_replies (
    id integer NOT NULL,
    ticket_id integer,
    user_id integer,
    message text NOT NULL,
    created_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.ticket_replies OWNER TO "user";

--
-- Name: ticket_replies_id_seq; Type: SEQUENCE; Schema: public; Owner: user
--

CREATE SEQUENCE public.ticket_replies_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ticket_replies_id_seq OWNER TO "user";

--
-- Name: ticket_replies_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: user
--

ALTER SEQUENCE public.ticket_replies_id_seq OWNED BY public.ticket_replies.id;


--
-- Name: tickets; Type: TABLE; Schema: public; Owner: user
--

CREATE TABLE public.tickets (
    id integer NOT NULL,
    user_id integer,
    subject text NOT NULL,
    message text NOT NULL,
    status character varying(20) DEFAULT 'open'::character varying,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.tickets OWNER TO "user";

--
-- Name: tickets_id_seq; Type: SEQUENCE; Schema: public; Owner: user
--

CREATE SEQUENCE public.tickets_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tickets_id_seq OWNER TO "user";

--
-- Name: tickets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: user
--

ALTER SEQUENCE public.tickets_id_seq OWNED BY public.tickets.id;


--
-- Name: user_courses; Type: TABLE; Schema: public; Owner: user
--

CREATE TABLE public.user_courses (
    id integer NOT NULL,
    user_id integer NOT NULL,
    course_id integer NOT NULL,
    enrolled_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.user_courses OWNER TO "user";

--
-- Name: user_courses_id_seq; Type: SEQUENCE; Schema: public; Owner: user
--

CREATE SEQUENCE public.user_courses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_courses_id_seq OWNER TO "user";

--
-- Name: user_courses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: user
--

ALTER SEQUENCE public.user_courses_id_seq OWNED BY public.user_courses.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: user
--

CREATE TABLE public.users (
    id integer NOT NULL,
    email text NOT NULL,
    password text NOT NULL,
    name text,
    role text DEFAULT 'student'::text,
    blocked boolean DEFAULT false NOT NULL
);


ALTER TABLE public.users OWNER TO "user";

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: user
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO "user";

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: user
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: courses id; Type: DEFAULT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.courses ALTER COLUMN id SET DEFAULT nextval('public.courses_id_seq'::regclass);


--
-- Name: lesson_progress id; Type: DEFAULT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.lesson_progress ALTER COLUMN id SET DEFAULT nextval('public.lesson_progress_id_seq'::regclass);


--
-- Name: lessons id; Type: DEFAULT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.lessons ALTER COLUMN id SET DEFAULT nextval('public.lessons_id_seq'::regclass);


--
-- Name: options id; Type: DEFAULT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.options ALTER COLUMN id SET DEFAULT nextval('public.options_id_seq'::regclass);


--
-- Name: questions id; Type: DEFAULT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.questions ALTER COLUMN id SET DEFAULT nextval('public.questions_id_seq'::regclass);


--
-- Name: system_logs id; Type: DEFAULT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.system_logs ALTER COLUMN id SET DEFAULT nextval('public.system_logs_id_seq'::regclass);


--
-- Name: system_settings id; Type: DEFAULT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.system_settings ALTER COLUMN id SET DEFAULT nextval('public.system_settings_id_seq'::regclass);


--
-- Name: teacher_student id; Type: DEFAULT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.teacher_student ALTER COLUMN id SET DEFAULT nextval('public.teacher_student_id_seq'::regclass);


--
-- Name: ticket_replies id; Type: DEFAULT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.ticket_replies ALTER COLUMN id SET DEFAULT nextval('public.ticket_replies_id_seq'::regclass);


--
-- Name: tickets id; Type: DEFAULT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.tickets ALTER COLUMN id SET DEFAULT nextval('public.tickets_id_seq'::regclass);


--
-- Name: user_courses id; Type: DEFAULT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.user_courses ALTER COLUMN id SET DEFAULT nextval('public.user_courses_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: courses; Type: TABLE DATA; Schema: public; Owner: user
--

COPY public.courses (id, title, description, teacher_id, created_at) FROM stdin;
1	Курс по HTML	Основы HTML: теги, структура и верстка	\N	2025-10-27 13:58:50.861992
2	Курс по CSS	Оформление страниц, стили, макеты	\N	2025-10-27 13:58:50.861992
3	Курс по SQL	Работа с базами данных на SQL	\N	2025-10-27 13:58:50.861992
4	Курс по PHP	Основы серверного программирования	\N	2025-10-27 13:58:50.861992
\.


--
-- Data for Name: lesson_progress; Type: TABLE DATA; Schema: public; Owner: user
--

COPY public.lesson_progress (id, user_id, lesson_id, completed_at, test_score, test_passed) FROM stdin;
1	4	4	2025-10-27 17:44:50.026072	\N	f
4	4	2	2025-10-27 17:45:09.351357	100	t
3	4	1	2025-10-27 17:45:00.110428	0	f
\.


--
-- Data for Name: lessons; Type: TABLE DATA; Schema: public; Owner: user
--

COPY public.lessons (id, course_id, title, content) FROM stdin;
1	1	Что такое HTML?	HTML — язык разметки для создания страниц
2	1	Основные теги	Разберём теги: div, h1-h6, p, a, img
3	1	HTML-форма	Создание форм и элементов ввода
4	2	CSS-селекторы	Как выбрать нужный элемент и применить стиль
5	2	Цвета и фон	Работа с цветами, изображениями фона
6	2	Flexbox	Макеты на флексах — современный способ выравнивания
7	3	Введение в SQL	Что такое SQL и зачем он нужен
8	3	SELECT-запросы	Получение данных из таблиц
9	3	JOIN и связи	Объединение таблиц через JOIN
10	4	Основы PHP	Как устроен PHP-код, синтаксис
11	4	Формы и $_POST	Получение данных из форм
12	4	Работа с БД через PDO	Подключение к PostgreSQL с помощью PDO
\.


--
-- Data for Name: options; Type: TABLE DATA; Schema: public; Owner: user
--

COPY public.options (id, question_id, text, is_correct) FROM stdin;
1	1	Язык программирования	f
2	1	Язык разметки гипертекста	t
3	1	База данных	f
4	2	Для стилизации страницы	f
5	2	Для создания структуры веб-страницы	t
6	2	Для работы с сервером	f
7	3	Создает изображение	f
8	3	Определяет абзац текста	t
9	3	Делает текст жирным	f
10	4	<a>	t
11	4	<link>	f
12	4	<href>	f
13	5	<form>	t
14	5	<input>	f
15	5	<action>	f
16	6	action	t
17	6	value	f
18	6	method	f
19	7	p	t
20	7	.p	f
21	7	#p	f
22	8	Выбирает элемент с id "box"	f
23	8	Выбирает элементы с классом "box"	t
24	8	Создает элемент	f
25	9	color	t
26	9	text-color	f
27	9	font-color	f
28	10	background	t
29	10	fill	f
30	10	bg	f
31	11	display: flex;	t
32	11	flex: true;	f
33	11	position: flex;	f
34	12	justify-content: center;	t
35	12	align-center	f
36	12	text-align: center;	f
37	13	Язык программирования	f
38	13	Язык структурированных запросов	t
39	13	Операционная система	f
40	14	Создание стилей	f
41	14	Работа с базами данных	t
42	14	Создание приложений	f
43	15	Удаляет данные	f
44	15	Получает данные	t
45	15	Изменяет таблицу	f
46	16	SELECT * FROM users;	t
47	16	GET users;	f
48	16	SELECT ALL users;	f
49	17	Соединяет таблицы по совпадающим значениям	t
50	17	Создает новую таблицу	f
51	17	Удаляет лишние поля	f
52	18	FROM a JOIN b ON ...	t
53	18	LINK a TO b	f
54	18	COMBINE a WITH b	f
55	19	Фреймворк для JavaScript	f
56	19	Серверный язык программирования	t
57	19	Язык разметки	f
58	20	В браузере пользователя	f
59	20	На сервере	t
60	20	В редакторе кода	f
61	21	Форма ввода	f
62	21	Глобальный массив с отправленными данными	t
63	21	Команда SQL	f
64	22	method="get"	f
65	22	method="post"	t
66	22	action="post"	f
67	23	Формат документа	f
68	23	Интерфейс для работы с базами данных	t
69	23	Функция для загрузки файлов	f
70	24	new PDO("mysql:...")	f
71	24	new PDO("pgsql:...")	t
72	24	connect_pgsql()	f
\.


--
-- Data for Name: questions; Type: TABLE DATA; Schema: public; Owner: user
--

COPY public.questions (id, lesson_id, question) FROM stdin;
1	1	Что такое HTML?
2	1	Для чего используется HTML?
3	2	Что делает тег <p>?
4	2	Какой тег используется для ссылок?
5	3	Какой тег создаёт форму?
6	3	Какой атрибут нужен для отправки формы?
7	4	Как выбрать все элементы <p>?
8	4	Что делает селектор .box?
9	5	Как задать цвет текста?
10	5	Как задать фон для элемента?
11	6	Свойство для включения флекс-контейнера:
12	6	Свойство для выравнивания по центру:
13	7	Что такое SQL?
14	7	Для чего используется SQL?
15	8	Что делает SELECT-запрос?
16	8	Как выбрать все строки таблицы users?
17	9	Что делает INNER JOIN?
18	9	Как записывается соединение двух таблиц?
19	10	Что такое PHP?
20	10	Где обычно выполняется PHP-код?
21	11	Что такое $_POST?
22	11	Какой метод формы нужен для $_POST?
23	12	Что такое PDO в PHP?
24	12	Как подключиться к PostgreSQL через PDO?
\.


--
-- Data for Name: system_logs; Type: TABLE DATA; Schema: public; Owner: user
--

COPY public.system_logs (id, user_id, action, ip, details, created_at) FROM stdin;
1	4	Вход в систему	172.18.0.1	ID: 4, Email: admin1@example.com	2025-10-27 17:39:29.855853
2	4	Урок завершён	172.18.0.1	Пользователь ID 4 завершил урок ID 4	2025-10-27 17:44:45.950932
3	4	Отмена завершения урока	172.18.0.1	Пользователь ID 4 снял отметку с урока ID 4	2025-10-27 17:44:46.703752
4	4	Урок завершён	172.18.0.1	Пользователь ID 4 завершил урок ID 4	2025-10-27 17:44:50.056701
5	4	Урок завершён	172.18.0.1	Пользователь ID 4 завершил урок ID 1	2025-10-27 17:45:00.13986
6	4	Сохранён результат теста	172.18.0.1	Пользователь ID 4, урок ID 2, баллы: 50, пройдено: Нет	2025-10-27 17:45:09.366691
7	4	Сохранён результат теста	172.18.0.1	Пользователь ID 4, урок ID 2, баллы: 100, пройдено: Да	2025-10-27 17:45:31.368462
8	4	Сохранён результат теста	172.18.0.1	Пользователь ID 4, урок ID 1, баллы: 0, пройдено: Нет	2025-10-27 17:45:43.843975
\.


--
-- Data for Name: system_settings; Type: TABLE DATA; Schema: public; Owner: user
--

COPY public.system_settings (id, key, value, description, updated_at) FROM stdin;
1	site_name	Образовательная платформа	Название сайта	2025-10-27 13:58:50.793469
2	site_description	Платформа для онлайн-обучения	Описание сайта	2025-10-27 13:58:50.793469
3	registration_enabled	true	Разрешить регистрацию новых пользователей	2025-10-27 13:58:50.793469
4	max_courses_per_teacher	10	Максимум курсов на преподавателя	2025-10-27 13:58:50.793469
\.


--
-- Data for Name: teacher_student; Type: TABLE DATA; Schema: public; Owner: user
--

COPY public.teacher_student (id, teacher_id, student_id, created_at) FROM stdin;
\.


--
-- Data for Name: ticket_replies; Type: TABLE DATA; Schema: public; Owner: user
--

COPY public.ticket_replies (id, ticket_id, user_id, message, created_at) FROM stdin;
\.


--
-- Data for Name: tickets; Type: TABLE DATA; Schema: public; Owner: user
--

COPY public.tickets (id, user_id, subject, message, status, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: user_courses; Type: TABLE DATA; Schema: public; Owner: user
--

COPY public.user_courses (id, user_id, course_id, enrolled_at) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: user
--

COPY public.users (id, email, password, name, role, blocked) FROM stdin;
1	user1@example.com	$2y$10$LHl7NIdjSYcsWGwJXknnkOu7.GLJCK9ptzKB15ELG9KNSwcrce86K	user1	student	f
2	teacher1@example.com	$2y$10$LHl7NIdjSYcsWGwJXknnkOu7.GLJCK9ptzKB15ELG9KNSwcrce86K	teacher1	teacher	f
3	teacher2@example.com	$2y$10$LHl7NIdjSYcsWGwJXknnkOu7.GLJCK9ptzKB15ELG9KNSwcrce86K	teacher2	teacher	f
4	admin1@example.com	$2y$10$LHl7NIdjSYcsWGwJXknnkOu7.GLJCK9ptzKB15ELG9KNSwcrce86K	admin1	admin	f
\.


--
-- Name: courses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: user
--

SELECT pg_catalog.setval('public.courses_id_seq', 4, true);


--
-- Name: lesson_progress_id_seq; Type: SEQUENCE SET; Schema: public; Owner: user
--

SELECT pg_catalog.setval('public.lesson_progress_id_seq', 6, true);


--
-- Name: lessons_id_seq; Type: SEQUENCE SET; Schema: public; Owner: user
--

SELECT pg_catalog.setval('public.lessons_id_seq', 12, true);


--
-- Name: options_id_seq; Type: SEQUENCE SET; Schema: public; Owner: user
--

SELECT pg_catalog.setval('public.options_id_seq', 72, true);


--
-- Name: questions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: user
--

SELECT pg_catalog.setval('public.questions_id_seq', 24, true);


--
-- Name: system_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: user
--

SELECT pg_catalog.setval('public.system_logs_id_seq', 8, true);


--
-- Name: system_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: user
--

SELECT pg_catalog.setval('public.system_settings_id_seq', 4, true);


--
-- Name: teacher_student_id_seq; Type: SEQUENCE SET; Schema: public; Owner: user
--

SELECT pg_catalog.setval('public.teacher_student_id_seq', 1, false);


--
-- Name: ticket_replies_id_seq; Type: SEQUENCE SET; Schema: public; Owner: user
--

SELECT pg_catalog.setval('public.ticket_replies_id_seq', 1, false);


--
-- Name: tickets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: user
--

SELECT pg_catalog.setval('public.tickets_id_seq', 1, false);


--
-- Name: user_courses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: user
--

SELECT pg_catalog.setval('public.user_courses_id_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: user
--

SELECT pg_catalog.setval('public.users_id_seq', 4, true);


--
-- Name: courses courses_pkey; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.courses
    ADD CONSTRAINT courses_pkey PRIMARY KEY (id);


--
-- Name: lesson_progress lesson_progress_pkey; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.lesson_progress
    ADD CONSTRAINT lesson_progress_pkey PRIMARY KEY (id);


--
-- Name: lesson_progress lesson_progress_user_id_lesson_id_key; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.lesson_progress
    ADD CONSTRAINT lesson_progress_user_id_lesson_id_key UNIQUE (user_id, lesson_id);


--
-- Name: lessons lessons_pkey; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT lessons_pkey PRIMARY KEY (id);


--
-- Name: options options_pkey; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.options
    ADD CONSTRAINT options_pkey PRIMARY KEY (id);


--
-- Name: questions questions_pkey; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT questions_pkey PRIMARY KEY (id);


--
-- Name: system_logs system_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.system_logs
    ADD CONSTRAINT system_logs_pkey PRIMARY KEY (id);


--
-- Name: system_settings system_settings_key_key; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.system_settings
    ADD CONSTRAINT system_settings_key_key UNIQUE (key);


--
-- Name: system_settings system_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.system_settings
    ADD CONSTRAINT system_settings_pkey PRIMARY KEY (id);


--
-- Name: teacher_student teacher_student_pkey; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.teacher_student
    ADD CONSTRAINT teacher_student_pkey PRIMARY KEY (id);


--
-- Name: teacher_student teacher_student_teacher_id_student_id_key; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.teacher_student
    ADD CONSTRAINT teacher_student_teacher_id_student_id_key UNIQUE (teacher_id, student_id);


--
-- Name: ticket_replies ticket_replies_pkey; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.ticket_replies
    ADD CONSTRAINT ticket_replies_pkey PRIMARY KEY (id);


--
-- Name: tickets tickets_pkey; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_pkey PRIMARY KEY (id);


--
-- Name: user_courses user_courses_pkey; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.user_courses
    ADD CONSTRAINT user_courses_pkey PRIMARY KEY (id);


--
-- Name: user_courses user_courses_user_id_course_id_key; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.user_courses
    ADD CONSTRAINT user_courses_user_id_course_id_key UNIQUE (user_id, course_id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: idx_lesson_progress_user_lesson; Type: INDEX; Schema: public; Owner: user
--

CREATE INDEX idx_lesson_progress_user_lesson ON public.lesson_progress USING btree (user_id, lesson_id);


--
-- Name: idx_system_logs_created_at; Type: INDEX; Schema: public; Owner: user
--

CREATE INDEX idx_system_logs_created_at ON public.system_logs USING btree (created_at);


--
-- Name: idx_tickets_status; Type: INDEX; Schema: public; Owner: user
--

CREATE INDEX idx_tickets_status ON public.tickets USING btree (status);


--
-- Name: courses courses_teacher_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.courses
    ADD CONSTRAINT courses_teacher_id_fkey FOREIGN KEY (teacher_id) REFERENCES public.users(id);


--
-- Name: lesson_progress lesson_progress_lesson_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.lesson_progress
    ADD CONSTRAINT lesson_progress_lesson_id_fkey FOREIGN KEY (lesson_id) REFERENCES public.lessons(id);


--
-- Name: lesson_progress lesson_progress_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.lesson_progress
    ADD CONSTRAINT lesson_progress_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: lessons lessons_course_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT lessons_course_id_fkey FOREIGN KEY (course_id) REFERENCES public.courses(id);


--
-- Name: options options_question_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.options
    ADD CONSTRAINT options_question_id_fkey FOREIGN KEY (question_id) REFERENCES public.questions(id) ON DELETE CASCADE;


--
-- Name: questions questions_lesson_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT questions_lesson_id_fkey FOREIGN KEY (lesson_id) REFERENCES public.lessons(id) ON DELETE CASCADE;


--
-- Name: system_logs system_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.system_logs
    ADD CONSTRAINT system_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: teacher_student teacher_student_student_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.teacher_student
    ADD CONSTRAINT teacher_student_student_id_fkey FOREIGN KEY (student_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: teacher_student teacher_student_teacher_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.teacher_student
    ADD CONSTRAINT teacher_student_teacher_id_fkey FOREIGN KEY (teacher_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: ticket_replies ticket_replies_ticket_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.ticket_replies
    ADD CONSTRAINT ticket_replies_ticket_id_fkey FOREIGN KEY (ticket_id) REFERENCES public.tickets(id) ON DELETE CASCADE;


--
-- Name: ticket_replies ticket_replies_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.ticket_replies
    ADD CONSTRAINT ticket_replies_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: tickets tickets_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: user_courses user_courses_course_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.user_courses
    ADD CONSTRAINT user_courses_course_id_fkey FOREIGN KEY (course_id) REFERENCES public.courses(id) ON DELETE CASCADE;


--
-- Name: user_courses user_courses_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: user
--

ALTER TABLE ONLY public.user_courses
    ADD CONSTRAINT user_courses_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict GdNHwYKUAdRDVsi30oKruQFxgqSdbMmzk7qTekoPgDHogFtU7VNk0bKXNM0gWaG

