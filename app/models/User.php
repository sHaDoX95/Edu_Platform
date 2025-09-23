<?php
require_once __DIR__ . '/../core/Database.php';

class User {
    public static function all() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT id, name, email, role, blocked FROM users ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($id) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findByEmail($email) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($name, $email, $passwordHash, $role = 'student') {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role) RETURNING id");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $passwordHash,
            ':role' => $role
        ]);
        return $stmt->fetchColumn();
    }

    public static function update($id, $name, $email, $role = null, $passwordHash = null) {
        $db = Database::connect();

        $fields = ['name' => $name, 'email' => $email];
        $set = "name = :name, email = :email";
        if ($role !== null) {
            $set .= ", role = :role";
            $fields['role'] = $role;
        }
        if ($passwordHash !== null && $passwordHash !== '') {
            $set .= ", password = :password";
            $fields['password'] = $passwordHash;
        }
        $fields['id'] = $id;

        $stmt = $db->prepare("UPDATE users SET $set WHERE id = :id");
        return $stmt->execute($fields);
    }

    public static function delete($id) {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function assignStudentToTeacher($studentId, $teacherId) {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO teacher_student (teacher_id, student_id) VALUES (:t, :s) ON CONFLICT (teacher_id, student_id) DO NOTHING");
        return $stmt->execute(['t' => $teacherId, 's' => $studentId]);
    }

    public static function detachStudentFromTeacher($studentId, $teacherId) {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM teacher_student WHERE teacher_id = :t AND student_id = :s");
        return $stmt->execute(['t' => $teacherId, 's' => $studentId]);
    }

    public static function getTeachers() {
        $db = Database::connect();
        $stmt = $db->query("SELECT id, name FROM users WHERE role = 'teacher' ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getStudentsForTeacher($teacherId) {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT u.id, u.name, u.email
            FROM users u
            JOIN teacher_student ts ON ts.student_id = u.id
            WHERE ts.teacher_id = :tid
            ORDER BY u.name
        ");
        $stmt->execute(['tid' => $teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUnassignedStudents() {
        $db = Database::connect();
        $stmt = $db->query("
            SELECT u.id, u.name, u.email
            FROM users u
            WHERE u.role = 'student'
              AND u.id NOT IN (SELECT student_id FROM teacher_student)
            ORDER BY u.name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function updateRoleAndStatus($id, $role, $blocked) {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE users SET role = ?, blocked = ? WHERE id = ?");
        $stmt->execute([$role, $blocked, $id]);
    }

    public static function filter($role = null, $status = null, $q = '') {
        $db = Database::connect();
        $sql = "SELECT id, name, email, role, blocked FROM users WHERE 1=1";
        $params = [];

        if ($role) {
            $sql .= " AND role = :role";
            $params[':role'] = $role;
        }

        if ($status === 'active') {
            $sql .= " AND blocked = false";
        } elseif ($status === 'blocked') {
            $sql .= " AND blocked = true";
        }

        if ($q !== '') {
            $sql .= " AND (LOWER(name) LIKE :q OR LOWER(email) LIKE :q)";
            $params[':q'] = '%' . strtolower($q) . '%';
        }

        $sql .= " ORDER BY id";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}