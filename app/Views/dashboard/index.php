<section class="page-head">
    <div>
        <h1>Dashboard</h1>
    </div>
    <div class="page-head-actions">
        <form class="dashboard-period-form" method="GET" action="<?= url('/dashboard') ?>">
            <label for="dashboardMonth">Month</label>
            <select id="dashboardMonth" name="month">
                <?php foreach ($availableMonths as $monthValue => $monthName): ?>
                    <option value="<?= $monthValue ?>" <?= $selectedMonth === $monthValue ? 'selected' : '' ?>>
                        <?= e($monthName) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="dashboardYear">Year</label>
            <select id="dashboardYear" name="year">
                <?php foreach ($availableYears as $year): ?>
                    <option value="<?= (int) $year ?>" <?= $selectedYear === (int) $year ? 'selected' : '' ?>>
                        <?= (int) $year ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn" type="submit">Apply</button>
        </form>
        <a class="btn btn-primary" href="<?= url('/transactions/create?back_to=' . rawurlencode($dashboardReturnPath)) ?>">+ Add Transaction</a>
    </div>
</section>

<section class="stats-grid">
    <article class="stat-card">
        <h3>Total Balance</h3>
        <p class="stat-value <?= $summary['total_balance'] >= 0 ? 'text-income' : 'text-expense' ?>">
            €<?= number_format($summary['total_balance'], 2) ?>
        </p>
    </article>

    <article class="stat-card">
        <h3><?= e($selectedMonthLabel) ?> Income</h3>
        <p class="stat-value text-income">€<?= number_format($summary['monthly_income'], 2) ?></p>
    </article>

    <article class="stat-card">
        <h3><?= e($selectedMonthLabel) ?> Expenses</h3>
        <p class="stat-value text-expense">€<?= number_format($summary['monthly_expense'], 2) ?></p>
    </article>
</section>

<section class="charts-grid">
    <article class="card chart-card">
        <h2>Expense Categories (<?= e($selectedMonthLabel . ' ' . (string) $selectedYear) ?>)</h2>
        <?php if (empty($pieValues)): ?>
            <p class="chart-empty muted" role="status">No expense data for this month.</p>
        <?php else: ?>
            <div class="chart-area">
                <canvas id="expensePie"></canvas>
            </div>
        <?php endif; ?>
    </article>

    <article class="card chart-card">
        <h2>Monthly Incomes & Expenses (Through <?= e($selectedMonthLabel . ' ' . (string) $selectedYear) ?>)</h2>
        <?php if (!array_filter(array_merge($lineIncomeValues, $lineExpenseValues))): ?>
            <p class="chart-empty muted" role="status">No income or expense activity for this month.</p>
        <?php else: ?>
            <div class="chart-area">
                <canvas id="incomeExpenseLine"></canvas>
            </div>
        <?php endif; ?>
    </article>
</section>

<section class="card">
    <div class="section-head">
        <h2>Recent Transactions</h2>
        <a class="btn" href="<?= url('/transactions?back_to=' . rawurlencode($dashboardReturnPath)) ?>">View All</a>
    </div>
    <div class="table-wrap">
        <table class="recent-table">
            <thead>
            <tr>
                <th scope="col">Date</th>
                <th scope="col">Type</th>
                <th scope="col">Category</th>
                <th scope="col">Description</th>
                <th scope="col">Amount</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($recent)): ?>
                <tr><td colspan="5" class="table-empty muted" role="status">No transactions yet.</td></tr>
            <?php else: ?>
                <?php foreach ($recent as $item): ?>
                    <tr>
                        <td class="date-cell">
                            <time datetime="<?= e($item['transaction_date']) ?>"><?= e($item['transaction_date']) ?></time>
                        </td>
                        <td><span class="tag tag-<?= e($item['type']) ?>"><?= e(ucfirst($item['type'])) ?></span></td>
                        <td><?= e($item['category_name']) ?></td>
                        <td class="description-cell"><?= e($item['description'] ?: '-') ?></td>
                        <td class="amount-cell <?= $item['type'] === 'income' ? 'text-income' : 'text-expense' ?>">
                            <?= $item['type'] === 'income' ? '+' : '-' ?>€<?= number_format((float) $item['amount'], 2) ?>
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
