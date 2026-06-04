<?php $backTo = sanitize_return_path($backTo ?? '/dashboard'); ?>

<section class="page-head">
    <div>
        <h1>Transactions</h1>
        <?php if ($backTo !== '/dashboard'): ?>
            <p class="muted">Opened from <?= e($backTo) ?></p>
        <?php endif; ?>
    </div>
    <div class="page-head-actions">
        <a class="btn" href="<?= url($backTo) ?>">Back</a>
        <a class="btn btn-primary" href="<?= url('/transactions/create?back_to=' . rawurlencode($backTo)) ?>">+ Add Transaction</a>
    </div>
</section>

<section class="card">
    <h2>Search & Filters</h2>
    <form class="filter-grid" method="GET" action="<?= url('/transactions') ?>">
        <input type="hidden" name="back_to" value="<?= e($backTo) ?>">
        <div>
            <label>From Date</label>
            <input type="date" name="from_date" value="<?= e(trim((string) $filters['from_date'])) ?>">
        </div>

        <div>
            <label>To Date</label>
            <input type="date" name="to_date" value="<?= e(trim((string) $filters['to_date'])) ?>">
        </div>

        <div>
            <label>Type</label>
            <select name="type">
                <option value="">All</option>
                <option value="income" <?= $filters['type'] === 'income' ? 'selected' : '' ?>>Income</option>
                <option value="expense" <?= $filters['type'] === 'expense' ? 'selected' : '' ?>>Expense</option>
            </select>
        </div>

        <div>
            <label>Category</label>
            <select name="category_id">
                <option value="">All</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= (string) $category['id'] === (string) $filters['category_id'] ? 'selected' : '' ?>>
                        <?= e(ucfirst($category['type']) . ' - ' . $category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Search Text</label>
            <input type="text" name="search" placeholder="Description or category" value="<?= e(trim((string) $filters['search'])) ?>">
        </div>

        <div class="filter-actions">
            <button class="btn btn-primary" type="submit">Apply</button>
            <a class="btn" href="<?= url('/transactions?back_to=' . rawurlencode($backTo)) ?>">Reset</a>
        </div>
    </form>
</section>

<section class="stats-grid">
    <article class="stat-card">
        <h3>Balance</h3>
        <p class="stat-value <?= $totalBalance >= 0 ? 'text-income' : 'text-expense' ?>">
            $<?= number_format($totalBalance, 2) ?>
        </p>
    </article>

    <article class="stat-card">
        <h3>Income</h3>
        <p class="stat-value text-income">$<?= number_format($totalIncome, 2) ?></p>
    </article>

    <article class="stat-card">
        <h3>Expenses</h3>
        <p class="stat-value text-expense">$<?= number_format($totalExpense, 2) ?></p>
    </article>
</section>

<section class="card">
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Category</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($transactions)): ?>
                <tr><td colspan="6" class="muted center">No matching transactions found.</td></tr>
            <?php else: ?>
                <?php foreach ($transactions as $item): ?>
                    <tr>
                        <td><?= e($item['transaction_date']) ?></td>
                        <td><span class="tag tag-<?= e($item['type']) ?>"><?= e(ucfirst($item['type'])) ?></span></td>
                        <td><?= e($item['category_name']) ?></td>
                        <td><?= e($item['description'] ?: '-') ?></td>
                        <td class="<?= $item['type'] === 'income' ? 'text-income' : 'text-expense' ?>">
                            <?= $item['type'] === 'income' ? '+' : '-' ?>$<?= number_format((float) $item['amount'], 2) ?>
                        </td>
                        <td class="actions-cell">
                            <a class="btn" href="<?= url('/transactions/edit/' . (int) $item['id'] . '?back_to=' . rawurlencode($backTo)) ?>">Edit</a>
                            <form method="POST" action="<?= url('/transactions/delete/' . (int) $item['id']) ?>" onsubmit="return confirm('Delete this transaction?')">
                                <input type="hidden" name="back_to" value="<?= e($backTo) ?>">
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
