<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Notification;

final class View
{
    public static function render(string $template, array $data = [], string $layout = 'app'): void
    {
        $templateFile = dirname(__DIR__, 2) . '/views/' . $template . '.php';
        $layoutFile = dirname(__DIR__, 2) . '/views/layouts/' . $layout . '.php';

        if (!is_file($templateFile) || !is_file($layoutFile)) {
            throw new \RuntimeException('Шаблон не найден: ' . $template);
        }

        $user = Auth::user();
        $notificationCount = $user ? Notification::unreadCount((int)$user['id']) : 0;
        extract($data, EXTR_SKIP);

        ob_start();
        require $templateFile;
        $content = (string)ob_get_clean();

        require $layoutFile;
    }
}
