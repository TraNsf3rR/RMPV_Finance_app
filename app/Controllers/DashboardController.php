<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Transaction;

class DashboardController extends Controller
{
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

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'summary' => $summary,
            'pieData' => $pieData,
            'lineExpenseData' => $lineExpenseData,
            'lineIncomeData' => $lineIncomeData,
            'recent' => $recent,
            'selectedPeriod' => $period,
            'availableYears' => $availableYears,
            'transactionsNavPath' => '/transactions?back_to=' . rawurlencode('/dashboard?month=' . $selectedMonth . '&year=' . $selectedYear),
        ]);
    }
}
