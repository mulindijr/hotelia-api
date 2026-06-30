<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\AuditController;

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {

  // User management routes
  Route::middleware('permission:update users')->group(function () {
    Route::post('/users/{user}/unlock', [UserController::class, 'unlock'])->name('admin.users.unlock');
  });

  // Audit logs route
  Route::middleware('permission:view activity logs')->group(function () {
    Route::get('/audit-logs', [AuditController::class, 'index'])->name('admin.audit-logs.index');
  });
});
