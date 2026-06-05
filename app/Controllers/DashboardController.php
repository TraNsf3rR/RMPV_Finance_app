<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Transaction;

class DashboardController extends Controller
{
    private const MONTHS = [
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

    public function index(): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        if ($userId === null) {
            $this->redirect('/login');
        }

        $transactionModel = new Transaction();

        $selectedYear = (int) ($_GET['year'] ?? date('Y'));
        $selectedMonth = (int) ($_GET['month'] ?? date('n'));

        $currentYear = (int) date('Y');
        if ($selectedYear < $currentYear - 10 || $selectedYear > $currentYear + 10) {
            $selectedYear = $currentYear;
        }

        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = (int) date('n');
        }

        $period = [
            'year' => $selectedYear,
            'month' => $selectedMonth,
        ];

        $availableYears = $transactionModel->getAvailableYears($userId);
        $yearOptions = range($currentYear + 5, $currentYear - 15);
        $availableYears = array_values(array_unique(array_merge($availableYears, $yearOptions, [$selectedYear])));
        rsort($availableYears);

        $summary = $transactionModel->getDashboardSummary($userId, $period);
        $pieData = $transactionModel->getExpenseCategoryPieData($userId, $period);
        $lineExpenseData = $transactionModel->getMonthlyExpenseLineData($userId, $period);
        $lineIncomeData = $transactionModel->getMonthlyIncomeLineData($userId, $period);
        $recent = $transactionModel->recent($userId);

        // Transform chart data
        $pieLabels = array_map(static fn (array $item): string => $item['name'], $pieData);
        $pieValues = array_map(static fn (array $item): float => (float) $item['total'], $pieData);
        $lineLabels = array_map(static fn (array $item): string => $item['month'], $lineExpenseData);
        $lineExpenseValues = array_map(static fn (array $item): float => (float) $item['total'], $lineExpenseData);
        $lineIncomeValues = array_map(static fn (array $item): float => (float) $item['total'], $lineIncomeData);

        // Calculate display values
        $selectedMonthLabel = self::MONTHS[$selectedMonth] ?? date('F');
        $dashboardReturnPath = '/dashboard?month=' . $selectedMonth . '&year=' . $selectedYear;

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'summary' => $summary,
            'pieLabels' => $pieLabels,
            'pieValues' => $pieValues,
            'lineLabels' => $lineLabels,
            'lineExpenseValues' => $lineExpenseValues,
            'lineIncomeValues' => $lineIncomeValues,
            'recent' => $recent,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'selectedMonthLabel' => $selectedMonthLabel,
            'availableYears' => $availableYears,
            'months' => self::MONTHS,
            'dashboardReturnPath' => $dashboardReturnPath,
            'transactionsNavPath' => '/transactions?back_to=' . rawurlencode($dashboardReturnPath),
        ]);
    }
}
