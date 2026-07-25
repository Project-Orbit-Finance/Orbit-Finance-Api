<?php

declare(strict_types=1);

use Orbit\Finance\Http\Controllers\Auth\AuthController;
use Orbit\Finance\Http\Controllers\Transaction\TransactionController;

return [
    ['POST', '/auth/register', [AuthController::class, 'register']],
    ['POST', '/auth/login', [AuthController::class, 'login']],
    ['POST', '/auth/logout', [AuthController::class, 'logout']],
    ['POST', '/auth/refresh', [AuthController::class, 'refresh']],
    ['POST', '/auth/forgot-password', [AuthController::class, 'forgotPassword']],
    ['POST', '/auth/reset-password', [AuthController::class, 'resetPassword']],
    ['GET', '/transactions', [TransactionController::class, 'index']],
    ['POST', '/transactions', [TransactionController::class, 'store']],
    ['GET', '/transactions/{transaction}', [TransactionController::class, 'show']],
    ['PATCH', '/transactions/{transaction}', [TransactionController::class, 'update']],
    ['DELETE', '/transactions/{transaction}', [TransactionController::class, 'destroy']],
    ['PATCH', '/transactions/{transaction}/category', [TransactionController::class, 'updateCategory']],
    ['GET', '/dashboard/summary', [TransactionController::class, 'summary']],
];
