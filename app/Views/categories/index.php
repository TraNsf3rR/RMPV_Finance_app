<section class="page-head">
    <h1>Category Management</h1>
</section>

<section class="category-columns">
    <article class="card">
        <h2>Income Categories</h2>

        <form class="inline-form" method="POST" action="<?= url('/categories/create') ?>" novalidate>
            <input type="hidden" name="type" value="income">
            <input type="text" name="name" placeholder="Add income category" value="<?= old_text('create_income_name') ?>">
            <button class="btn btn-primary" type="submit">Add</button>
        </form>
        <?php if (error('create_income_name')): ?>
            <p class="field-error"><?= e(error('create_income_name')) ?></p>
        <?php endif; ?>

        <div class="category-list">
            <?php foreach ($incomeCategories as $category): ?>
                <div class="category-item">
                    <form method="POST" action="<?= url('/categories/update/' . (int) $category['id']) ?>" class="inline-form wide" novalidate>
                        <input type="text" name="name" value="<?= old_text('edit_name_' . (int) $category['id'], trim((string) $category['name'])) ?>">
                        <input type="hidden" name="type" value="income">
                        <button class="btn" type="submit">Save</button>
                    </form>
                    <form method="POST" action="<?= url('/categories/delete/' . (int) $category['id']) ?>" onsubmit="return confirm('Delete this category?')">
                        <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                </div>
                <?php if (error('edit_name_' . (int) $category['id'])): ?>
                    <p class="field-error"><?= e(error('edit_name_' . (int) $category['id'])) ?></p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="card">
        <h2>Expense Categories</h2>

        <form class="inline-form" method="POST" action="<?= url('/categories/create') ?>" novalidate>
            <input type="hidden" name="type" value="expense">
            <input type="text" name="name" placeholder="Add expense category" value="<?= old_text('create_expense_name') ?>">
            <button class="btn btn-primary" type="submit">Add</button>
        </form>
        <?php if (error('create_expense_name')): ?>
            <p class="field-error"><?= e(error('create_expense_name')) ?></p>
        <?php endif; ?>

        <div class="category-list">
            <?php foreach ($expenseCategories as $category): ?>
                <div class="category-item">
                    <form method="POST" action="<?= url('/categories/update/' . (int) $category['id']) ?>" class="inline-form wide" novalidate>
                        <input type="text" name="name" value="<?= old_text('edit_name_' . (int) $category['id'], trim((string) $category['name'])) ?>">
                        <input type="hidden" name="type" value="expense">
                        <button class="btn" type="submit">Save</button>
                    </form>
                    <form method="POST" action="<?= url('/categories/delete/' . (int) $category['id']) ?>" onsubmit="return confirm('Delete this category?')">
                        <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                </div>
                <?php if (error('edit_name_' . (int) $category['id'])): ?>
                    <p class="field-error"><?= e(error('edit_name_' . (int) $category['id'])) ?></p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </article>
</section>
