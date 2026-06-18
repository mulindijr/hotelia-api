<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;

Route::prefix('v1')->group(function () {

  Route::prefix('auth')->group(function () {

    // Public auth routes
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);

    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {

      Route::get('/me', [AuthController::class, 'me']);

      Route::post('/logout', [AuthController::class, 'logout']);
    });
  });
});
