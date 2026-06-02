<?php

declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require BASE_PATH . '/app/Views/' . $view . '.php';
        $content = ob_get_clean();

        require BASE_PATH . '/app/Views/layouts/' . $layout . '.php';
    }

    protected function redirect(string $path): never
    {
        \redirect($path);
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    protected function old(string $key, mixed $default = null): mixed
    {
        return \old($key, $default);
    }

    protected function backWithErrors(string $path, array $errors, array $input = []): never
    {
        \set_errors($errors);
        \set_old_input($input);
        $this->redirect($path);
    }
}
