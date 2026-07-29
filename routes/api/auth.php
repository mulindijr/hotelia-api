<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    // Public auth routes — rate limited to 5 attempts/min per IP
    Route::middleware('throttle:auth-login')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])->name('auth.forgot-password');
        Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('auth.reset-password');
    });

    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        Route::get('/status', [AuthController::class, 'status'])->name('auth.status');

        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');

        // Sensitive token/password operations — rate limited to 10 attempts/min per user
        Route::middleware('throttle:auth-sensitive')->group(function () {
            Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->name('auth.refresh-token');
            Route::post('/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');
        });
    });
});
