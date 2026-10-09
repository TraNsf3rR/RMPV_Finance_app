<?php $emailError = error('email'); ?>

<section class="auth-wrap">
    <div class="card auth-card">
        <h1>Forgot Password</h1>
        <p class="muted">Enter your account email and we’ll send a password reset link if an account matches.</p>

        <form method="POST" action="<?= url('/forgot-password') ?>" class="stack" novalidate>
            <?= csrf_field() ?>
            <div class="form-field">
                <label for="forgotPasswordEmail">Email</label>
                <input
                    id="forgotPasswordEmail"
                    type="email"
                    name="email"
                    autocomplete="email"
                    value="<?= old_text('email') ?>"
                    <?= $emailError ? 'aria-invalid="true" aria-describedby="forgotPasswordEmailError"' : '' ?>
                >
                <?php if ($emailError): ?>
                    <p id="forgotPasswordEmailError" class="field-error" role="alert"><?= e($emailError) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Send Reset Link</button>
        </form>

        <p class="muted inline-link"><a href="<?= url('/login') ?>">Back to login</a></p>
    </div>
</section>