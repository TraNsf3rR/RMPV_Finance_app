<?php
$pieLabels = array_map(static fn (array $item): string => $item['name'], $pieData);
$pieValues = array_map(static fn (array $item): float => (float) $item['total'], $pieData);
$lineLabels = array_map(static fn (array $item): string => $item['month'], $lineExpenseData);
$lineExpenseValues = array_map(static fn (array $item): float => (float) $item['total'], $lineExpenseData);
$lineIncomeValues = array_map(static fn (array $item): float => (float) $item['total'], $lineIncomeData);
$selectedMonth = (int) ($selectedPeriod['month'] ?? date('n'));
$selectedYear = (int) ($selectedPeriod['year'] ?? date('Y'));
$selectedMonthLabel = DateTime::createFromFormat('!m', (string) $selectedMonth)?->format('F') ?? date('F');
$dashboardReturnPath = '/dashboard?month=' . $selectedMonth . '&year=' . $selectedYear;
$months = [
    1 => 'January',
    2 => 'February',
    3 => 'March',
    4 => 'April',
    5 => 'May',
    6 => 'June',
    7 => 'July',
    8 => 'August',
    9 => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December',
];
?>

<section class="page-head">
    <div>
        <h1>Dashboard</h1>
        <p class="muted">Viewing data for <?= e($selectedMonthLabel . ' ' . (string) $selectedYear) ?></p>
    </div>
    <div class="page-head-actions">
        <form class="dashboard-period-form" method="GET" action="<?= url('/dashboard') ?>">
            <div>
                <label for="dashboardMonth">Month</label>
                <select id="dashboardMonth" name="month">
                    <?php foreach ($months as $monthValue => $monthName): ?>
                        <option value="<?= $monthValue ?>" <?= $selectedMonth === $monthValue ? 'selected' : '' ?>>
                            <?= e($monthName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="dashboardYear">Year</label>
                <select id="dashboardYear" name="year">
                    <?php foreach ($availableYears as $year): ?>
                        <option value="<?= (int) $year ?>" <?= $selectedYear === (int) $year ? 'selected' : '' ?>>
                            <?= (int) $year ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn" type="submit">Apply</button>
        </form>
        <a class="btn btn-primary" href="<?= url('/transactions/create?back_to=' . rawurlencode($dashboardReturnPath)) ?>">+ Add Transaction</a>
    </div>
</section>

<section class="stats-grid">
    <article class="stat-card">
        <h3>Total Balance</h3>
        <p class="stat-value <?= $summary['total_balance'] >= 0 ? 'text-income' : 'text-expense' ?>">
            $<?= number_format($summary['total_balance'], 2) ?>
        </p>
    </article>

    <article class="stat-card">
        <h3><?= e($selectedMonthLabel) ?> Income</h3>
        <p class="stat-value text-income">$<?= number_format($summary['monthly_income'], 2) ?></p>
    </article>

    <article class="stat-card">
        <h3><?= e($selectedMonthLabel) ?> Expenses</h3>
        <p class="stat-value text-expense">$<?= number_format($summary['monthly_expense'], 2) ?></p>
    </article>
</section>

<section class="charts-grid">
    <article class="card">
        <h2>Expense Categories (<?= e($selectedMonthLabel . ' ' . (string) $selectedYear) ?>)</h2>
        <canvas id="expensePie"></canvas>
    </article>

    <article class="card">
        <h2>Monthly Incomes & Expenses (12 Months Through <?= e($selectedMonthLabel . ' ' . (string) $selectedYear) ?>)</h2>
        <canvas id="incomeExpenseLine"></canvas>
    </article>
</section>

<section class="card">
    <div class="section-head">
        <h2>Recent Transactions</h2>
        <a class="btn" href="<?= url('/transactions?back_to=' . rawurlencode($dashboardReturnPath)) ?>">View All</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Category</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($recent)): ?>
                <tr><td colspan="5" class="muted center">No transactions yet.</td></tr>
            <?php else: ?>
                <?php foreach ($recent as $item): ?>
                    <tr>
                        <td><?= e($item['transaction_date']) ?></td>
                        <td><span class="tag tag-<?= e($item['type']) ?>"><?= e(ucfirst($item['type'])) ?></span></td>
                        <td><?= e($item['category_name']) ?></td>
                        <td><?= e($item['description'] ?: '-') ?></td>
                        <td class="<?= $item['type'] === 'income' ? 'text-income' : 'text-expense' ?>">
                            <?= $item['type'] === 'income' ? '+' : '-' ?>$<?= number_format((float) $item['amount'], 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
window.dashboardData = {
    pie: {
        labels: <?= json_encode($pieLabels, JSON_THROW_ON_ERROR) ?>,
        values: <?= json_encode($pieValues, JSON_THROW_ON_ERROR) ?>
    },
    line: {
        labels: <?= json_encode($lineLabels, JSON_THROW_ON_ERROR) ?>,
        income: <?= json_encode($lineIncomeValues, JSON_THROW_ON_ERROR) ?>,
        expense: <?= json_encode($lineExpenseValues, JSON_THROW_ON_ERROR) ?>
    }
};
</script>
<script src="/assets/js/dashboard.js"></script>
