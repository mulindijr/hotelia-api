<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Hotels\MaintenanceController;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/maintenance')->group(function () {
    Route::get('/', [MaintenanceController::class, 'index'])->middleware('permission:view maintenance')->name('maintenance.index');
    Route::post('/', [MaintenanceController::class, 'store'])->middleware('permission:manage maintenance')->name('maintenance.store');
    Route::get('/{maintenanceRequest}', [MaintenanceController::class, 'show'])->middleware('permission:view maintenance')->name('maintenance.show');
    Route::put('/{maintenanceRequest}', [MaintenanceController::class, 'update'])->middleware('permission:manage maintenance')->name('maintenance.update');
    Route::delete('/{maintenanceRequest}', [MaintenanceController::class, 'destroy'])->middleware('permission:manage maintenance')->name('maintenance.destroy');
});
