<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = (string)Env::get('DB_HOST', '127.0.0.1');
        $port = (string)Env::get('DB_PORT', '3306');
        $name = (string)Env::get('DB_NAME', 'english_tutor');
        $user = (string)Env::get('DB_USER', 'root');
        $pass = (string)Env::get('DB_PASSWORD', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            $timezone = new \DateTimeZone((string)Env::get('APP_TIMEZONE', 'Europe/Berlin'));
            $offset = (new \DateTimeImmutable('now', $timezone))->format('P');
            if (preg_match('/^[+-]\d{2}:\d{2}$/', $offset)) {
                self::$pdo->exec("SET time_zone = " . self::$pdo->quote($offset));
            }
        } catch (PDOException $e) {
            http_response_code(500);
            $debug = Env::bool('APP_DEBUG', false);
            exit($debug ? 'Ошибка подключения к базе данных: ' . e($e->getMessage()) : 'Ошибка подключения к базе данных.');
        }

        return self::$pdo;
    }
}
