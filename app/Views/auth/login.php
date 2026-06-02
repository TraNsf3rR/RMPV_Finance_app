<section class="auth-wrap">
    <div class="card auth-card">
        <h1>Login</h1>
        <p class="muted">Manage your personal finances in one place.</p>

        <form method="POST" action="<?= url('/login') ?>" class="stack" novalidate>
            <label>Email</label>
            <input type="email" name="email" value="<?= old_text('email') ?>">
            <?php if (error('email')): ?>
                <p class="field-error"><?= e(error('email')) ?></p>
            <?php endif; ?>

            <label>Password</label>
            <input type="password" name="password">
            <?php if (error('password')): ?>
                <p class="field-error"><?= e(error('password')) ?></p>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>

        <p class="muted inline-link">No account? <a href="<?= url('/register') ?>">Register here</a></p>
        <p class="muted inline-link"><a href="<?= url('/forgot-password') ?>">Forgot password?</a></p>
    </div>
</section>
