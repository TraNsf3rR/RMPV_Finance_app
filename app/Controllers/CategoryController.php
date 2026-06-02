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

        $name = trim((string) ($_POST['name'] ?? ''));
        $type = trim((string) ($_POST['type'] ?? ''));

        if ($name === '' || !in_array($type, ['income', 'expense'], true)) {
            $this->backWithErrors('/categories', [
                'create_' . $type . '_name' => 'Category name is required.',
            ], [
                'create_' . $type . '_name' => $name,
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

        $name = trim((string) ($_POST['name'] ?? ''));
        $type = trim((string) ($_POST['type'] ?? ''));

        if ($name === '' || !in_array($type, ['income', 'expense'], true)) {
            $this->backWithErrors('/categories', [
                'edit_name_' . (int) $id => 'Category name is required.',
            ], [
                'edit_name_' . (int) $id => $name,
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
}
