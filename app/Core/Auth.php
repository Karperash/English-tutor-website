<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private static ?array $cached = null;

    public static function attempt(string $login, string $password): bool
    {
        $user = User::findByLogin($login);
        if (!$user || !empty($user['deleted_at'])) {
            return false;
        }

        if (!password_verify($password, (string)$user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        self::$cached = $user;
        return true;
    }

    public static function user(): ?array
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        $id = $_SESSION['user_id'] ?? null;
        if (!$id) {
            return null;
        }

        $user = User::findById((int)$id);
        if (!$user || !empty($user['deleted_at'])) {
            self::logout();
            return null;
        }

        return self::$cached = $user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        self::$cached = null;
        session_regenerate_id(true);
    }

    public static function requireLogin(): array
    {
        $user = self::user();
        if (!$user) {
            Flash::set('error', 'Сначала войдите в аккаунт.');
            header('Location: /login');
            exit;
        }
        return $user;
    }

    public static function requireRole(string $role): array
    {
        $user = self::requireLogin();
        if (($user['role'] ?? null) !== $role) {
            http_response_code(403);
            exit('Доступ запрещён.');
        }
        return $user;
    }
}
