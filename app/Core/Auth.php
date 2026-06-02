<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function user(): ?array
    {
        $id = self::id();

        if ($id === null) {
            return null;
        }

        return (new User())->findById($id);
    }

    public static function login(int $userId): void
    {
        $_SESSION['user_id'] = $userId;
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        session_regenerate_id(true);
    }

    public static function requireGuest(): void
    {
        if (self::check()) {
            \redirect('/dashboard');
        }
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            \redirect('/login');
        }
    }
}
