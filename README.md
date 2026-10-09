# Finance Tracker (PHP MVC)

Simple dark-themed finance tracker built with PHP, HTML, MySQL, CSS, JavaScript, and Chart.js.

## Features

- Register and login
- Dashboard:
  - Total balance (income - expenses)
  - Monthly income
  - Monthly expenses
- Transaction management:
  - Add, edit, delete
  - Category assignment
- Category management:
  - Separate income and expense category management
  - Custom category add/edit/delete (per user)
  - Built-in income: Work, Freelance, Side-job
  - Built-in expense: Food, Bills, Transportation, Entertainment
- Charts and reports:
  - Expense category pie chart (current month)
  - Monthly expense line chart (last 12 months)
- Search and filters:
  - Date range
  - Category
  - Type
  - Description/category text

## Project Structure (MVC)

- app/
  - Core/ (Router, Controller, Database, Auth)
  - Controllers/
  - Models/
  - Views/
- config/
- database/
- public/
  - .htaccess (Apache URL rewriting)
  - assets/
  - index.php
  - router.php (optional custom PHP built-in server routing)
- routes.php

## Setup

1. Configure DB connection:
   - Edit `config/config.php` if needed.

2. Install dependencies:

```bash
composer install
```

3. Configure Gmail for password-reset email:
   1. Enable 2-Step Verification for the Gmail account that will send email.
   2. Open [Google App Passwords](https://myaccount.google.com/apppasswords), create an app password, and keep it available. Use this app password rather than your regular Google password.
   3. Stop any currently running development server with `Ctrl+C`.
   4. In PowerShell, from the project directory, set the email environment variables. Enter the generated app password without spaces when prompted:

```powershell
$env:APP_URL = 'http://localhost:8080'
$env:MAIL_USERNAME = Read-Host 'Gmail address'
$env:MAIL_FROM_ADDRESS = $env:MAIL_USERNAME
$securePassword = Read-Host 'Google app password' -AsSecureString
$env:MAIL_PASSWORD = [System.Net.NetworkCredential]::new('', $securePassword).Password
```

These variables apply only to the current PowerShell session. Keep using this same window to start the server. The app defaults to Gmail SMTP at `smtp.gmail.com:587` with STARTTLS; `MAIL_HOST` and `MAIL_PORT` only need to be set if you use a different SMTP server. Never put the app password in source control or share it.

4. Run server:

```bash
php -S localhost:8080 -t public
```

5. Open app:

- http://localhost:8080/register

To test password-reset email, register an account, open `/forgot-password`, and submit that account's email. Check the inbox and spam folder. The reset link expires after one hour. The generic response does not confirm whether an account exists.

The PHP built-in server can serve this app using `public/index.php` as its front controller; the separate `public/router.php` script is optional. For Apache, point the document root at `public/` and enable `mod_rewrite`; `public/.htaccess` forwards application routes to the front controller.

6. Database auto setup:

- On first database request, the app automatically creates the database, tables, indexes, and default categories, including the password-reset token table.
- Make sure the configured MySQL user has permission to create databases/tables/indexes.

## Notes

- Routes are defined in `routes.php`.
- `public/index.php` remains the front controller, but the web server forwards clean URLs such as `/dashboard` to it internally.
