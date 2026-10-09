<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Category;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        if ($userId === null) {
            $this->redirect('/login');
        }

        $filters = [
            'from_date' => trim((string) ($_GET['from_date'] ?? '')),
            'to_date' => trim((string) ($_GET['to_date'] ?? '')),
            'type' => trim((string) ($_GET['type'] ?? '')),
            'category_id' => trim((string) ($_GET['category_id'] ?? '')),
            'search' => trim((string) ($_GET['search'] ?? '')),
        ];
        $backTo = \sanitize_return_path($_GET['back_to'] ?? null, '/dashboard');

        $transactions = (new Transaction())->listByUserWithFilters($userId, $filters);
        $categories = (new Category())->allForUser($userId);

        // Calculate totals for filtered transactions
        $totalIncome = 0;
        $totalExpense = 0;
        foreach ($transactions as $transaction) {
            if ($transaction['type'] === 'income') {
                $totalIncome += (float) $transaction['amount'];
            } else {
                $totalExpense += (float) $transaction['amount'];
            }
        }
        $totalBalance = $totalIncome - $totalExpense;

        $this->view('transactions/index', [
            'title' => 'Transactions',
            'transactions' => $transactions,
            'categories' => $categories,
            'filters' => $filters,
            'backTo' => $backTo,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'totalBalance' => $totalBalance,
        ]);
    }

    public function createForm(): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        if ($userId === null) {
            $this->redirect('/login');
        }

        $categories = (new Category())->allForUser($userId);
        $backTo = \sanitize_return_path($_GET['back_to'] ?? null, '/transactions');

        $this->view('transactions/form', [
            'title' => 'Add Transaction',
            'transaction' => null,
            'isEdit' => false,
            'selectedType' => 'expense',
            'selectedCategory' => 0,
            'categories' => $categories,
            'action' => '/transactions/create',
            'backTo' => $backTo,
        ]);
    }

    public function create(): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        if ($userId === null) {
            $this->redirect('/login');
        }

        $backTo = \sanitize_return_path($_POST['back_to'] ?? null, '/transactions');

        $payload = $this->validatedPayload($userId);
        if ($payload === null) {
            $this->redirect('/transactions/create?back_to=' . rawurlencode($backTo));
        }

        (new Transaction())->create($payload + ['user_id' => $userId]);
        $this->flash('success', 'Transaction created.');
        $this->redirect($backTo);
    }

    public function editForm(string $id): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        if ($userId === null) {
            $this->redirect('/login');
        }

        $transaction = (new Transaction())->findById($userId, (int) $id);
        if ($transaction === null) {
            $this->flash('error', 'Transaction not found.');
            $this->redirect('/transactions');
        }

        $categories = (new Category())->allForUser($userId);
        $backTo = \sanitize_return_path($_GET['back_to'] ?? null, '/transactions');

        $selectedType = trim((string) old('type', $transaction['type']));
        $selectedCategory = (int) old('category_id', (int) $transaction['category_id']);

        $this->view('transactions/form', [
            'title' => 'Edit Transaction',
            'transaction' => $transaction,
            'isEdit' => true,
            'selectedType' => $selectedType,
            'selectedCategory' => $selectedCategory,
            'categories' => $categories,
            'action' => '/transactions/edit/' . (int) $id,
            'backTo' => $backTo,
        ]);
    }

    public function edit(string $id): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        if ($userId === null) {
            $this->redirect('/login');
        }

        $backTo = \sanitize_return_path($_POST['back_to'] ?? null, '/transactions');

        $payload = $this->validatedPayload($userId);
        if ($payload === null) {
            $this->redirect('/transactions/edit/' . (int) $id . '?back_to=' . rawurlencode($backTo));
        }

        $updated = (new Transaction())->update($userId, (int) $id, $payload);

        if (!$updated) {
            $this->flash('error', 'Unable to update transaction.');
            $this->redirect($backTo);
        }

        $this->flash('success', 'Transaction updated.');
        $this->redirect($backTo);
    }

    public function delete(string $id): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        if ($userId === null) {
            $this->redirect('/login');
        }

        $backTo = \sanitize_return_path($_POST['back_to'] ?? null, '/transactions');

        $deleted = (new Transaction())->delete($userId, (int) $id);

        if (!$deleted) {
            $this->flash('error', 'Transaction not found or cannot be deleted.');
            $this->redirect($backTo);
        }

        $this->flash('success', 'Transaction deleted.');
        $this->redirect($backTo);
    }

    private function validatedPayload(int $userId): ?array
    {
        $typeInput = $_POST['type'] ?? '';
        $type = is_string($typeInput) ? trim($typeInput) : '';

        $categoryIdInput = $_POST['category_id'] ?? '';
        $validatedCategoryId = is_string($categoryIdInput)
            ? filter_var($categoryIdInput, FILTER_VALIDATE_INT, [
                'options' => [
                    'min_range' => 1,
                    'max_range' => 4294967295,
                ],
            ])
            : false;
        $categoryId = $validatedCategoryId === false ? 0 : $validatedCategoryId;

        $amountInput = $_POST['amount'] ?? '';
        $amountInput = is_string($amountInput) ? trim($amountInput) : '';
        $amountHasValidFormat = preg_match('/\A[0-9]+(?:\.[0-9]{1,2})?\z/', $amountInput) === 1;
        $numericAmount = $amountHasValidFormat ? (float) $amountInput : 0.0;

        $descriptionInput = $_POST['description'] ?? '';
        $description = is_string($descriptionInput) ? trim($descriptionInput) : '';
        $descriptionIsString = is_string($descriptionInput);

        $transactionDateInput = $_POST['transaction_date'] ?? '';
        $transactionDate = is_string($transactionDateInput) ? trim($transactionDateInput) : '';

        $errors = [];

        if (!in_array($type, ['income', 'expense'], true)) {
            $errors['type'] = 'Select a valid transaction type.';
        }

        if ($categoryId <= 0) {
            $errors['category_id'] = 'Select a valid category.';
        }

        if (!$descriptionIsString) {
            $errors['description'] = 'Description must be text.';
        }

        if ($amountInput === '') {
            $errors['amount'] = 'Amount is required.';
        } elseif (!$amountHasValidFormat) {
            $errors['amount'] = 'Enter a valid amount with up to two decimal places.';
        } elseif ($numericAmount <= 0) {
            $errors['amount'] = 'Amount must be greater than 0.';
        } elseif ($numericAmount > 9999999999.99) {
            $errors['amount'] = 'Amount cannot exceed 9,999,999,999.99.';
        }

        if ($transactionDate === '') {
            $errors['transaction_date'] = 'Date is required.';
        } else {
            $dateParts = [];
            $dateHasValidFormat = preg_match(
                '/\A([0-9]{4})-([0-9]{2})-([0-9]{2})\z/',
                $transactionDate,
                $dateParts
            ) === 1;

            if ($dateHasValidFormat) {
                $year = (int) $dateParts[1];
                $month = (int) $dateParts[2];
                $day = (int) $dateParts[3];
                $dateHasValidFormat = $year >= 1000 && checkdate($month, $day, $year);
            }

            if (!$dateHasValidFormat) {
                $errors['transaction_date'] = 'Enter a valid date.';
            }
        }

        if ($categoryId > 0) {
            $category = (new Category())->findAccessibleById($userId, $categoryId);

            if ($category === null) {
                $errors['category_id'] = 'Select a valid category.';
            } elseif (
                in_array($type, ['income', 'expense'], true)
                && $category['type'] !== $type
            ) {
                $errors['category_id'] = 'Selected category does not match transaction type.';
            }
        }

        if ($errors !== []) {
            \set_errors($errors);
            \set_old_input([
                'type' => $type,
                'category_id' => $categoryId > 0 ? (string) $categoryId : '',
                'amount' => $amountInput,
                'description' => $description,
                'transaction_date' => $transactionDate,
            ]);
            return null;
        }

        return [
            'type' => $type,
            'category_id' => $categoryId,
            'amount' => $amountInput,
            'description' => $description,
            'transaction_date' => $transactionDate,
        ];
    }
}
