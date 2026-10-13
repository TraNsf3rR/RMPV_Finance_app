<?php $appName = (string) config('app_name', 'Finance Tracker'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title . ' | ' . $appName) ?></title>
    <script src="<?= e(url('/assets/js/theme.js')) ?>"></script>
    <link rel="stylesheet" href="<?= e(url('/assets/css/style.css')) ?>">
</head>
<body>
    <div class="bg-glow glow-1"></div>
    <div class="bg-glow glow-2"></div>
    <div class="app-shell">
        <header class="topbar">
            <div class="topbar-brand">
                <a class="brand" href="<?= e(url('/dashboard')) ?>"><?= e($appName) ?></a>
                <button
                    class="btn theme-toggle"
                    type="button"
                    data-theme-toggle
                    aria-label="Switch to light theme"
                >Light theme</button>
            </div>
        </header>

        <main class="content">
            <section class="auth-wrap">
                <div class="card auth-card">
                    <p class="muted"><?= e((string) $statusCode) ?></p>
                    <h1><?= e($title) ?></h1>
                    <p class="muted"><?= e($message) ?></p>
                    <a class="btn btn-primary" href="<?= e(url('/dashboard')) ?>">Return to dashboard</a>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
