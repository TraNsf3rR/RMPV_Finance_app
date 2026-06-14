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

        $availableYears = $transactionModel->getAvailableYears($userId);
        rsort($availableYears);

        if (empty($availableYears)) {
            $currentYear = (int) date('Y');
            $availableYears = [$currentYear];
            $selectedYear = $currentYear;
            $availableMonthValues = [((int) date('n'))];
            $selectedMonth = (int) date('n');
        } elseif (!in_array($selectedYear, $availableYears, true)) {
            $selectedYear = $availableYears[0];
        }

        $availableMonthValues = $transactionModel->getAvailableMonthsForYear($userId, $selectedYear);
        if (empty($availableMonthValues)) {
            $selectedYear = $availableYears[0];
            $availableMonthValues = $transactionModel->getAvailableMonthsForYear($userId, $selectedYear);
        }

        if (empty($availableMonthValues)) {
            $availableMonthValues = [(int) date('n')];
        }

        if ($selectedMonth < 1 || !in_array($selectedMonth, $availableMonthValues, true)) {
            $selectedMonth = $availableMonthValues[0];
        }

        $availableMonths = array_intersect_key(self::MONTHS, array_flip($availableMonthValues));

        $period = [
            'year' => $selectedYear,
            'month' => $selectedMonth,
        ];

        $summary = $transactionModel->getDashboardSummary($userId, $period);
        $pieData = $transactionModel->getExpenseCategoryPieData($userId, $period);
        $lineExpenseData = $transactionModel->getWeeklyExpenseLineData($userId, $period);
        $lineIncomeData = $transactionModel->getWeeklyIncomeLineData($userId, $period);
        $recent = $transactionModel->recent($userId);

        // Transform chart data
        $pieLabels = array_map(static fn (array $item): string => $item['name'], $pieData);
        $pieValues = array_map(static fn (array $item): float => (float) $item['total'], $pieData);
        $lineLabels = array_map(static fn (array $item): string => $item['week'], $lineExpenseData);
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
            'availableMonths' => $availableMonths,
            'dashboardReturnPath' => $dashboardReturnPath,
            'transactionsNavPath' => '/transactions?back_to=' . rawurlencode($dashboardReturnPath),
        ]);
    }
}
