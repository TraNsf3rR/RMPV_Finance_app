<?php
$nameError = error('name');
$emailError = error('email');
$passwordError = error('password');
$confirmationError = error('password_confirmation');
?>

<section class="auth-wrap">
    <div class="card auth-card">
        <h1>Create Account</h1>
        <p class="muted">Start tracking your income and expenses today.</p>

        <form method="POST" action="<?= url('/register') ?>" class="stack" novalidate>
            <?= csrf_field() ?>
            <div class="form-field">
                <label for="registerName">Name</label>
                <input
                    id="registerName"
                    type="text"
                    name="name"
                    autocomplete="name"
                    value="<?= old_text('name') ?>"
                    <?= $nameError ? 'aria-invalid="true" aria-describedby="registerNameError"' : '' ?>
                >
                <?php if ($nameError): ?>
                    <p id="registerNameError" class="field-error" role="alert"><?= e($nameError) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="registerEmail">Email</label>
                <input
                    id="registerEmail"
                    type="email"
                    name="email"
                    autocomplete="email"
                    value="<?= old_text('email') ?>"
                    <?= $emailError ? 'aria-invalid="true" aria-describedby="registerEmailError"' : '' ?>
                >
                <?php if ($emailError): ?>
                    <p id="registerEmailError" class="field-error" role="alert"><?= e($emailError) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="registerPassword">Password</label>
                <input
                    id="registerPassword"
                    type="password"
                    name="password"
                    autocomplete="new-password"
                    data-password-rules="register-password-rules"
                    aria-describedby="register-password-rules<?= $passwordError ? ' registerPasswordError' : '' ?>"
                    <?= $passwordError ? 'aria-invalid="true"' : '' ?>
                >
                <ul id="register-password-rules" class="password-rules">
                    <li data-rule="length">At least 8 characters</li>
                    <li data-rule="upper">At least 1 uppercase letter</li>
                    <li data-rule="lower">At least 1 lowercase letter</li>
                    <li data-rule="number">At least 1 number</li>
                    <li data-rule="special">At least 1 special character</li>
                </ul>
                <?php if ($passwordError): ?>
                    <p id="registerPasswordError" class="field-error" role="alert"><?= e($passwordError) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="registerPasswordConfirmation">Confirm Password</label>
                <input
                    id="registerPasswordConfirmation"
                    type="password"
                    name="password_confirmation"
                    autocomplete="new-password"
                    <?= $confirmationError ? 'aria-invalid="true" aria-describedby="registerPasswordConfirmationError"' : '' ?>
                >
                <?php if ($confirmationError): ?>
                    <p id="registerPasswordConfirmationError" class="field-error" role="alert"><?= e($confirmationError) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>

        <p class="muted inline-link">Already have an account? <a href="<?= url('/login') ?>">Login</a></p>
    </div>
</section>
<script src="/assets/js/password-rules.js"></script>
