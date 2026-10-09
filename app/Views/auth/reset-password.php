<section class="auth-wrap">
    <div class="card auth-card">
        <h1>Choose a New Password</h1>
        <p class="muted">Enter a new password for your account.</p>

        <form method="POST" action="<?= url('/reset-password') ?>" class="stack" novalidate>
            <input type="hidden" name="token" value="<?= e($token) ?>">

            <label>New Password</label>
            <input type="password" name="password" data-password-rules="reset-password-rules">
            <ul id="reset-password-rules" class="password-rules">
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
    </div>
</section>
<script src="/assets/js/password-rules.js"></script>
