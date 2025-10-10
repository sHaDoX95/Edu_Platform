<?php

class Database {
    private static $pdo;

    public static function connect() {
        if (!self::$pdo) {
            self::$pdo = new PDO("pgsql:host=db;port=5432;dbname=edu_platform", "user", "password", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            self::$pdo->exec("SET TIME ZONE 'Europe/Moscow';");
        }

        return self::$pdo;
    }
}