<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;
    private static bool $initialized = false;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = (string) config('db.host');
        $port = (string) config('db.port', '3306');
        $database = (string) config('db.database');
        $username = (string) config('db.username');
        $password = (string) config('db.password');
        $charset = (string) config('db.charset', 'utf8mb4');

        try {
            $serverDsn = sprintf('mysql:host=%s;port=%s;charset=%s', $host, $port, $charset);
            $serverConnection = new PDO($serverDsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            self::ensureDatabaseExists($serverConnection, $database);

            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $database, $charset);
            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            self::initializeSchemaIfNeeded(self::$connection);
        } catch (PDOException $e) {
            http_response_code(500);
            echo 'Database connection failed: ' . $e->getMessage();
            exit;
        }

        return self::$connection;
    }

    private static function ensureDatabaseExists(PDO $connection, string $database): void
    {
        $safeDatabase = str_replace('`', '``', $database);
        $connection->exec("CREATE DATABASE IF NOT EXISTS `{$safeDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    private static function initializeSchemaIfNeeded(PDO $connection): void
    {
        if (self::$initialized) {
            return;
        }

        $connection->exec(
            'CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB'
        );

        $connection->exec(
            'CREATE TABLE IF NOT EXISTS categories (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NULL,
                name VARCHAR(120) NOT NULL,
                type ENUM("income", "expense") NOT NULL,
                is_default TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_categories_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB'
        );

        $connection->exec(
            'CREATE TABLE IF NOT EXISTS transactions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                category_id INT UNSIGNED NOT NULL,
                type ENUM("income", "expense") NOT NULL,
                amount DECIMAL(12,2) NOT NULL,
                description VARCHAR(255) NULL,
                transaction_date DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_transactions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_transactions_category FOREIGN KEY (category_id) REFERENCES categories(id)
            ) ENGINE=InnoDB'
        );

        self::ensureIndexExists($connection, 'transactions', 'idx_transactions_user_date', 'user_id, transaction_date');
        self::ensureIndexExists($connection, 'transactions', 'idx_transactions_user_type', 'user_id, type');
        self::ensureIndexExists($connection, 'categories', 'idx_categories_type', 'type');

        self::seedDefaultCategories($connection);
        self::$initialized = true;
    }

    private static function seedDefaultCategories(PDO $connection): void
    {
        $defaults = [
            ['Work', 'income'],
            ['Freelance', 'income'],
            ['Side-job', 'income'],
            ['Food', 'expense'],
            ['Bills', 'expense'],
            ['Transportations', 'expense'],
            ['Entertainment', 'expense'],
        ];

        $select = $connection->prepare(
            'SELECT id FROM categories WHERE user_id IS NULL AND name = :name AND type = :type LIMIT 1'
        );
        $insert = $connection->prepare(
            'INSERT INTO categories (user_id, name, type, is_default) VALUES (NULL, :name, :type, 1)'
        );

        foreach ($defaults as [$name, $type]) {
            $select->execute(['name' => $name, 'type' => $type]);
            $exists = $select->fetch();

            if ($exists) {
                continue;
            }

            $insert->execute(['name' => $name, 'type' => $type]);
        }
    }

    private static function ensureIndexExists(PDO $connection, string $table, string $index, string $columns): void
    {
        $stmt = $connection->prepare(
            'SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = :table_name AND index_name = :index_name'
        );
        $stmt->execute([
            'table_name' => $table,
            'index_name' => $index,
        ]);

        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }

        $safeTable = str_replace('`', '``', $table);
        $safeIndex = str_replace('`', '``', $index);
        $connection->exec("CREATE INDEX `{$safeIndex}` ON `{$safeTable}` ({$columns})");
    }
}
