<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * PDO connection. Every query of the app uses prepared statements
 * (the previous version concatenated user input into SQL).
 *
 * DB_DSN examples:
 *   mysql:host=127.0.0.1;port=3306;dbname=mf_buy;charset=utf8mb4
 *   sqlite:database/shop.sqlite      (zero-setup demo)
 */
final class Database
{
    public static function connect(string $dsn, ?string $user = null, ?string $password = null): PDO
    {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $pdo->exec('PRAGMA foreign_keys = ON');
        }
        return $pdo;
    }

    public static function fromConfig(string $root): PDO
    {
        $dsn = Config::get('DB_DSN', 'sqlite:' . $root . '/database/shop.sqlite');
        // Relative SQLite paths are resolved from the project root.
        if (str_starts_with($dsn, 'sqlite:') && !str_starts_with($dsn, 'sqlite::memory:')) {
            $path = substr($dsn, 7);
            if (!preg_match('#^([A-Za-z]:)?[\\\\/]#', $path)) {
                $dsn = 'sqlite:' . $root . '/' . $path;
            }
        }
        return self::connect($dsn, Config::get('DB_USER'), Config::get('DB_PASSWORD'));
    }
}
