<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Notification
{
    public static function create(int $userId, string $type, string $message, ?string $link = null): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO notifications (user_id, type, message, link) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $type, $message, $link]);
    }

    public static function forUser(int $userId, int $limit = 100): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . (int)$limit
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function unreadCount(int $userId): int
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public static function markAllRead(int $userId): void
    {
        $stmt = Database::connection()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
        $stmt->execute([$userId]);
    }
}
