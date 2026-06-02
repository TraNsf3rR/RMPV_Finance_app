e-mail:
test@test.lv

Password:
123_Test

TODO:

--General
Ja nonullē datu bāzi, tad nav erroru un neizmet no sistēmas (paliek sessionID)

--Transactions


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
  - assets/
  - index.php
- routes.php

## Setup

1. Configure DB connection:
   - Edit `config/config.php` if needed.

2. Run server:

```bash
php -S localhost:8080 -t public
```

3. Open app:

- http://localhost:8080/index.php/register

4. Database auto setup:

- On first request, the app automatically creates the database, tables, indexes, and default categories.
- Make sure the configured MySQL user has permission to create databases/tables/indexes.

## Notes

- Routes are defined in `routes.php`.
- Because of the built-in PHP server command, route links are served through `index.php` (example: `/index.php/dashboard`).

