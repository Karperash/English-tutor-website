<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Homework;

final class FileController
{
    public function download(string $id): void
    {
        $user = Auth::requireLogin();
        $file = Homework::file((int)$id);
        if (!$file) {
            http_response_code(404);
            exit('Файл не найден.');
        }

        $allowed = $user['role'] === 'teacher' && (int)$file['teacher_id'] === (int)$user['id'];
        $allowed = $allowed || ($user['role'] === 'student' && (int)$file['student_id'] === (int)$user['id']);
        if (!$allowed) {
            http_response_code(403);
            exit('Доступ запрещён.');
        }

        $path = dirname(__DIR__, 2) . '/storage/homework/' . basename((string)$file['stored_name']);
        if (!is_file($path)) {
            http_response_code(404);
            exit('Файл отсутствует на диске.');
        }

        header('Content-Type: ' . $file['mime_type']);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: attachment; filename="download"; filename*=UTF-8\'\'' . rawurlencode((string)$file['original_name']));
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }
}
