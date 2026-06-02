<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Category
{
    public function allForUser(int $userId, ?string $type = null): array
    {
        $sql = 'SELECT * FROM categories WHERE (user_id IS NULL OR user_id = :user_id)';
        $params = ['user_id' => $userId];

        if ($type !== null) {
            $sql .= ' AND type = :type';
            $params['type'] = $type;
        }

        $sql .= ' ORDER BY type, is_default DESC, name';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function findAccessibleById(int $userId, int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM categories WHERE id = :id AND (user_id IS NULL OR user_id = :user_id) LIMIT 1'
        );
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        $category = $stmt->fetch();
        return $category ?: null;
    }

    public function findCustomById(int $userId, int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM categories WHERE id = :id AND user_id = :user_id AND is_default = 0 LIMIT 1'
        );
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        $category = $stmt->fetch();
        return $category ?: null;
    }

    public function createCustom(int $userId, string $name, string $type): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO categories (user_id, name, type, is_default) VALUES (:user_id, :name, :type, 0)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'name' => $name,
            'type' => $type,
        ]);
    }

    public function updateCustom(int $userId, int $id, string $name, string $type): bool
    {
        $stmt = Database::connection()->prepare(
            'UPDATE categories
             SET name = :name, type = :type
             WHERE id = :id
               AND (user_id IS NULL OR user_id = :user_id)'
        );

        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'name' => $name,
            'type' => $type,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deleteCustom(int $userId, int $id): bool
    {
        $usageStmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM transactions WHERE category_id = :category_id'
        );
        $usageStmt->execute([
            'category_id' => $id,
        ]);

        if ((int) $usageStmt->fetchColumn() > 0) {
            return false;
        }

        $stmt = Database::connection()->prepare(
            'DELETE FROM categories
             WHERE id = :id
               AND (user_id IS NULL OR user_id = :user_id)'
        );
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }
}
