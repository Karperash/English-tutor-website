<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class RecurringLesson
{
    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT *
             FROM recurring_lessons
             WHERE student_id = ?
               AND active = 1
             ORDER BY weekday, start_time"
        );

        $stmt->execute([$studentId]);

        return $stmt->fetchAll();
    }

    public static function forTeacher(int $teacherId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT r.*, u.first_name, u.last_name
             FROM recurring_lessons r
             JOIN users u ON u.id = r.student_id
             WHERE r.teacher_id = ?
               AND r.active = 1
               AND u.deleted_at IS NULL
             ORDER BY r.weekday, r.start_time"
        );

        $stmt->execute([$teacherId]);

        return $stmt->fetchAll();
    }

    public static function create(
        int $teacherId,
        int $studentId,
        int $weekday,
        string $time
    ): void {
        $check = Database::connection()->prepare(
            "SELECT COUNT(*)
             FROM recurring_lessons
             WHERE teacher_id = ?
               AND student_id = ?
               AND weekday = ?
               AND start_time = ?
               AND active = 1"
        );

        $check->execute([
            $teacherId,
            $studentId,
            $weekday,
            $time . ':00',
        ]);

        if ((int)$check->fetchColumn() > 0) {
            throw new \RuntimeException(
                'Такое постоянное занятие уже добавлено.'
            );
        }

        $stmt = Database::connection()->prepare(
            "INSERT INTO recurring_lessons
            (
                teacher_id,
                student_id,
                weekday,
                start_time
            )
            VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            $teacherId,
            $studentId,
            $weekday,
            $time,
        ]);
    }

    public static function delete(
        int $id,
        int $studentId
    ): void {
        $stmt = Database::connection()->prepare(
            "DELETE FROM recurring_lessons
             WHERE id = ?
               AND student_id = ?"
        );

        $stmt->execute([
            $id,
            $studentId,
        ]);
    }

    public static function upcomingForTeacher(
        int $teacherId,
        int $days = 60
    ): array {
        $recurring = self::forTeacher($teacherId);

        $result = [];

        $start = new \DateTimeImmutable('today');
        $end = $start->modify('+' . $days . ' days');

        foreach ($recurring as $rule) {
            $weekday = (int)$rule['weekday'];

            $date = $start;

            while ((int)$date->format('N') !== $weekday) {
                $date = $date->modify('+1 day');
            }

            while ($date < $end) {
                $startAt = new \DateTimeImmutable(
                    $date->format('Y-m-d') . ' ' . $rule['start_time']
                );

                $endAt = $startAt->modify(
                    '+' . (int)$rule['duration_minutes'] . ' minutes'
                );

                if ($startAt >= new \DateTimeImmutable('now')) {
                    $result[] = [
                        'id' => 'recurring_' . $rule['id'] . '_' . $date->format('Ymd'),
                        'recurring_id' => (int)$rule['id'],
                        'student_id' => (int)$rule['student_id'],
                        'first_name' => $rule['first_name'],
                        'last_name' => $rule['last_name'],
                        'starts_at' => $startAt->format('Y-m-d H:i:s'),
                        'ends_at' => $endAt->format('Y-m-d H:i:s'),
                        'is_recurring' => true,
                    ];
                }

                $date = $date->modify('+7 days');
            }
        }

        usort(
            $result,
            fn(array $a, array $b) =>
                strcmp($a['starts_at'], $b['starts_at'])
        );

        return $result;
    }
}