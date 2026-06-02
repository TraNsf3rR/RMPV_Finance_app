<section class="auth-wrap">
    <div class="card auth-card">
        <h1>Create Account</h1>
        <p class="muted">Start tracking your income and expenses today.</p>

        <form method="POST" action="<?= url('/register') ?>" class="stack" novalidate>
            <label>Name</label>
            <input type="text" name="name" value="<?= old_text('name') ?>">
            <?php if (error('name')): ?>
                <p class="field-error"><?= e(error('name')) ?></p>
            <?php endif; ?>

            <label>Email</label>
            <input type="email" name="email" value="<?= old_text('email') ?>">
            <?php if (error('email')): ?>
                <p class="field-error"><?= e(error('email')) ?></p>
            <?php endif; ?>

            <label>Password</label>
            <input type="password" name="password" data-password-rules="register-password-rules">
            <ul id="register-password-rules" class="password-rules">
                <li data-rule="length">At least 8 characters</li>
                <li data-rule="upper">At least 1 uppercase letter</li>
                <li data-rule="lower">At least 1 lowercase letter</li>
                <li data-rule="number">At least 1 number</li>
                <li data-rule="special">At least 1 special character</li>
            </ul>
            <?php if (error('password')): ?>
                <p class="field-error"><?= e(error('password')) ?></p>
            <?php endif; ?>

            <label>Confirm Password</label>
            <input type="password" name="password_confirmation">
            <?php if (error('password_confirmation')): ?>
                <p class="field-error"><?= e(error('password_confirmation')) ?></p>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>

        <p class="muted inline-link">Already have an account? <a href="<?= url('/login') ?>">Login</a></p>
    </div>
</section>
<script src="/assets/js/password-rules.js"></script>
