<section class="auth-wrap">
    <div class="card auth-card">
        <?php if ($isValidToken): ?>
            <h1>Confirm Account Activation</h1>
            <p class="muted">Select the button below to activate your account. This link can only be used once.</p>

            <form method="POST" action="<?= url('/verify-email') ?>" class="stack">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <button type="submit" class="btn btn-primary">Activate Account</button>
            </form>
        <?php else: ?>
            <h1>Activation Link Unavailable</h1>
            <p class="muted">This link is invalid, expired, or has already been used.</p>
            <a class="btn btn-primary" href="<?= url('/verify-email/pending') ?>">Request a New Link</a>
        <?php endif; ?>
    </div>
</section>
