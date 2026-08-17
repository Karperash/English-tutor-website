<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

final class NotificationService
{
    public static function teacherId(): int
    {
        $teacher = User::findByLogin('Katy1009');
        if (!$teacher) {
            throw new \RuntimeException('Аккаунт преподавателя не найден.');
        }
        return (int)$teacher['id'];
    }

    public static function notifyTeacher(string $type, string $message, ?string $link = null): void
    {
        Notification::create(self::teacherId(), $type, $message, $link);
    }

    public static function notifyStudent(int $studentId, string $type, string $message, ?string $link = null): void
    {
        Notification::create($studentId, $type, $message, $link);
    }
}
