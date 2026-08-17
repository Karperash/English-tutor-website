<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ScheduleSlot
{
    public static function create(int $teacherId, string $startsAt): int
    {
        $endsAt = date('Y-m-d H:i:s', strtotime($startsAt . ' +60 minutes'));
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $teacherLock = $pdo->prepare(
                "SELECT id FROM users WHERE id = ? AND role = 'teacher' FOR UPDATE"
            );
            $teacherLock->execute([$teacherId]);
            if (!$teacherLock->fetchColumn()) {
                throw new \RuntimeException('Аккаунт преподавателя не найден.');
            }

            $check = $pdo->prepare(
                'SELECT COUNT(*) FROM schedule_slots
                 WHERE teacher_id = ? AND starts_at < ? AND ends_at > ?'
            );
            $check->execute([$teacherId, $endsAt, $startsAt]);
            if ((int)$check->fetchColumn() > 0) {
                throw new \RuntimeException('Этот час пересекается с уже созданным слотом.');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO schedule_slots (teacher_id, starts_at, ends_at, status)
                 VALUES (?, ?, ?, 'available')"
            );
            $stmt->execute([$teacherId, $startsAt, $endsAt]);
            $id = (int)$pdo->lastInsertId();
            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT s.*, l.id AS lesson_id, l.student_id, u.first_name, u.last_name
             FROM schedule_slots s
             LEFT JOIN lessons l ON l.schedule_slot_id = s.id
             LEFT JOIN users u ON u.id = l.student_id
             WHERE s.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function deleteIfFree(int $id, int $teacherId): bool
    {
        $stmt = Database::connection()->prepare(
            "DELETE s FROM schedule_slots s
             LEFT JOIN lessons l ON l.schedule_slot_id = s.id
             WHERE s.id = ? AND s.teacher_id = ? AND l.id IS NULL"
        );
        $stmt->execute([$id, $teacherId]);
        return $stmt->rowCount() > 0;
    }

    public static function teacherUpcoming(int $teacherId, int $days = 60): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT s.*, l.id AS lesson_id, l.student_id, u.first_name, u.last_name,
                    (SELECT COUNT(*) FROM student_availability sa WHERE sa.schedule_slot_id = s.id) AS availability_count
             FROM schedule_slots s
             LEFT JOIN lessons l ON l.schedule_slot_id = s.id
             LEFT JOIN users u ON u.id = l.student_id
             WHERE s.teacher_id = ?
               AND s.starts_at >= NOW() - INTERVAL 1 DAY
               AND s.starts_at < NOW() + INTERVAL " . (int)$days . " DAY
             ORDER BY s.starts_at"
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    public static function studentAvailable(int $studentId, int $days = 30): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT s.*, CASE WHEN sa.id IS NULL THEN 0 ELSE 1 END AS flagged
             FROM schedule_slots s
             LEFT JOIN lessons l ON l.schedule_slot_id = s.id
             LEFT JOIN student_availability sa ON sa.schedule_slot_id = s.id AND sa.student_id = ?
             WHERE s.status = 'available'
               AND s.starts_at >= NOW()
               AND s.starts_at < NOW() + INTERVAL " . (int)$days . " DAY
               AND l.id IS NULL
             ORDER BY s.starts_at"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public static function freeForTeacher(int $teacherId, ?int $excludeLessonId = null): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT s.*
             FROM schedule_slots s
             LEFT JOIN lessons l ON l.schedule_slot_id = s.id
             WHERE s.teacher_id = ? AND s.status = 'available' AND s.starts_at >= NOW() AND l.id IS NULL
             ORDER BY s.starts_at
             LIMIT 200"
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    public static function availabilityNames(int $slotId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT u.id, u.first_name, u.last_name
             FROM student_availability sa
             JOIN users u ON u.id = sa.student_id
             WHERE sa.schedule_slot_id = ? AND u.deleted_at IS NULL
             ORDER BY u.last_name, u.first_name"
        );
        $stmt->execute([$slotId]);
        return $stmt->fetchAll();
    }
}
