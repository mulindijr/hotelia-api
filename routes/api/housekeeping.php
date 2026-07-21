<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Housekeeping\HousekeepingController;
use App\Constants\Permissions;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/housekeeping')->scopeBindings()->group(function () {
    Route::get('/', [HousekeepingController::class, 'index'])->middleware('permission:' . Permissions::VIEW_HOUSEKEEPING)->name('housekeeping.index');
    Route::post('/', [HousekeepingController::class, 'store'])->middleware('permission:' . Permissions::MANAGE_HOUSEKEEPING)->name('housekeeping.store');
    Route::get('/{task}', [HousekeepingController::class, 'show'])->middleware('permission:' . Permissions::VIEW_HOUSEKEEPING)->name('housekeeping.show');
    Route::put('/{task}', [HousekeepingController::class, 'update'])->middleware('permission:' . Permissions::MANAGE_HOUSEKEEPING)->name('housekeeping.update');
    Route::delete('/{task}', [HousekeepingController::class, 'destroy'])->middleware('permission:' . Permissions::MANAGE_HOUSEKEEPING)->name('housekeeping.destroy');
});
