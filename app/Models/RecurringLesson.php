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


    public static function create(
        int $teacherId,
        int $studentId,
        int $weekday,
        string $time
    ): void {

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
            $time
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
            $studentId
        ]);
    }

}