<?php
$isEdit = $transaction !== null;
$selectedType = trim((string) old('type', $isEdit ? $transaction['type'] : 'expense'));
$selectedCategory = (int) old('category_id', $isEdit ? (int) $transaction['category_id'] : 0);
$backTo = sanitize_return_path($backTo ?? '/transactions');
?>

<section class="page-head">
    <h1><?= $isEdit ? 'Edit Transaction' : 'Add Transaction' ?></h1>
    <a class="btn" href="<?= url($backTo) ?>">Back</a>
</section>

<section class="card form-card">
    <form method="POST" action="<?= url($action) ?>" class="stack" novalidate>
        <input type="hidden" name="back_to" value="<?= e($backTo) ?>">
        <label>Type</label>
        <select id="typeField" name="type">
            <option value="income" <?= $selectedType === 'income' ? 'selected' : '' ?>>Income</option>
            <option value="expense" <?= $selectedType === 'expense' ? 'selected' : '' ?>>Expense</option>
        </select>
        <?php if (error('type')): ?>
            <p class="field-error"><?= e(error('type')) ?></p>
        <?php endif; ?>

        <label>Category</label>
        <select id="categoryField" name="category_id">
            <option value="">Select category</option>
            <?php foreach ($categories as $category): ?>
                <option
                    value="<?= (int) $category['id'] ?>"
                    data-type="<?= e($category['type']) ?>"
                    <?= $selectedCategory === (int) $category['id'] ? 'selected' : '' ?>
                >
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (error('category_id')): ?>
            <p class="field-error"><?= e(error('category_id')) ?></p>
        <?php endif; ?>

        <label>Amount</label>
        <input type="number" name="amount" min="0.01" step="0.01" value="<?= old_text('amount', $transaction['amount'] ?? '') ?>">
        <?php if (error('amount')): ?>
            <p class="field-error"><?= e(error('amount')) ?></p>
        <?php endif; ?>

        <label>Date</label>
        <input type="date" name="transaction_date" value="<?= old_text('transaction_date', $transaction['transaction_date'] ?? date('Y-m-d')) ?>">
        <?php if (error('transaction_date')): ?>
            <p class="field-error"><?= e(error('transaction_date')) ?></p>
        <?php endif; ?>

        <label>Description</label>
        <textarea name="description" rows="4" placeholder="Optional note"><?= old_text('description', $transaction['description'] ?? '') ?></textarea>

        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?></button>
    </form>
</section>

<script>
(function () {
    const typeField = document.getElementById('typeField');
    const categoryField = document.getElementById('categoryField');

    function applyCategoryFilter() {
        const selectedType = typeField.value;

        for (const option of categoryField.options) {
            if (!option.value) {
                option.hidden = false;
                continue;
            }

            option.hidden = option.dataset.type !== selectedType;
        }

        const selected = categoryField.options[categoryField.selectedIndex];
        if (selected && selected.value && selected.dataset.type !== selectedType) {
            categoryField.value = '';
        }
    }

    typeField.addEventListener('change', applyCategoryFilter);
    applyCategoryFilter();
})();
</script>
