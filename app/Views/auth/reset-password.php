<?php
$passwordError = error('password');
$confirmationError = error('password_confirmation');
?>

<section class="auth-wrap">
    <div class="card auth-card">
        <h1>Choose a New Password</h1>
        <p class="muted">Enter a new password for your account.</p>

        <form method="POST" action="<?= url('/reset-password') ?>" class="stack" novalidate>
            <input type="hidden" name="token" value="<?= e($token) ?>">

            <div class="form-field">
                <label for="resetPassword">New Password</label>
                <input
                    id="resetPassword"
                    type="password"
                    name="password"
                    autocomplete="new-password"
                    data-password-rules="reset-password-rules"
                    aria-describedby="reset-password-rules<?= $passwordError ? ' resetPasswordError' : '' ?>"
                    <?= $passwordError ? 'aria-invalid="true"' : '' ?>
                >
                <ul id="reset-password-rules" class="password-rules">
                    <li data-rule="length">At least 8 characters</li>
                    <li data-rule="upper">At least 1 uppercase letter</li>
                    <li data-rule="lower">At least 1 lowercase letter</li>
                    <li data-rule="number">At least 1 number</li>
                    <li data-rule="special">At least 1 special character</li>
                </ul>
                <?php if ($passwordError): ?>
                    <p id="resetPasswordError" class="field-error" role="alert"><?= e($passwordError) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="resetPasswordConfirmation">Confirm New Password</label>
                <input
                    id="resetPasswordConfirmation"
                    type="password"
                    name="password_confirmation"
                    autocomplete="new-password"
                    <?= $confirmationError ? 'aria-invalid="true" aria-describedby="resetPasswordConfirmationError"' : '' ?>
                >
                <?php if ($confirmationError): ?>
                    <p id="resetPasswordConfirmationError" class="field-error" role="alert"><?= e($confirmationError) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
</section>
<script src="/assets/js/password-rules.js"></script>
