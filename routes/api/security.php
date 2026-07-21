<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Security\SecurityController;
use App\Constants\Permissions;

Route::middleware('auth:sanctum')->group(function () {

  // Login history route
  Route::get('/login-history', [SecurityController::class, 'loginHistory'])->name('security.login-history');

  // Failed login attempts route
  Route::get('/failed-logins', [SecurityController::class, 'failedLogins'])
    ->middleware(['permission:' . Permissions::VIEW_ACTIVITY_LOGS])->name('security.failed-logins');
});