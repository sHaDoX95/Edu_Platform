<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Logger.php';

class ChatController
{
    // Список всех чатов для текущего пользователя
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: /auth/login');
            exit;
        }

        $db = Database::connect();

        // Пагинация для админа
        $limit = 10;
        $currentPage = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($currentPage - 1) * $limit;
        $q = trim($_GET['q'] ?? '');

        if ($user['role'] === 'admin') {
            // Админ видит все чаты с пагинацией
            $where = '';
            $params = [];

            if ($q !== '') {
                $where = "WHERE c.title ILIKE ?";
                $params[] = "%$q%";
            }

            // Получаем общее количество
            $countStmt = $db->prepare("SELECT COUNT(*) FROM chats c $where");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();
            $pages = ceil($total / $limit);

            $sql = "SELECT c.*, u.name AS teacher_name, 
                           (SELECT COUNT(*) FROM chat_participants cp WHERE cp.chat_id = c.id) as participants_count
                    FROM chats c 
                    LEFT JOIN users u ON c.teacher_id = u.id
                    $where
                    ORDER BY c.created_at DESC
                    LIMIT ? OFFSET ?";

            $stmt = $db->prepare($sql);
            if ($q !== '') {
                $stmt->execute([...$params, $limit, $offset]);
            } else {
                $stmt->execute([$limit, $offset]);
            }

            $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Получаем преподавателей для формы
            $teachers = $db->query("SELECT id, name FROM users WHERE role = 'teacher'")->fetchAll(PDO::FETCH_ASSOC);
            $students = $db->query("SELECT id, name FROM users WHERE role = 'student'")->fetchAll(PDO::FETCH_ASSOC);

            include __DIR__ . '/../views/admin/chats.php';
        } else {
            // Остальные — только те, где участвуют
            $stmt = $db->prepare("SELECT c.*, u.name AS teacher_name
                                  FROM chats c
                                  LEFT JOIN users u ON c.teacher_id = u.id
                                  JOIN chat_participants cp ON c.id = cp.chat_id
                                  WHERE cp.user_id = :uid
                                  ORDER BY c.created_at DESC");
            $stmt->execute(['uid' => $user['id']]);
            $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            include __DIR__ . '/../views/chat/index.php';
        }
    }

    // Форма создания нового чата (для админа)
    public function create()
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'admin') {
            header('Location: /chat');
            exit;
        }

        $db = Database::connect();
        $teachers = $db->query("SELECT id, name FROM users WHERE role = 'teacher'")->fetchAll(PDO::FETCH_ASSOC);
        $students = $db->query("SELECT id, name FROM users WHERE role = 'student'")->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/admin/chats_create.php';
    }

    // Сохранение нового чата
    public function store()
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'admin') {
            header('Location: /chat');
            exit;
        }

        $db = Database::connect();

        // Получаем и валидируем данные
        $title = trim($_POST['title'] ?? '');
        $teacher_id = $_POST['teacher_id'] ?? null;
        $participants = $_POST['participants'] ?? [];

        // Валидация
        if (empty($title)) {
            $_SESSION['flash_error'] = 'Название чата не может быть пустым';
            header('Location: /admin/chats/create');
            exit;
        }

        if (empty($teacher_id)) {
            $_SESSION['flash_error'] = 'Необходимо выбрать преподавателя';
            header('Location: /admin/chats/create');
            exit;
        }

        // Преобразуем teacher_id в integer
        $teacher_id = (int)$teacher_id;

        if ($teacher_id <= 0) {
            $_SESSION['flash_error'] = 'Неверный преподаватель';
            header('Location: /admin/chats/create');
            exit;
        }

        // Фильтруем участников - убираем пустые значения и преобразуем в integer
        $participants = array_filter($participants, function ($p) {
            return !empty($p);
        });
        $participants = array_map('intval', $participants);

        if (empty($participants)) {
            $_SESSION['flash_error'] = 'Необходимо выбрать хотя бы одного участника';
            header('Location: /admin/chats/create');
            exit;
        }

        try {
            $stmt = $db->prepare("INSERT INTO chats (title, teacher_id, created_by) VALUES (:title, :teacher_id, :created_by) RETURNING id");
            $stmt->execute([
                'title' => $title,
                'teacher_id' => $teacher_id,
                'created_by' => $user['id']
            ]);

            $chat_id = $stmt->fetchColumn();

            // Получаем имя преподавателя для лога
            $teacher_name = $db->query("SELECT name FROM users WHERE id = $teacher_id")->fetchColumn();

            // Получаем имена участников для лога
            $participant_names = [];
            if (!empty($participants)) {
                $placeholders = str_repeat('?,', count($participants) - 1) . '?';
                $stmt = $db->prepare("SELECT name FROM users WHERE id IN ($placeholders)");
                $stmt->execute($participants);
                $participant_names = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            // Добавляем участников
            $insert = $db->prepare("INSERT INTO chat_participants (chat_id, user_id) VALUES (:chat_id, :user_id)");
            foreach ($participants as $p) {
                if ($p > 0) {
                    $insert->execute(['chat_id' => $chat_id, 'user_id' => $p]);
                }
            }

            // Добавляем преподавателя тоже (если его еще нет в участниках)
            if (!in_array($teacher_id, $participants)) {
                $insert->execute(['chat_id' => $chat_id, 'user_id' => $teacher_id]);
            }

            // Логируем создание чата
            $participants_list = implode(', ', $participant_names);
            Logger::log(
                "Создан чат",
                "ID: $chat_id, Название: $title, Преподаватель: $teacher_name, Участники: $participants_list"
            );

            $_SESSION['flash_success'] = 'Чат успешно создан!';
            header('Location: /admin/chats');
            exit;
        } catch (PDOException $e) {
            error_log("Chat creation error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Ошибка при создании чата: ' . $e->getMessage();
            header('Location: /admin/chats/create');
            exit;
        }
    }

    // Просмотр чата
    public function view($id)
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: /auth/login');
            exit;
        }

        $db = Database::connect();

        // Проверяем, что пользователь имеет доступ
        $stmt = $db->prepare("SELECT * FROM chat_participants WHERE chat_id = :chat_id AND user_id = :uid");
        $stmt->execute(['chat_id' => $id, 'uid' => $user['id']]);
        if (!$stmt->fetch() && $user['role'] !== 'admin') {
            die("Доступ запрещён");
        }

        // Получаем чат и сообщения
        $chat = $db->query("SELECT c.*, u.name as teacher_name FROM chats c LEFT JOIN users u ON c.teacher_id = u.id WHERE c.id = $id")->fetch(PDO::FETCH_ASSOC);

        $messages = $db->query("
            SELECT m.*, u.name, u.role 
            FROM chat_messages m 
            JOIN users u ON m.sender_id = u.id 
            WHERE chat_id = $id 
            ORDER BY m.created_at ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/chat/view.php';
    }

    // Отправка сообщения (AJAX)
    public function sendMessage()
    {
        $user = Auth::user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Не авторизован']);
            exit;
        }

        $db = Database::connect();
        $chat_id = $_POST['chat_id'];
        $message = trim($_POST['message']);

        if (empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Сообщение не может быть пустым']);
            exit;
        }

        // Проверяем доступ к чату
        $stmt = $db->prepare("SELECT * FROM chat_participants WHERE chat_id = :chat_id AND user_id = :uid");
        $stmt->execute(['chat_id' => $chat_id, 'uid' => $user['id']]);
        if (!$stmt->fetch() && $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
            exit;
        }

        // Сохраняем сообщение
        $stmt = $db->prepare("INSERT INTO chat_messages (chat_id, sender_id, message) VALUES (:chat_id, :sender_id, :message) RETURNING id, created_at");
        $stmt->execute([
            'chat_id' => $chat_id,
            'sender_id' => $user['id'],
            'message' => $message
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Логируем отправку сообщения (только для админов)
        if ($user['role'] === 'admin') {
            $chat_title = $db->query("SELECT title FROM chats WHERE id = $chat_id")->fetchColumn();
            Logger::log(
                "Отправлено сообщение в чат",
                "Чат: $chat_title (ID: $chat_id), Сообщение: " . (strlen($message) > 50 ? substr($message, 0, 50) . '...' : $message)
            );
        }

        echo json_encode([
            'success' => true,
            'message_id' => $result['id'],
            'created_at' => $result['created_at'],
            'sender_name' => $user['name'],
            'sender_role' => $user['role']
        ]);
    }

    // Получение новых сообщений (AJAX)
    public function getMessages($chat_id)
    {
        $user = Auth::user();
        if (!$user) {
            http_response_code(401);
            exit;
        }

        $db = Database::connect();

        // Проверяем доступ
        $stmt = $db->prepare("SELECT * FROM chat_participants WHERE chat_id = :chat_id AND user_id = :uid");
        $stmt->execute(['chat_id' => $chat_id, 'uid' => $user['id']]);
        if (!$stmt->fetch() && $user['role'] !== 'admin') {
            http_response_code(403);
            exit;
        }

        $last_id = $_GET['last_id'] ?? 0;

        $messages = $db->query("
            SELECT m.*, u.name, u.role 
            FROM chat_messages m 
            JOIN users u ON m.sender_id = u.id 
            WHERE chat_id = $chat_id AND m.id > $last_id
            ORDER BY m.created_at ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($messages);
    }

    // Обновление названия чата (AJAX)
    public function updateTitle()
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
            exit;
        }

        $chat_id = $_POST['chat_id'] ?? null;
        $title = trim($_POST['title'] ?? '');

        if (!$chat_id || !$title) {
            echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
            exit;
        }

        $db = Database::connect();

        // Получаем старое название для лога
        $old_title = $db->query("SELECT title FROM chats WHERE id = $chat_id")->fetchColumn();

        $stmt = $db->prepare("UPDATE chats SET title = ? WHERE id = ?");
        $stmt->execute([$title, $chat_id]);

        // Логируем изменение названия
        Logger::log(
            "Изменено название чата",
            "ID чата: $chat_id, Старое название: $old_title, Новое название: $title"
        );

        echo json_encode(['success' => true]);
    }

    // Обновление преподавателя чата (AJAX)
    public function updateTeacher()
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
            exit;
        }

        $chat_id = $_POST['chat_id'] ?? null;
        $teacher_id = $_POST['teacher_id'] ?? null;

        if (!$chat_id) {
            echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
            exit;
        }

        $db = Database::connect();

        // Получаем старые данные для лога
        $old_teacher_id = $db->query("SELECT teacher_id FROM chats WHERE id = $chat_id")->fetchColumn();
        $old_teacher_name = $db->query("SELECT name FROM users WHERE id = $old_teacher_id")->fetchColumn();
        $new_teacher_name = $teacher_id ? $db->query("SELECT name FROM users WHERE id = $teacher_id")->fetchColumn() : 'Не назначен';

        // Обновляем преподавателя в чате
        $stmt = $db->prepare("UPDATE chats SET teacher_id = ? WHERE id = ?");
        $stmt->execute([$teacher_id, $chat_id]);

        // Удаляем старого преподавателя из участников и добавляем нового
        if ($teacher_id) {
            // Удаляем старого преподавателя
            $deleteStmt = $db->prepare("DELETE FROM chat_participants WHERE chat_id = ? AND user_id IN (SELECT teacher_id FROM chats WHERE id = ? AND teacher_id IS NOT NULL)");
            $deleteStmt->execute([$chat_id, $chat_id]);

            // Добавляем нового преподавателя
            $insertStmt = $db->prepare("INSERT INTO chat_participants (chat_id, user_id) VALUES (?, ?) ON CONFLICT DO NOTHING");
            $insertStmt->execute([$chat_id, $teacher_id]);
        }

        // Логируем изменение преподавателя
        Logger::log(
            "Изменен преподаватель чата",
            "ID чата: $chat_id, Старый преподаватель: $old_teacher_name, Новый преподаватель: $new_teacher_name"
        );

        echo json_encode(['success' => true]);
    }

    // Удаление чата
    public function delete()
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'admin') {
            header('Location: /chat');
            exit;
        }

        $chat_id = $_POST['chat_id'] ?? null;

        if ($chat_id) {
            $db = Database::connect();

            try {
                // Получаем информацию о чате для лога
                $chat_info = $db->query("SELECT title, teacher_id FROM chats WHERE id = $chat_id")->fetch(PDO::FETCH_ASSOC);
                $chat_title = $chat_info['title'] ?? 'Неизвестно';
                $teacher_id = $chat_info['teacher_id'] ?? null;
                $teacher_name = $teacher_id ? $db->query("SELECT name FROM users WHERE id = $teacher_id")->fetchColumn() : 'Не назначен';

                // Получаем количество сообщений и участников для лога
                $messages_count = $db->query("SELECT COUNT(*) FROM chat_messages WHERE chat_id = $chat_id")->fetchColumn();
                $participants_count = $db->query("SELECT COUNT(*) FROM chat_participants WHERE chat_id = $chat_id")->fetchColumn();

                // Начинаем транзакцию для безопасного удаления
                $db->beginTransaction();

                // Удаляем сообщения чата
                $stmt = $db->prepare("DELETE FROM chat_messages WHERE chat_id = ?");
                $stmt->execute([$chat_id]);

                // Удаляем участников чата
                $stmt = $db->prepare("DELETE FROM chat_participants WHERE chat_id = ?");
                $stmt->execute([$chat_id]);

                // Удаляем сам чат
                $stmt = $db->prepare("DELETE FROM chats WHERE id = ?");
                $stmt->execute([$chat_id]);

                $db->commit();

                // Логируем удаление чата
                Logger::log(
                    "Удален чат",
                    "ID: $chat_id, Название: $chat_title, Преподаватель: $teacher_name, " .
                        "Сообщений: $messages_count, Участников: $participants_count"
                );

                $_SESSION['flash_success'] = 'Чат успешно удален!';
            } catch (PDOException $e) {
                $db->rollBack();
                error_log("Chat deletion error: " . $e->getMessage());
                $_SESSION['flash_error'] = 'Ошибка при удалении чата';
            }
        } else {
            $_SESSION['flash_error'] = 'Ошибка при удалении чата';
        }

        header('Location: /admin/chats');
        exit;
    }

    // Форма редактирования чата
    public function edit($id)
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'admin') {
            header('Location: /chat');
            exit;
        }

        $db = Database::connect();

        // Получаем данные чата
        $chat = $db->query("SELECT * FROM chats WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
        if (!$chat) {
            $_SESSION['flash_error'] = 'Чат не найден';
            header('Location: /admin/chats');
            exit;
        }

        // Получаем текущих участников
        $currentParticipants = $db->query("SELECT user_id FROM chat_participants WHERE chat_id = $id")->fetchAll(PDO::FETCH_COLUMN);

        $teachers = $db->query("SELECT id, name FROM users WHERE role = 'teacher'")->fetchAll(PDO::FETCH_ASSOC);
        $students = $db->query("SELECT id, name FROM users WHERE role = 'student'")->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/admin/editChats.php';
    }

    // Обновление чата
    public function update($id)
    {
        $user = Auth::user();
        if (!$user || $user['role'] !== 'admin') {
            header('Location: /chat');
            exit;
        }

        $db = Database::connect();

        // Получаем и валидируем данные
        $title = trim($_POST['title'] ?? '');
        $teacher_id = $_POST['teacher_id'] ?? null;
        $participants = $_POST['participants'] ?? [];

        // Валидация
        if (empty($title)) {
            $_SESSION['flash_error'] = 'Название чата не может быть пустым';
            header("Location: /admin/chats/edit/$id");
            exit;
        }

        if (empty($teacher_id)) {
            $_SESSION['flash_error'] = 'Необходимо выбрать преподавателя';
            header("Location: /admin/chats/edit/$id");
            exit;
        }

        // Преобразуем teacher_id в integer
        $teacher_id = (int)$teacher_id;

        if ($teacher_id <= 0) {
            $_SESSION['flash_error'] = 'Неверный преподаватель';
            header("Location: /admin/chats/edit/$id");
            exit;
        }

        // Фильтруем участников
        $participants = array_filter($participants, function ($p) {
            return !empty($p);
        });
        $participants = array_map('intval', $participants);

        if (empty($participants)) {
            $_SESSION['flash_error'] = 'Необходимо выбрать хотя бы одного участника';
            header("Location: /admin/chats/edit/$id");
            exit;
        }

        try {
            // Получаем старые данные для лога
            $old_chat = $db->query("SELECT title, teacher_id FROM chats WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
            $old_title = $old_chat['title'];
            $old_teacher_id = $old_chat['teacher_id'];
            $old_teacher_name = $db->query("SELECT name FROM users WHERE id = $old_teacher_id")->fetchColumn();
            $new_teacher_name = $db->query("SELECT name FROM users WHERE id = $teacher_id")->fetchColumn();

            $old_participants = $db->query("SELECT user_id FROM chat_participants WHERE chat_id = $id")->fetchAll(PDO::FETCH_COLUMN);

            $db->beginTransaction();

            // Обновляем данные чата
            $stmt = $db->prepare("UPDATE chats SET title = ?, teacher_id = ? WHERE id = ?");
            $stmt->execute([$title, $teacher_id, $id]);

            // Обновляем участников - сначала удаляем старых
            $db->prepare("DELETE FROM chat_participants WHERE chat_id = ?")->execute([$id]);

            // Добавляем новых участников
            $insert = $db->prepare("INSERT INTO chat_participants (chat_id, user_id) VALUES (?, ?)");
            foreach ($participants as $p) {
                if ($p > 0) {
                    $insert->execute([$id, $p]);
                }
            }

            // Добавляем преподавателя (если его еще нет в участниках)
            if (!in_array($teacher_id, $participants)) {
                $insert->execute([$id, $teacher_id]);
            }

            $db->commit();

            // Логируем обновление чата
            $changes = [];
            if ($old_title !== $title) {
                $changes[] = "название: '$old_title' → '$title'";
            }
            if ($old_teacher_id != $teacher_id) {
                $changes[] = "преподаватель: '$old_teacher_name' → '$new_teacher_name'";
            }
            if (count(array_diff($old_participants, $participants)) > 0 || count(array_diff($participants, $old_participants)) > 0) {
                $changes[] = "изменен список участников";
            }

            if (!empty($changes)) {
                Logger::log(
                    "Обновлен чат",
                    "ID: $id, Изменения: " . implode(', ', $changes)
                );
            }

            $_SESSION['flash_success'] = 'Чат успешно обновлен!';
            header('Location: /admin/chats');
            exit;
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Chat update error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Ошибка при обновлении чата: ' . $e->getMessage();
            header("Location: /admin/chats/edit/$id");
            exit;
        }
    }

    // Вспомогательный метод для логирования
    private function logChatAction($action, $details = '')
    {
        Logger::log($action, $details);
    }
}
