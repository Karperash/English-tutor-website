<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/'): string
{
    return $path === '' ? '/' : $path;
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function current_user(): ?array
{
    return Auth::user();
}

function flash(string $key): ?string
{
    return Flash::get($key);
}

function selected(string|int|null $a, string|int|null $b): string
{
    return (string)$a === (string)$b ? 'selected' : '';
}

function checked(bool $condition): string
{
    return $condition ? 'checked' : '';
}
