<?php

use App\Core\Auth;
use App\Models\Transaction;

$user = Auth::user();
$flash = $_SESSION['flash'] ?? null;
$transactionsNavPath = sanitize_return_path($transactionsNavPath ?? '/transactions', '/transactions');

// Calculate total balance for all transactions
$totalBalance = 0;
if ($user) {
    $transactionModel = new Transaction();
    $summary = $transactionModel->getDashboardSummary((int) $user['id'], []);
    $totalBalance = $summary['total_balance'] ?? 0;
}

unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($title ?? 'Finance Tracker') . ' | ' . config('app_name', 'Finance Tracker')) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="bg-glow glow-1"></div>
    <div class="bg-glow glow-2"></div>
    <div class="app-shell">
        <header class="topbar">
            <a class="brand" href="<?= url('/dashboard') ?>">Finance Tracker</a>

            <?php if ($user): ?>
                <nav class="nav-links">
                    <a href="<?= url('/dashboard') ?>">Dashboard</a>
                    <a href="<?= url($transactionsNavPath) ?>">Transactions</a>
                    <a href="<?= url('/categories') ?>">Categories</a>
                </nav>
                <div class="balance-display <?= $totalBalance >= 0 ? 'text-income' : 'text-expense' ?>">
                    <span>Total Balance:</span>
                    <strong><?= $totalBalance >= 0 ? '' : '-' ?>€<?= number_format(abs($totalBalance), 2) ?></strong>
                </div>
                <div class="user-block">
                    <span><?= e($user['name']) ?></span>
                    <form method="POST" action="<?= url('/logout') ?>">
                        <button class="btn btn-danger" type="submit">Logout</button>
                    </form>
                </div>
            <?php endif; ?>
        </header>

        <main class="content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</body>
</html>
<?php unset($_SESSION['_errors'], $_SESSION['_old']); ?>
