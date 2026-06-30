<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Security\SecurityController;

Route::middleware('auth:sanctum')->group(function () {

  // Login history route
  Route::get('/login-history', [SecurityController::class, 'loginHistory'])->name('security.login-history');

  // Failed login attempts route
  Route::get('/failed-logins', [SecurityController::class, 'failedLogins'])
    ->middleware(['permission:view activity logs'])->name('security.failed-logins');
});