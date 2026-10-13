<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        if ($userId === null) {
            $this->redirect('/login');
        }

        $categories = (new Category())->allForUser($userId);
        $incomeCategories = array_values(array_filter($categories, static fn (array $item): bool => $item['type'] === 'income'));
        $expenseCategories = array_values(array_filter($categories, static fn (array $item): bool => $item['type'] === 'expense'));

        $this->view('categories/index', [
            'title' => 'Categories',
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    public function create(): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        if ($userId === null) {
            $this->redirect('/login');
        }

        $nameInput = $_POST['name'] ?? '';
        $name = is_string($nameInput) ? trim($nameInput) : '';
        $type = trim((string) ($_POST['type'] ?? ''));
        $nameError = $this->validateCategoryName($nameInput, $name);

        if ($nameError !== null || !in_array($type, ['income', 'expense'], true)) {
            $errorKey = 'create_' . $type . '_name';
            $this->backWithErrors('/categories', [
                $errorKey => $nameError ?? 'Category name is required.',
            ], [
                $errorKey => $name,
            ]);
        }

        (new Category())->createCustom($userId, $name, $type);
        $this->flash('success', 'Category added.');
        $this->redirect('/categories');
    }

    public function update(string $id): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        if ($userId === null) {
            $this->redirect('/login');
        }

        $nameInput = $_POST['name'] ?? '';
        $name = is_string($nameInput) ? trim($nameInput) : '';
        $type = trim((string) ($_POST['type'] ?? ''));
        $nameError = $this->validateCategoryName($nameInput, $name);

        if ($nameError !== null || !in_array($type, ['income', 'expense'], true)) {
            $errorKey = 'edit_name_' . (int) $id;
            $this->backWithErrors('/categories', [
                $errorKey => $nameError ?? 'Category name is required.',
            ], [
                $errorKey => $name,
            ]);
        }

        $updated = (new Category())->updateCustom($userId, (int) $id, $name, $type);

        if (!$updated) {
            $this->flash('error', 'Unable to update category.');
            $this->redirect('/categories');
        }

        $this->flash('success', 'Category updated.');
        $this->redirect('/categories');
    }

    public function delete(string $id): void
    {
        Auth::requireAuth();

        $userId = Auth::id();
        if ($userId === null) {
            $this->redirect('/login');
        }

        $deleted = (new Category())->deleteCustom($userId, (int) $id);

        if (!$deleted) {
            $this->flash('error', 'Unable to delete category. It may already be in use.');
            $this->redirect('/categories');
        }

        $this->flash('success', 'Category deleted.');
        $this->redirect('/categories');
    }

    private function validateCategoryName(mixed $nameInput, string $name): ?string
    {
        if (!is_string($nameInput)) {
            return 'Category name must be text.';
        }

        if ($name === '') {
            return 'Category name is required.';
        }

        $nameLength = \unicode_character_count($name);
        if ($nameLength === null) {
            return 'Category name must contain valid UTF-8 text.';
        }

        if ($nameLength > 120) {
            return 'Category name cannot exceed 120 characters.';
        }

        return null;
    }
}
