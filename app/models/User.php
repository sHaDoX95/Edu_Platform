<?php
require_once __DIR__ . '/../core/Database.php';

class User {
    public static function all() {
        $db = Database::connect();
        $stmt = $db->query("SELECT id, name, email, role FROM users ORDER BY id DESC");
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

    // Привязать студента к преподавателю
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
}