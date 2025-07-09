<?php

class Database {
    private static $pdo;

    public static function connect() {
        if (!self::$pdo) {
            self::$pdo = new PDO("pgsql:host=db;port=5432;dbname=edu_platform", "user", "password");
        }
        return self::$pdo;
    }
}
