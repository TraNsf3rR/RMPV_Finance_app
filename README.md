# Finance Tracker (PHP MVC)

Simple PHP MVC finance tracker with a dark theme and a selectable light theme, built with PHP, HTML, MySQL, CSS, JavaScript, and Chart.js.

## Features

- Dark/light theme toggle with the preference saved in the browser
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
  - Expense category pie chart (selected month)
  - Weekly income and expense totals for the selected month
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
   - The sample configuration uses the MySQL `root` account with no password for local development only. Never deploy with these defaults; use a dedicated database account with a strong password, keep credentials out of source control, and grant the permissions required by the automatic database setup.

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

## Routes

Routes are registered in [`routes.php`](./routes.php). Dashboard, transaction, and category routes require authentication; authentication forms are for guests. Every POST route requires a valid CSRF token.

### Authentication

| Method | Path |
| --- | --- |
| GET | `/login` |
| POST | `/login` |
| GET | `/register` |
| POST | `/register` |
| GET | `/forgot-password` |
| POST | `/forgot-password` |
| GET | `/reset-password` |
| POST | `/reset-password` |
| POST | `/logout` |

### Dashboard and transactions

| Method | Path |
| --- | --- |
| GET | `/` |
| GET | `/dashboard` |
| GET | `/transactions` |
| GET | `/transactions/create` |
| POST | `/transactions/create` |
| GET | `/transactions/edit/{id}` |
| POST | `/transactions/edit/{id}` |
| POST | `/transactions/delete/{id}` |

### Categories

| Method | Path |
| --- | --- |
| GET | `/categories` |
| POST | `/categories/create` |
| POST | `/categories/update/{id}` |
| POST | `/categories/delete/{id}` |

## Manual testing

There is no automated test suite configured. Run the PHP syntax check from PowerShell in the project root:

```powershell
$phpFiles = @(Get-ChildItem app, config, public -Recurse -Filter *.php) + @(Get-Item routes.php)
foreach ($file in $phpFiles) {
    php -l $file.FullName
    if ($LASTEXITCODE -ne 0) {
        throw "PHP syntax check failed: $($file.FullName)"
    }
}
```

Use a disposable account for these checks and remove test transactions and categories afterward:

1. Register with a name of exactly 120 Unicode characters, then try 121. The first should be accepted; the second should show a validation error without truncating the value. Repeat for custom category creation and editing.
2. Add income transactions of `0.10` and `0.20`, then an expense of `0.30`. The dashboard and transaction totals should show a balance of exactly `€0.00`. An amount greater than `9,999,999,999.99` should be rejected.
3. Submit an invalid calendar date such as `2026-02-30` and confirm no transaction is saved. Built-in categories should have no edit/delete controls; direct update/delete requests with a valid CSRF token must not alter them.
4. Remove the `csrf_token` field from a POST form and submit it. Confirm the response is a styled 403 page. Visit an unknown route and confirm the styled 404 page and HTTP 404 status.
5. Request a password reset, use the emailed link to change the password, then try the same link again; it should be rejected. Reset links expire after one hour.
6. In the browser's Network panel, confirm the dashboard loads the local `chart.js-4.5.1` asset and a non-dashboard page does not request Chart.js.

## Notes

- `public/index.php` remains the front controller, but the web server forwards clean URLs such as `/dashboard` to it internally.
- Chart.js 4.5.1 is bundled locally under the MIT license in [`public/assets/js/vendor/chart.js-4.5.1/`](./public/assets/js/vendor/chart.js-4.5.1/) and loaded only on the dashboard.
