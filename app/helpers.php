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
    $base = rtrim((string) config('base_url', ''), '/');
    $path = '/' . ltrim($path, '/');

    return $base . $path;
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

function unicode_character_count(string $value): ?int
{
    if (preg_match('//u', $value) !== 1) {
        return null;
    }

    $count = 0;
    $byteLength = strlen($value);

    for ($index = 0; $index < $byteLength; $index++) {
        if ((ord($value[$index]) & 0xC0) !== 0x80) {
            $count++;
        }
    }

    return $count;
}

function format_money(string $amount): string
{
    if (preg_match('/\A(-?)([0-9]+)(?:\.([0-9]{1,2}))?\z/', $amount, $parts) !== 1) {
        throw new \InvalidArgumentException('Money values must be decimal strings with up to two fractional digits.');
    }

    $whole = ltrim($parts[2], '0');
    $whole = $whole === '' ? '0' : $whole;
    $formattedWhole = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $whole);

    if ($formattedWhole === null) {
        throw new \RuntimeException('Unable to format monetary amount.');
    }

    $fraction = str_pad($parts[3] ?? '', 2, '0');
    $sign = $whole === '0' && $fraction === '00' ? '' : $parts[1];

    return $sign . $formattedWhole . '.' . $fraction;
}

function money_is_negative(string $amount): bool
{
    return str_starts_with($amount, '-')
        && preg_match('/[1-9]/', $amount) === 1;
}

function money_greater_than(string $amount, string $limit): bool
{
    $pattern = '/\A([0-9]+)(?:\.([0-9]{1,2}))?\z/';
    $amountParts = [];
    $limitParts = [];

    if (
        preg_match($pattern, $amount, $amountParts) !== 1
        || preg_match($pattern, $limit, $limitParts) !== 1
    ) {
        throw new \InvalidArgumentException('Money comparisons require non-negative decimal values with up to two fractional digits.');
    }

    $amountWhole = ltrim($amountParts[1], '0');
    $limitWhole = ltrim($limitParts[1], '0');
    $amountWhole = $amountWhole === '' ? '0' : $amountWhole;
    $limitWhole = $limitWhole === '' ? '0' : $limitWhole;

    if (strlen($amountWhole) !== strlen($limitWhole)) {
        return strlen($amountWhole) > strlen($limitWhole);
    }

    $wholeComparison = strcmp($amountWhole, $limitWhole);
    if ($wholeComparison !== 0) {
        return $wholeComparison > 0;
    }

    return strcmp(
        str_pad($amountParts[2] ?? '', 2, '0'),
        str_pad($limitParts[2] ?? '', 2, '0')
    ) > 0;
}

function csrf_token(): string
{
    $token = $_SESSION['_csrf_token'] ?? null;

    if (!is_string($token) || $token === '') {
        $token = bin2hex(random_bytes(32));
        $_SESSION['_csrf_token'] = $token;
    }

    return $token;
}

function csrf_token_is_valid(mixed $token): bool
{
    $sessionToken = $_SESSION['_csrf_token'] ?? null;

    return is_string($token)
        && is_string($sessionToken)
        && $sessionToken !== ''
        && hash_equals($sessionToken, $token);
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
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
    if (
        $routePath === ''
        || $routePath[0] !== '/'
        || str_contains($routePath, '\\')
    ) {
        return $default;
    }

    $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';

    return $routePath . $query;
}

function get_user_total_balance(int $userId): string
{
    $transactionModel = new \App\Models\Transaction();
    $summary = $transactionModel->getDashboardSummary($userId, []);
    return (string) ($summary['total_balance'] ?? '0.00');
}
