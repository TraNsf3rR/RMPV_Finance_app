<section class="auth-wrap">
    <div class="card auth-card">
        <h1>Forgot Password</h1>
        <p class="muted">Enter your account email and we’ll send a password reset link if an account matches.</p>

        <form method="POST" action="<?= url('/forgot-password') ?>" class="stack" novalidate>
            <label>Email</label>
            <input type="email" name="email" value="<?= old_text('email') ?>">
            <?php if (error('email')): ?>
                <p class="field-error"><?= e(error('email')) ?></p>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Send Reset Link</button>
        </form>

        <p class="muted inline-link"><a href="<?= url('/login') ?>">Back to login</a></p>
    </div>
</section>