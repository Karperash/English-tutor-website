<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class TeacherNote
{
    public static function create(int $studentId, ?int $lessonId, string $content): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO teacher_notes (student_id, lesson_id, content) VALUES (?, ?, ?)'
        );
        $stmt->execute([$studentId, $lessonId, $content]);
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT n.*, l.starts_at AS lesson_starts_at
             FROM teacher_notes n
             LEFT JOIN lessons l ON l.id = n.lesson_id
             WHERE n.student_id = ? ORDER BY n.created_at DESC'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
}
