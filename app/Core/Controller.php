<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $template, array $data = [], string $layout = 'app'): void
    {
        View::render($template, $data, $layout);
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    protected function input(string $key, string $default = ''): string
    {
        return trim((string)($_POST[$key] ?? $default));
    }
}
