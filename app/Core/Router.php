<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$this->normalizePath($path)] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$this->normalizePath($path)] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = $this->normalizeRequestPath($uri);

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            $params = array_filter($matches, static fn ($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
            $this->invokeHandler($handler, $params);
            return;
        }

        http_response_code(404);
        echo '404 Not Found';
    }

    private function invokeHandler(callable|array $handler, array $params): void
    {
        if (is_array($handler)) {
            [$controllerClass, $method] = $handler;
            $controller = new $controllerClass();
            $controller->$method(...array_values($params));
            return;
        }

        $handler(...array_values($params));
    }

    private function normalizePath(string $path): string
    {
        $trimmed = '/' . trim($path, '/');
        if ($trimmed === '//') {
            return '/';
        }

        $normalized = rtrim($trimmed, '/');
        return $normalized !== '' ? $normalized : '/';
    }

    private function normalizeRequestPath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        if (str_starts_with($path, '/index.php')) {
            $path = substr($path, strlen('/index.php')) ?: '/';
        }

        return $this->normalizePath($path);
    }
}
