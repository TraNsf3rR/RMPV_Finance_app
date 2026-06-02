<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

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

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = trim((string) ($_POST['password'] ?? ''));
        $confirm = trim((string) ($_POST['password_confirmation'] ?? ''));

        $errors = [];

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if ($password === '') {
            $errors['password'] = 'New password is required.';
        } else {
            $passwordError = $this->validatePassword($password);
            if ($passwordError !== null) {
                $errors['password'] = $passwordError;
            }
        }

        if ($confirm === '') {
            $errors['password_confirmation'] = 'Password confirmation is required.';
        } elseif ($password !== $confirm) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }

        $userModel = new User();
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && !$userModel->findByEmail($email)) {
            $errors['email'] = 'No account was found for this email.';
        }

        if ($errors !== []) {
            $this->backWithErrors('/forgot-password', $errors, [
                'email' => $email,
            ]);
        }

        $userModel->updatePasswordByEmail($email, password_hash($password, PASSWORD_DEFAULT));
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
}
