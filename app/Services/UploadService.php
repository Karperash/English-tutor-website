<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;

final class UploadService
{
    private const ALLOWED_MIME = [
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function save(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Файл не был загружен.');
        }

        $maxBytes = (int)Env::get('UPLOAD_MAX_MB', 10) * 1024 * 1024;
        $size = (int)($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            throw new \RuntimeException('Файл слишком большой. Максимум: ' . (int)Env::get('UPLOAD_MAX_MB', 10) . ' МБ.');
        }

        $tmp = (string)$file['tmp_name'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($tmp);
        if (!isset(self::ALLOWED_MIME[$mime])) {
            throw new \RuntimeException('Разрешены только DOC, DOCX, JPG, PNG и WEBP.');
        }

        $original = basename((string)($file['name'] ?? 'file'));
        $ext = self::ALLOWED_MIME[$mime];
        $stored = bin2hex(random_bytes(20)) . '.' . $ext;
        $dir = dirname(__DIR__, 2) . '/storage/homework';
        if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
            throw new \RuntimeException('Не удалось создать директорию для загрузок.');
        }

        $target = $dir . '/' . $stored;
        if (!move_uploaded_file($tmp, $target)) {
            throw new \RuntimeException('Не удалось сохранить файл.');
        }

        return [
            'original_name' => $original,
            'stored_name' => $stored,
            'mime_type' => $mime,
            'file_size' => $size,
        ];
    }

    public static function normalizeMultiple(array $files): array
    {
        if (!isset($files['name']) || !is_array($files['name'])) {
            return [];
        }

        $result = [];
        foreach ($files['name'] as $i => $name) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $result[] = [
                'name' => $name,
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$i] ?? 0,
            ];
        }
        return $result;
    }
}
