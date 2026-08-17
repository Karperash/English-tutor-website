<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class StudentAvailability
{
    public static function toggle(int $studentId, int $slotId): bool
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id FROM student_availability WHERE student_id = ? AND schedule_slot_id = ?');
        $stmt->execute([$studentId, $slotId]);
        $id = $stmt->fetchColumn();

        if ($id) {
            $delete = $pdo->prepare('DELETE FROM student_availability WHERE id = ?');
            $delete->execute([$id]);
            return false;
        }

        $insert = $pdo->prepare('INSERT INTO student_availability (student_id, schedule_slot_id) VALUES (?, ?)');
        $insert->execute([$studentId, $slotId]);
        return true;
    }
}
