<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    public static function findByLogin(string $login): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE login = ? LIMIT 1');
        $stmt->execute([$login]);
        return $stmt->fetch() ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function students(): array
    {
        return Database::connection()->query(
            "SELECT * FROM users WHERE role = 'student' AND deleted_at IS NULL ORDER BY last_name, first_name"
        )->fetchAll();
    }

    public static function createStudent(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO users (role, first_name, last_name, login, password_hash, phone, parent_name, parent_phone)
             VALUES (\'student\', :first_name, :last_name, :login, :password_hash, :phone, :parent_name, :parent_phone)'
        );
        $stmt->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'login' => $data['login'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'phone' => $data['phone'],
            'parent_name' => $data['parent_name'],
            'parent_phone' => $data['parent_phone'],
        ]);
        return (int)Database::connection()->lastInsertId();
    }

    public static function updateStudent(int $id, array $data): void
    {
        $params = [
            'id' => $id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'login' => $data['login'],
            'phone' => $data['phone'],
            'parent_name' => $data['parent_name'],
            'parent_phone' => $data['parent_phone'],
        ];

        $passwordSql = '';
        if (!empty($data['password'])) {
            $passwordSql = ', password_hash = :password_hash';
            $params['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $stmt = Database::connection()->prepare(
            "UPDATE users SET first_name = :first_name, last_name = :last_name, login = :login,
             phone = :phone, parent_name = :parent_name, parent_phone = :parent_phone{$passwordSql}
             WHERE id = :id AND role = 'student' AND deleted_at IS NULL"
        );
        $stmt->execute($params);
    }

    public static function softDeleteStudent(int $id): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $cancel = $pdo->prepare(
                "UPDATE lessons
                 SET status = 'teacher_cancelled', schedule_slot_id = NULL, cancelled_at = NOW()
                 WHERE student_id = ? AND status = 'scheduled'"
            );
            $cancel->execute([$id]);

            $stmt = $pdo->prepare(
                "UPDATE users SET deleted_at = NOW() WHERE id = ? AND role = 'student' AND deleted_at IS NULL"
            );
            $stmt->execute([$id]);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function loginExists(string $login, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE login = ?';
        $params = [$login];
        if ($exceptId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }
}
