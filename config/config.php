<?php

declare(strict_types=1);

return [
    'app_name' => 'Finance Tracker',
    'base_url' => '',
    'app_url' => getenv('APP_URL') ?: 'http://localhost:8080',
    'mail' => [
        'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
        'port' => (int) (getenv('MAIL_PORT') ?: '587'),
        'username' => getenv('MAIL_USERNAME') ?: '',
        'password' => getenv('MAIL_PASSWORD') ?: '',
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: (getenv('MAIL_USERNAME') ?: ''),
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'Finance Tracker',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'finance_tracker',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
];
