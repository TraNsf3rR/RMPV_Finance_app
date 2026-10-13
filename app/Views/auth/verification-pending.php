<?php
$emailError = error('email');
?>

<section class="auth-wrap">
    <div class="card auth-card">
        <h1>Activate Your Account</h1>
        <p class="muted">
            Before signing in, activate your account using the link sent to
            <?= $email !== '' ? '<strong>' . e($email) . '</strong>' : 'your email address' ?>.
            The link expires after 24 hours. Check your spam folder if it is not in your inbox.
        </p>

        <form method="POST" action="<?= url('/verify-email/resend') ?>" class="stack" novalidate>
            <?= csrf_field() ?>
            <div class="form-field">
                <label for="verificationEmail">Email</label>
                <input
                    id="verificationEmail"
                    type="email"
                    name="email"
                    autocomplete="email"
                    value="<?= old_text('email', $email) ?>"
                    <?= $emailError ? 'aria-invalid="true" aria-describedby="verificationEmailError"' : '' ?>
                >
                <?php if ($emailError): ?>
                    <p id="verificationEmailError" class="field-error" role="alert"><?= e($emailError) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Resend Activation Link</button>
        </form>

        <p class="muted inline-link"><a href="<?= url('/login') ?>">Back to login</a></p>
    </div>
</section>
