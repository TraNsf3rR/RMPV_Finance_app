<?php

declare(strict_types=1);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$publicDirectory = realpath(__DIR__);
$requestedFile = realpath(
    $publicDirectory . DIRECTORY_SEPARATOR . ltrim($requestPath, '/\\')
);

if (
    $requestPath !== '/'
    && $requestedFile !== false
    && $requestedFile !== realpath(__FILE__)
    && str_starts_with($requestedFile, $publicDirectory . DIRECTORY_SEPARATOR)
    && is_file($requestedFile)
) {
    return false;
}

require $publicDirectory . DIRECTORY_SEPARATOR . 'index.php';
