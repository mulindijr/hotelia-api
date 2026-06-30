<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;

Route::prefix('auth')->group(function () {

  // Public auth routes
  Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

  Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])->name('auth.forgot-password');

  Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('auth.reset-password');

  // Protected auth routes
  Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::get('/status', [AuthController::class, 'status'])->name('auth.status');

    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');

    Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->name('auth.refresh-token');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');
  });
});
