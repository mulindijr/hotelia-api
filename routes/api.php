<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Security\SecurityController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\AuditController;

Route::prefix('v1')->group(function () {

  // ==========================================
  // AUTHENTICATION MODULE (Public & Protected)
  // ==========================================

  Route::prefix('auth')->group(function () {

    // Public auth routes
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);

    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {

      Route::get('/me', [AuthController::class, 'me']);
      Route::get('/status', [AuthController::class, 'status']);

      Route::post('/logout', [AuthController::class, 'logout']);
      Route::post('/logout-all', [AuthController::class, 'logoutAll']);

      Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
      Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
  });

  // ==========================================
  // SECURITY & AUDIT MODULE (Protected)
  // ==========================================

  Route::middleware('auth:sanctum')->group(function () {

    // Login history route
    Route::get('/login-history', [SecurityController::class, 'loginHistory']);

    // Failed login attempts route
    Route::get('/failed-logins', [SecurityController::class, 'failedLogins'])
      ->middleware(['permission:view activity logs']);
  });

  // ==========================================
  // ADMIN MODULE (Protected)
  // ==========================================

  Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {

    // User management routes
    Route::middleware('permission:update users')->group(function () {
      Route::post('/users/{user}/unlock', [UserController::class, 'unlock']);
    });

    // Audit logs route
    Route::middleware('permission:view activity logs')->group(function () {
      Route::get('/audit-logs', [AuditController::class, 'index']);
    });

  });
});
