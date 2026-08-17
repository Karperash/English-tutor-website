<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Homework
{
    public static function create(int $lessonId, int $studentId, string $title, string $description): int
    {
        $stmt = Database::connection()->prepare(
            "INSERT INTO homework (lesson_id, student_id, title, description, status)
             VALUES (?, ?, ?, ?, 'assigned')"
        );
        $stmt->execute([$lessonId, $studentId, $title, $description]);
        return (int)Database::connection()->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT h.*, u.first_name, u.last_name, l.starts_at AS lesson_starts_at
             FROM homework h
             JOIN users u ON u.id = h.student_id
             LEFT JOIN lessons l ON l.id = h.lesson_id
             WHERE h.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }


    public static function forLesson(int $lessonId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM homework WHERE lesson_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$lessonId]);
        return $stmt->fetchAll();
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT h.*, l.starts_at AS lesson_starts_at
             FROM homework h LEFT JOIN lessons l ON l.id = h.lesson_id
             WHERE h.student_id = ? ORDER BY h.created_at DESC'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public static function forTeacher(int $teacherId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT h.*, u.first_name, u.last_name, l.starts_at AS lesson_starts_at
             FROM homework h
             JOIN users u ON u.id = h.student_id
             JOIN lessons l ON l.id = h.lesson_id
             WHERE l.teacher_id = ? ORDER BY h.created_at DESC"
        );
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll();
    }

    public static function countSubmittedForTeacher(int $teacherId): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM homework h
             JOIN lessons l ON l.id = h.lesson_id
             WHERE l.teacher_id = ? AND h.status = 'submitted'"
        );
        $stmt->execute([$teacherId]);
        return (int)$stmt->fetchColumn();
    }

    public static function submit(int $id, int $studentId, string $comment): void
    {
        $stmt = Database::connection()->prepare(
            "UPDATE homework SET student_comment = ?, status = 'submitted', submitted_at = NOW()
             WHERE id = ? AND student_id = ?"
        );
        $stmt->execute([$comment, $id, $studentId]);
    }

    public static function check(int $id, string $feedback): void
    {
        $stmt = Database::connection()->prepare(
            "UPDATE homework SET teacher_feedback = ?, status = 'checked', checked_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$feedback, $id]);
    }

    public static function addFile(int $homeworkId, string $uploadedBy, array $file): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO homework_files (homework_id, uploaded_by, original_name, stored_name, mime_type, file_size)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $homeworkId,
            $uploadedBy,
            $file['original_name'],
            $file['stored_name'],
            $file['mime_type'],
            $file['file_size'],
        ]);
        return (int)Database::connection()->lastInsertId();
    }

    public static function files(int $homeworkId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM homework_files WHERE homework_id = ? ORDER BY created_at'
        );
        $stmt->execute([$homeworkId]);
        return $stmt->fetchAll();
    }

    public static function file(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT f.*, h.student_id, l.teacher_id
             FROM homework_files f
             JOIN homework h ON h.id = f.homework_id
             JOIN lessons l ON l.id = h.lesson_id
             WHERE f.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
