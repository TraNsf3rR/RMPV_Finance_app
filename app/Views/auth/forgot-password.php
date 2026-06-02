<section class="auth-wrap">
    <div class="card auth-card">
        <h1>Reset Password</h1>
        <p class="muted">Enter your account email and choose a new password.</p>

        <form method="POST" action="<?= url('/forgot-password') ?>" class="stack" novalidate>
            <label>Email</label>
            <input type="email" name="email" value="<?= old_text('email') ?>">
            <?php if (error('email')): ?>
                <p class="field-error"><?= e(error('email')) ?></p>
            <?php endif; ?>

            <label>New Password</label>
            <input type="password" name="password" data-password-rules="forgot-password-rules">
            <ul id="forgot-password-rules" class="password-rules">
                <li data-rule="length">At least 8 characters</li>
                <li data-rule="upper">At least 1 uppercase letter</li>
                <li data-rule="lower">At least 1 lowercase letter</li>
                <li data-rule="number">At least 1 number</li>
                <li data-rule="special">At least 1 special character</li>
            </ul>
            <?php if (error('password')): ?>
                <p class="field-error"><?= e(error('password')) ?></p>
            <?php endif; ?>

            <label>Confirm New Password</label>
            <input type="password" name="password_confirmation">
            <?php if (error('password_confirmation')): ?>
                <p class="field-error"><?= e(error('password_confirmation')) ?></p>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>

        <p class="muted inline-link"><a href="<?= url('/login') ?>">Back to login</a></p>
    </div>
</section>
<script src="/assets/js/password-rules.js"></script>