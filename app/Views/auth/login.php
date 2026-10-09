<?php
$emailError = error('email');
$passwordError = error('password');
?>

<section class="auth-wrap">
    <div class="card auth-card">
        <h1>Login</h1>
        <p class="muted">Manage your personal finances in one place.</p>

        <form method="POST" action="<?= url('/login') ?>" class="stack" novalidate>
            <div class="form-field">
                <label for="loginEmail">Email</label>
                <input
                    id="loginEmail"
                    type="email"
                    name="email"
                    autocomplete="email"
                    value="<?= old_text('email') ?>"
                    <?= $emailError ? 'aria-invalid="true" aria-describedby="loginEmailError"' : '' ?>
                >
                <?php if ($emailError): ?>
                    <p id="loginEmailError" class="field-error" role="alert"><?= e($emailError) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="loginPassword">Password</label>
                <input
                    id="loginPassword"
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    <?= $passwordError ? 'aria-invalid="true" aria-describedby="loginPasswordError"' : '' ?>
                >
                <?php if ($passwordError): ?>
                    <p id="loginPasswordError" class="field-error" role="alert"><?= e($passwordError) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>

        <p class="muted inline-link">No account? <a href="<?= url('/register') ?>">Register here</a></p>
        <p class="muted inline-link"><a href="<?= url('/forgot-password') ?>">Forgot password?</a></p>
    </div>
</section>
