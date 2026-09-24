<?php

use App\Constants\Permissions;
use App\Http\Controllers\Api\V1\Admin\AuditController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\PermissionController;

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {

    // Roles and Permissions routes
    // Currently relying on general admin access, but could be scoped to manage_users or manage_roles
    Route::middleware('permission:'.Permissions::UPDATE_USERS)->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::get('permissions', [PermissionController::class, 'index'])->name('admin.permissions.index');
    });

    // User management routes
    Route::middleware('permission:'.Permissions::UPDATE_USERS)->group(function () {
        Route::post('/users/{user}/unlock', [UserController::class, 'unlock'])->name('admin.users.unlock');
    });

    // Audit logs route
    Route::middleware('permission:'.Permissions::VIEW_ACTIVITY_LOGS)->group(function () {
        Route::get('/audit-logs', [AuditController::class, 'index'])->name('admin.audit-logs.index');
    });
});
