<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Models\EmailVerification;
use App\Models\PasswordReset;
use App\Models\User;
use PHPMailer\PHPMailer\Exception as MailerException;
use RuntimeException;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        Auth::requireGuest();
        $this->view('auth/login', ['title' => 'Login']);
    }

    public function login(): void
    {
        Auth::requireGuest();

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = trim((string) ($_POST['password'] ?? ''));

        if ($email === '' || $password === '') {
            $this->backWithErrors('/login', [
                'email' => $email === '' ? 'Email is required.' : '',
                'password' => $password === '' ? 'Password is required.' : '',
            ], [
                'email' => $email,
            ]);
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->backWithErrors('/login', [
                'password' => 'Invalid email or password.',
            ], [
                'email' => $email,
            ]);
        }

        if ((int) $user['email_verified'] !== 1) {
            $_SESSION['_pending_verification_email'] = (string) $user['email'];
            $this->flash('error', 'Activate your email address before signing in.');
            $this->redirect('/verify-email/pending');
        }

        Auth::login((int) $user['id']);
        $this->flash('success', 'Welcome back, ' . $user['name'] . '!');
        $this->redirect('/dashboard');
    }

    public function showRegister(): void
    {
        Auth::requireGuest();
        $this->view('auth/register', ['title' => 'Register']);
    }

    public function showForgotPassword(): void
    {
        Auth::requireGuest();
        $this->view('auth/forgot-password', ['title' => 'Forgot Password']);
    }

    public function register(): void
    {
        Auth::requireGuest();

        $nameInput = $_POST['name'] ?? '';
        $nameIsString = is_string($nameInput);
        $name = $nameIsString ? trim($nameInput) : '';
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = trim((string) ($_POST['password'] ?? ''));
        $confirm = trim((string) ($_POST['password_confirmation'] ?? ''));

        $errors = [];

        if (!$nameIsString) {
            $errors['name'] = 'Name must be text.';
        } elseif ($name === '') {
            $errors['name'] = 'Name is required.';
        } else {
            $nameLength = \unicode_character_count($name);
            if ($nameLength === null) {
                $errors['name'] = 'Name must contain valid UTF-8 text.';
            } elseif ($nameLength > 120) {
                $errors['name'] = 'Name cannot exceed 120 characters.';
            }
        }

        if ($email === '' || $password === '' || $confirm === '') {
            if ($email === '') {
                $errors['email'] = 'Email is required.';
            }
            if ($password === '') {
                $errors['password'] = 'Password is required.';
            }
            if ($confirm === '') {
                $errors['password_confirmation'] = 'Password confirmation is required.';
            }
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        $passwordError = $this->validatePassword($password);
        if ($password !== '' && $passwordError !== null) {
            $errors['password'] = $passwordError;
        }

        if ($password !== $confirm) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }

        if ($errors !== []) {
            $this->backWithErrors('/register', $errors, [
                'name' => $name,
                'email' => $email,
            ]);
        }

        $userModel = new User();
        $existingUser = $userModel->findByEmail($email);

        if ($existingUser !== null) {
            if ((int) $existingUser['email_verified'] !== 1) {
                $_SESSION['_pending_verification_email'] = $email;
                $this->flash('error', 'An account with this email is waiting for activation. Request a new link below.');
                $this->redirect('/verify-email/pending');
            }

            $this->backWithErrors('/register', [
                'email' => 'Email is already used.',
            ], [
                'name' => $name,
                'email' => $email,
            ]);
        }

        $id = $userModel->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
        $_SESSION['_pending_verification_email'] = $email;

        try {
            if ($this->sendEmailVerification($id, $email)) {
                $this->flash('success', 'Account created. Check your email for an activation link before signing in.');
            } else {
                $this->flash('error', 'Your account was created, but an activation link could not be issued. Please request a new one.');
            }
        } catch (MailerException | RuntimeException $exception) {
            error_log('Account verification email delivery failed: ' . $exception->getMessage());
            $this->flash('error', 'Your account was created, but the activation email could not be sent. Check the mail settings and try again.');
        }

        $this->redirect('/verify-email/pending');
    }

    public function showEmailVerificationPending(): void
    {
        Auth::requireGuest();

        $email = $_SESSION['_pending_verification_email'] ?? '';
        $this->view('auth/verification-pending', [
            'title' => 'Activate Your Account',
            'email' => is_string($email) ? $email : '',
        ]);
    }

    public function showEmailVerification(): void
    {
        Auth::requireGuest();

        $tokenInput = $_GET['token'] ?? '';
        $token = is_string($tokenInput) ? $tokenInput : '';
        $isValidToken = $this->isValidEmailVerificationToken($token)
            && (new EmailVerification())->hasValidToken(hash('sha256', $token));

        header('Referrer-Policy: no-referrer');
        header('Cache-Control: no-store, max-age=0');

        $this->view('auth/verify-email', [
            'title' => 'Activate Your Account',
            'isValidToken' => $isValidToken,
            'token' => $isValidToken ? $token : '',
        ]);
    }

    public function verifyEmail(): void
    {
        Auth::requireGuest();

        $tokenInput = $_POST['token'] ?? '';
        $token = is_string($tokenInput) ? $tokenInput : '';
        $verified = $this->isValidEmailVerificationToken($token)
            && (new EmailVerification())->verify(hash('sha256', $token));

        if (!$verified) {
            $this->flash('error', 'This activation link is invalid, expired, or already used. Request a new link.');
            $this->redirect('/verify-email/pending');
        }

        unset($_SESSION['_pending_verification_email']);
        $this->flash('success', 'Your account has been activated. You can now sign in.');
        $this->redirect('/login');
    }

    public function resendEmailVerification(): void
    {
        Auth::requireGuest();

        $emailInput = $_POST['email'] ?? '';
        $email = is_string($emailInput) ? trim($emailInput) : '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->backWithErrors('/verify-email/pending', [
                'email' => 'Please enter a valid email address.',
            ], [
                'email' => $email,
            ]);
        }

        $_SESSION['_pending_verification_email'] = $email;
        $user = (new User())->findByEmail($email);

        if ($user !== null && (int) $user['email_verified'] !== 1) {
            try {
                $this->sendEmailVerification((int) $user['id'], (string) $user['email']);
            } catch (MailerException | RuntimeException $exception) {
                error_log('Account verification email delivery failed: ' . $exception->getMessage());
                $this->flash('error', 'The activation email could not be sent. Check the mail settings and try again.');
                $this->redirect('/verify-email/pending');
            }
        }

        $this->flash(
            'success',
            'If this address belongs to an unverified account, an activation link has been sent. Wait one minute before requesting another.'
        );
        $this->redirect('/verify-email/pending');
    }

    public function forgotPassword(): void
    {
        Auth::requireGuest();

        $emailInput = $_POST['email'] ?? '';
        $email = is_string($emailInput) ? trim($emailInput) : '';
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
            $this->backWithErrors('/forgot-password', $errors, [
                'email' => $email,
            ]);
        }

        $user = (new User())->findByEmail($email);

        if ($user !== null) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $userId = (int) $user['id'];
            $passwordReset = new PasswordReset();

            if ($passwordReset->issueForUser($userId, $tokenHash)) {
                try {
                    (new Mailer())->sendPasswordReset((string) $user['email'], $token);
                } catch (MailerException | RuntimeException $exception) {
                    $passwordReset->revoke($userId, $tokenHash);
                    error_log('Password reset email delivery failed: ' . $exception->getMessage());
                }
            }
        }

        $this->flash(
            'success',
            'If an account exists for that email, password reset instructions will be sent shortly.'
        );
        $this->redirect('/forgot-password');
    }

    public function showResetPassword(): void
    {
        Auth::requireGuest();

        $tokenInput = $_GET['token'] ?? '';
        $token = is_string($tokenInput) ? $tokenInput : '';

        if (!$this->isValidResetToken($token) || !(new PasswordReset())->hasValidToken(hash('sha256', $token))) {
            $this->flash('error', 'That reset link is invalid or expired. Please request a new one.');
            $this->redirect('/forgot-password');
        }

        header('Referrer-Policy: no-referrer');
        header('Cache-Control: no-store, max-age=0');

        $this->view('auth/reset-password', [
            'title' => 'Choose a New Password',
            'token' => $token,
        ]);
    }

    public function resetPassword(): void
    {
        Auth::requireGuest();

        $tokenInput = $_POST['token'] ?? '';
        $token = is_string($tokenInput) ? $tokenInput : '';
        $passwordReset = new PasswordReset();

        if (!$this->isValidResetToken($token) || !$passwordReset->hasValidToken(hash('sha256', $token))) {
            $this->flash('error', 'That reset link is invalid or expired. Please request a new one.');
            $this->redirect('/forgot-password');
        }

        $passwordInput = $_POST['password'] ?? '';
        $password = is_string($passwordInput) ? trim($passwordInput) : '';
        $confirmationInput = $_POST['password_confirmation'] ?? '';
        $confirmation = is_string($confirmationInput) ? trim($confirmationInput) : '';
        $errors = [];

        $passwordError = $this->validatePassword($password);
        if ($passwordError !== null) {
            $errors['password'] = $passwordError;
        }

        if ($confirmation === '') {
            $errors['password_confirmation'] = 'Password confirmation is required.';
        } elseif ($password !== $confirmation) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }

        if ($errors !== []) {
            $this->backWithErrors(
                '/reset-password?token=' . rawurlencode($token),
                $errors
            );
        }

        $updated = $passwordReset->resetPassword(
            hash('sha256', $token),
            password_hash($password, PASSWORD_DEFAULT)
        );

        if (!$updated) {
            $this->flash('error', 'That reset link is invalid or expired. Please request a new one.');
            $this->redirect('/forgot-password');
        }

        $this->flash('success', 'Password updated. You can now sign in.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        Auth::requireAuth();
        Auth::logout();

        $this->flash('success', 'You have been logged out.');
        $this->redirect('/login');
    }

    private function validatePassword(string $password): ?string
    {
        if ($password === '') {
            return 'Password is required.';
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/', $password)) {
            return 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.';
        }

        return null;
    }

    private function isValidResetToken(string $token): bool
    {
        return preg_match('/\A[a-f0-9]{64}\z/', $token) === 1;
    }

    private function isValidEmailVerificationToken(string $token): bool
    {
        return preg_match('/\A[a-f0-9]{64}\z/', $token) === 1;
    }

    private function sendEmailVerification(int $userId, string $email): bool
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $emailVerification = new EmailVerification();

        if (!$emailVerification->issueForUser($userId, $tokenHash)) {
            return false;
        }

        try {
            (new Mailer())->sendEmailVerification($email, $token);
        } catch (MailerException | RuntimeException $exception) {
            $emailVerification->revoke($userId, $tokenHash);
            throw $exception;
        }

        return true;
    }
}
