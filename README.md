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
  - router.php (PHP built-in server routing)
- routes.php

## Setup

1. Configure DB connection:
   - Edit `config/config.php` if needed.

2. Run server:

```bash
php -S localhost:8080 -t public public/router.php
```

3. Open app:

- http://localhost:8080/register

The built-in server uses `public/router.php` to forward application routes to `index.php` while serving existing asset files directly. For Apache, point the document root at `public/` and enable `mod_rewrite`; `public/.htaccess` forwards application routes to the front controller.

4. Database auto setup:

- On first request, the app automatically creates the database, tables, indexes, and default categories.
- Make sure the configured MySQL user has permission to create databases/tables/indexes.

## Notes

- Routes are defined in `routes.php`.
- `public/index.php` remains the front controller, but the web server forwards clean URLs such as `/dashboard` to it internally.
