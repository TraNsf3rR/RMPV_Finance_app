<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class User
{
    public function create(string $name, string $email, string $passwordHash): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT id, name, email, created_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function updatePasswordByEmail(string $email, string $passwordHash): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE users SET password_hash = :password_hash WHERE email = :email'
        );
        $stmt->execute([
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);

        return $stmt->rowCount() > 0;
    }
}
