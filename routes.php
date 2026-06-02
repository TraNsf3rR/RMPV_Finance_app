<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\DashboardController;
use App\Controllers\TransactionController;

$router->get('/', [DashboardController::class, 'index']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index']);

$router->get('/transactions', [TransactionController::class, 'index']);
$router->get('/transactions/create', [TransactionController::class, 'createForm']);
$router->post('/transactions/create', [TransactionController::class, 'create']);
$router->get('/transactions/edit/{id}', [TransactionController::class, 'editForm']);
$router->post('/transactions/edit/{id}', [TransactionController::class, 'edit']);
$router->post('/transactions/delete/{id}', [TransactionController::class, 'delete']);

$router->get('/categories', [CategoryController::class, 'index']);
$router->post('/categories/create', [CategoryController::class, 'create']);
$router->post('/categories/update/{id}', [CategoryController::class, 'update']);
$router->post('/categories/delete/{id}', [CategoryController::class, 'delete']);
