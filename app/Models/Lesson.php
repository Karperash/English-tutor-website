<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;

final class Lesson
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT l.*, u.first_name, u.last_name, u.phone, u.parent_name, u.parent_phone
             FROM lessons l
             JOIN users u ON u.id = l.student_id
             WHERE l.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT l.* FROM lessons l
             WHERE l.student_id = ?
             ORDER BY l.starts_at DESC
             LIMIT 200"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public static function upcomingForStudent(int $studentId, int $limit = 10): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT l.* FROM lessons l
             WHERE l.student_id = ? AND l.status = 'scheduled' AND l.starts_at >= NOW()
             ORDER BY l.starts_at ASC LIMIT " . (int)$limit
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public static function upcomingForTeacher(int $teacherId, int $limit = 20): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT l.*, u.first_name, u.last_name
             FROM lessons l
             JOIN users u ON u.id = l.student_id
             WHERE l.teacher_id = ? AND l.status = 'scheduled' AND l.starts_at >= NOW()
             ORDER BY l.starts_at ASC LIMIT " . (int)$limit
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }
public static function scheduleForTeacher(int $teacherId, int $days = 60): array
{
    $stmt = Database::connection()->prepare(
        "SELECT l.*, u.first_name, u.last_name
         FROM lessons l
         JOIN users u ON u.id = l.student_id
         WHERE l.teacher_id = ?
           AND l.status = 'scheduled'
           AND l.starts_at >= NOW() - INTERVAL 1 DAY
           AND l.starts_at < NOW() + INTERVAL " . (int)$days . " DAY
         ORDER BY l.starts_at ASC"
    );

    $stmt->execute([$teacherId]);

    return $stmt->fetchAll();
}
    public static function todayForTeacher(int $teacherId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT l.*, u.first_name, u.last_name
             FROM lessons l
             JOIN users u ON u.id = l.student_id
             WHERE l.teacher_id = ? AND l.status = 'scheduled' AND DATE(l.starts_at) = CURDATE()
             ORDER BY l.starts_at"
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    public static function unpaidCountForTeacher(int $teacherId): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM lessons WHERE teacher_id = ? AND payment_status = 'unpaid' AND status IN ('scheduled','completed')"
        );
        $stmt->execute([$teacherId]);
        return (int)$stmt->fetchColumn();
    }
public static function createByTeacher(
    int $teacherId,
    int $studentId,
    string $startsAt
): array {
    $pdo = Database::connection();

    $starts = new DateTimeImmutable($startsAt);
    $ends = $starts->modify('+60 minutes');

    $studentStmt = $pdo->prepare(
        "SELECT id
         FROM users
         WHERE id = ?
           AND role = 'student'
           AND deleted_at IS NULL
         LIMIT 1"
    );
    $studentStmt->execute([$studentId]);

    if (!$studentStmt->fetchColumn()) {
        throw new \RuntimeException('Ученик не найден.');
    }

    $check = $pdo->prepare(
        "SELECT COUNT(*)
         FROM lessons
         WHERE teacher_id = ?
           AND status = 'scheduled'
           AND starts_at < ?
           AND ends_at > ?"
    );

    $check->execute([
        $teacherId,
        $ends->format('Y-m-d H:i:s'),
        $starts->format('Y-m-d H:i:s'),
    ]);

    if ((int)$check->fetchColumn() > 0) {
        throw new \RuntimeException(
            'На это время уже назначено другое занятие.'
        );
    }

    $stmt = $pdo->prepare(
        "INSERT INTO lessons
        (
            student_id,
            teacher_id,
            schedule_slot_id,
            starts_at,
            ends_at,
            status,
            payment_status,
            booked_at
        )
        VALUES (?, ?, NULL, ?, ?, 'scheduled', 'unpaid', NOW())"
    );

    $stmt->execute([
        $studentId,
        $teacherId,
        $starts->format('Y-m-d H:i:s'),
        $ends->format('Y-m-d H:i:s'),
    ]);

    $lessonId = (int)$pdo->lastInsertId();

    return self::find($lessonId)
        ?? throw new \RuntimeException('Не удалось создать занятие.');
}
    public static function book(int $studentId, int $slotId): array
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $studentLock = $pdo->prepare(
                "SELECT id FROM users WHERE id = ? AND role = 'student' AND deleted_at IS NULL FOR UPDATE"
            );
            $studentLock->execute([$studentId]);
            if (!$studentLock->fetchColumn()) {
                throw new \RuntimeException('Аккаунт ученика не найден.');
            }

            $stmt = $pdo->prepare(
                "SELECT s.*
                 FROM schedule_slots s
                 LEFT JOIN lessons l ON l.schedule_slot_id = s.id
                 WHERE s.id = ? AND s.status = 'available' AND l.id IS NULL
                 FOR UPDATE"
            );
            $stmt->execute([$slotId]);
            $slot = $stmt->fetch();
            if (!$slot) {
                throw new \RuntimeException('Это время уже занято или недоступно.');
            }

            $startsAt = new DateTimeImmutable((string)$slot['starts_at']);
            $minTime = new DateTimeImmutable('+12 hours');
            if ($startsAt < $minTime) {
                throw new \RuntimeException('Записаться можно не позднее чем за 12 часов до занятия.');
            }

            $weekStart = $startsAt->modify('monday this week')->setTime(0, 0, 0);
            $weekEnd = $weekStart->modify('+7 days');
            $countStmt = $pdo->prepare(
                "SELECT COUNT(*) FROM lessons
                 WHERE student_id = ? AND status = 'scheduled'
                   AND starts_at >= ? AND starts_at < ?"
            );
            $countStmt->execute([
                $studentId,
                $weekStart->format('Y-m-d H:i:s'),
                $weekEnd->format('Y-m-d H:i:s'),
            ]);
            if ((int)$countStmt->fetchColumn() >= 3) {
                throw new \RuntimeException('На одну неделю можно записаться максимум на 3 занятия.');
            }

            $insert = $pdo->prepare(
                "INSERT INTO lessons
                 (student_id, teacher_id, schedule_slot_id, starts_at, ends_at, status, payment_status, booked_at)
                 VALUES (?, ?, ?, ?, ?, 'scheduled', 'unpaid', NOW())"
            );
            $insert->execute([
                $studentId,
                (int)$slot['teacher_id'],
                $slotId,
                $slot['starts_at'],
                $slot['ends_at'],
            ]);

            $lessonId = (int)$pdo->lastInsertId();
            $pdo->commit();
            return self::find($lessonId) ?? throw new \RuntimeException('Не удалось загрузить созданное занятие.');
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function cancelByStudent(int $lessonId, int $studentId): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT * FROM lessons WHERE id = ? AND student_id = ? FOR UPDATE');
            $stmt->execute([$lessonId, $studentId]);
            $lesson = $stmt->fetch();
            if (!$lesson || $lesson['status'] !== 'scheduled') {
                throw new \RuntimeException('Занятие не найдено или уже недоступно для отмены.');
            }

            $startsAt = new DateTimeImmutable((string)$lesson['starts_at']);
            if ($startsAt <= new DateTimeImmutable('+3 hours')) {
                throw new \RuntimeException('Отменить занятие можно не позднее чем за 3 часа до начала.');
            }

            self::writeHistory($pdo, $lessonId, 'student_cancelled', (string)$lesson['starts_at'], null, 'Отменено учеником');
            $update = $pdo->prepare(
                "UPDATE lessons SET status = 'student_cancelled', schedule_slot_id = NULL, cancelled_at = NOW() WHERE id = ?"
            );
            $update->execute([$lessonId]);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function cancelByTeacher(int $lessonId, int $teacherId): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT * FROM lessons WHERE id = ? AND teacher_id = ? FOR UPDATE');
            $stmt->execute([$lessonId, $teacherId]);
            $lesson = $stmt->fetch();
            if (!$lesson || $lesson['status'] !== 'scheduled') {
                throw new \RuntimeException('Занятие уже отменено или завершено.');
            }

            self::writeHistory($pdo, $lessonId, 'teacher_cancelled', (string)$lesson['starts_at'], null, 'Отменено преподавателем');
            $update = $pdo->prepare(
                "UPDATE lessons SET status = 'teacher_cancelled', schedule_slot_id = NULL, cancelled_at = NOW() WHERE id = ?"
            );
            $update->execute([$lessonId]);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function moveByTeacher(int $lessonId, int $teacherId, int $newSlotId): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $lessonStmt = $pdo->prepare('SELECT * FROM lessons WHERE id = ? AND teacher_id = ? FOR UPDATE');
            $lessonStmt->execute([$lessonId, $teacherId]);
            $lesson = $lessonStmt->fetch();
            if (!$lesson || $lesson['status'] !== 'scheduled') {
                throw new \RuntimeException('Перенести можно только запланированное занятие.');
            }

            $slotStmt = $pdo->prepare(
                "SELECT s.* FROM schedule_slots s
                 LEFT JOIN lessons l ON l.schedule_slot_id = s.id
                 WHERE s.id = ? AND s.teacher_id = ? AND s.status = 'available' AND s.starts_at >= NOW() AND l.id IS NULL FOR UPDATE"
            );
            $slotStmt->execute([$newSlotId, $teacherId]);
            $slot = $slotStmt->fetch();
            if (!$slot) {
                throw new \RuntimeException('Новое время уже занято или не существует.');
            }

            self::writeHistory(
                $pdo,
                $lessonId,
                'rescheduled',
                (string)$lesson['starts_at'],
                (string)$slot['starts_at'],
                'Занятие перенесено преподавателем'
            );

            $update = $pdo->prepare(
                'UPDATE lessons SET schedule_slot_id = ?, starts_at = ?, ends_at = ? WHERE id = ?'
            );
            $update->execute([$newSlotId, $slot['starts_at'], $slot['ends_at'], $lessonId]);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function updateTeacherFields(int $lessonId, int $teacherId, array $data): void
    {
        $stmt = Database::connection()->prepare(
            "UPDATE lessons
             SET meeting_url = ?, meeting_comment = ?, payment_status = ?, status = ?
             WHERE id = ? AND teacher_id = ?"
        );
        $stmt->execute([
            $data['meeting_url'] ?: null,
            $data['meeting_comment'] ?: null,
            $data['payment_status'],
            $data['status'],
            $lessonId,
            $teacherId,
        ]);
    }

    public static function history(int $lessonId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM lesson_history WHERE lesson_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$lessonId]);
        return $stmt->fetchAll();
    }

    private static function writeHistory(PDO $pdo, int $lessonId, string $action, ?string $oldStartsAt, ?string $newStartsAt, string $comment): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO lesson_history (lesson_id, action, old_starts_at, new_starts_at, comment) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$lessonId, $action, $oldStartsAt, $newStartsAt, $comment]);
    }
}
