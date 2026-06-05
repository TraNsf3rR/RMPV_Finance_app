<?php

declare(strict_types=1);

function config(string $key, mixed $default = null): mixed
{
    static $config = null;

    if ($config === null) {
        $config = require BASE_PATH . '/config/config.php';
    }

    $segments = explode('.', $key);
    $value = $config;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function url(string $path = ''): string
{
    $base = rtrim((string) config('base_url', '/index.php'), '/');
    $path = '/' . ltrim($path, '/');

    return $base . ($path === '/' ? '' : $path);
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function trim_input(mixed $value): mixed
{
    if (is_array($value)) {
        return array_map(static fn (mixed $item): mixed => trim_input($item), $value);
    }

    return is_string($value) ? trim($value) : $value;
}

function set_old_input(array $input): void
{
    $_SESSION['_old'] = trim_input($input);
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function set_errors(array $errors): void
{
    $_SESSION['_errors'] = $errors;
}

function error(string $key): ?string
{
    $errors = $_SESSION['_errors'] ?? [];
    return isset($errors[$key]) ? (string) $errors[$key] : null;
}

function old_text(string $key, mixed $default = ''): string
{
    return e(trim((string) old($key, $default)));
}

function sanitize_return_path(?string $path, string $default = '/transactions'): string
{
    $path = trim((string) $path);

    if ($path === '') {
        return $default;
    }

    $parts = parse_url($path);
    if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
        return $default;
    }

    $routePath = $parts['path'] ?? '';
    if ($routePath === '' || $routePath[0] !== '/') {
        return $default;
    }

    $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';

    return $routePath . $query;
}

function get_user_total_balance(int $userId): float
{
    $transactionModel = new \App\Models\Transaction();
    $summary = $transactionModel->getDashboardSummary($userId, []);
    return (float) ($summary['total_balance'] ?? 0);
}
