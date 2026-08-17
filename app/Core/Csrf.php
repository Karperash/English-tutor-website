<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['_csrf'];
    }

    public static function validate(?string $token): bool
    {
        return is_string($token)
            && isset($_SESSION['_csrf'])
            && hash_equals((string)$_SESSION['_csrf'], $token);
    }

    public static function guard(): void
    {
        if (!self::validate($_POST['_token'] ?? null)) {
            http_response_code(419);
            exit('Срок действия формы истёк. Обновите страницу и повторите действие.');
        }
    }
}
