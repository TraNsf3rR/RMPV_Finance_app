<?php
$typeError = error('type');
$categoryError = error('category_id');
$amountError = error('amount');
$dateError = error('transaction_date');
$descriptionError = error('description');
?>

<section class="page-head">
    <h1><?= $isEdit ? 'Edit Transaction' : 'Add Transaction' ?></h1>
    <a class="btn" href="<?= url($backTo) ?>">Back</a>
</section>

<section class="card form-card">
    <form method="POST" action="<?= url($action) ?>" class="stack" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="back_to" value="<?= e($backTo) ?>">
        <div class="form-field">
            <label for="typeField">Type</label>
            <select
                id="typeField"
                name="type"
                <?= $typeError ? 'aria-invalid="true" aria-describedby="typeFieldError"' : '' ?>
            >
                <option value="income" <?= $selectedType === 'income' ? 'selected' : '' ?>>Income</option>
                <option value="expense" <?= $selectedType === 'expense' ? 'selected' : '' ?>>Expense</option>
            </select>
            <?php if ($typeError): ?>
                <p id="typeFieldError" class="field-error" role="alert"><?= e($typeError) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="categoryField">Category</label>
            <select
                id="categoryField"
                name="category_id"
                <?= $categoryError ? 'aria-invalid="true" aria-describedby="categoryFieldError"' : '' ?>
            >
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
            <?php if ($categoryError): ?>
                <p id="categoryFieldError" class="field-error" role="alert"><?= e($categoryError) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="amountField">Amount</label>
            <input
                id="amountField"
                type="number"
                name="amount"
                min="0.01"
                step="0.01"
                value="<?= old_text('amount', $transaction['amount'] ?? '') ?>"
                <?= $amountError ? 'aria-invalid="true" aria-describedby="amountFieldError"' : '' ?>
            >
            <?php if ($amountError): ?>
                <p id="amountFieldError" class="field-error" role="alert"><?= e($amountError) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="transactionDate">Date</label>
            <input
                id="transactionDate"
                type="date"
                name="transaction_date"
                value="<?= old_text('transaction_date', $transaction['transaction_date'] ?? date('Y-m-d')) ?>"
                <?= $dateError ? 'aria-invalid="true" aria-describedby="transactionDateError"' : '' ?>
            >
            <?php if ($dateError): ?>
                <p id="transactionDateError" class="field-error" role="alert"><?= e($dateError) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="descriptionField">Description</label>
            <input
                id="descriptionField"
                type="text"
                name="description"
                placeholder="Optional note"
                value="<?= old_text('description', $transaction['description'] ?? '') ?>"
                <?= $descriptionError ? 'aria-invalid="true" aria-describedby="descriptionFieldError"' : '' ?>
            >
            <?php if ($descriptionError): ?>
                <p id="descriptionFieldError" class="field-error" role="alert"><?= e($descriptionError) ?></p>
            <?php endif; ?>
        </div>

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
