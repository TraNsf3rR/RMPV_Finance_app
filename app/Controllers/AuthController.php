<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
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

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = trim((string) ($_POST['password'] ?? ''));
        $confirm = trim((string) ($_POST['password_confirmation'] ?? ''));

        $errors = [];

        if ($name === '' || $email === '' || $password === '' || $confirm === '') {
            if ($name === '') {
                $errors['name'] = 'Name is required.';
            }
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

        $userModel = new User();

        if ($userModel->findByEmail($email)) {
            $errors['email'] = 'Email is already used.';
        }

        if ($errors !== []) {
            $this->backWithErrors('/register', $errors, [
                'name' => $name,
                'email' => $email,
            ]);
        }

        $id = $userModel->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
        Auth::login($id);

        $this->flash('success', 'Account created successfully.');
        $this->redirect('/dashboard');
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
}
