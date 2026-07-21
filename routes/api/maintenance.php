<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Hotels\MaintenanceController;
use App\Constants\Permissions;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/maintenance')->scopeBindings()->group(function () {
    Route::get('/', [MaintenanceController::class, 'index'])->middleware('permission:' . Permissions::VIEW_MAINTENANCE)->name('maintenance.index');
    Route::post('/', [MaintenanceController::class, 'store'])->middleware('permission:' . Permissions::MANAGE_MAINTENANCE)->name('maintenance.store');
    Route::get('/{maintenanceRequest}', [MaintenanceController::class, 'show'])->middleware('permission:' . Permissions::VIEW_MAINTENANCE)->name('maintenance.show');
    Route::put('/{maintenanceRequest}', [MaintenanceController::class, 'update'])->middleware('permission:' . Permissions::MANAGE_MAINTENANCE)->name('maintenance.update');
    Route::delete('/{maintenanceRequest}', [MaintenanceController::class, 'destroy'])->middleware('permission:' . Permissions::MANAGE_MAINTENANCE)->name('maintenance.destroy');
});
