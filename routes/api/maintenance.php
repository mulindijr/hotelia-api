<?php

use App\Constants\Permissions;
use App\Http\Controllers\Api\V1\Maintenance\MaintenanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/maintenance')->scopeBindings()->group(function () {
    Route::get('/', [MaintenanceController::class, 'index'])->middleware('permission:'.Permissions::VIEW_MAINTENANCE)->name('maintenance.index');
    Route::post('/', [MaintenanceController::class, 'store'])->middleware('permission:'.Permissions::MANAGE_MAINTENANCE)->name('maintenance.store');
    Route::get('/{maintenanceRequest}', [MaintenanceController::class, 'show'])->middleware('permission:'.Permissions::VIEW_MAINTENANCE)->name('maintenance.show');
    Route::match(['put', 'patch'], '/{maintenanceRequest}', [MaintenanceController::class, 'update'])->middleware('permission:'.Permissions::MANAGE_MAINTENANCE)->name('maintenance.update');
    Route::delete('/{maintenanceRequest}', [MaintenanceController::class, 'destroy'])->middleware('permission:'.Permissions::MANAGE_MAINTENANCE)->name('maintenance.destroy');
});
