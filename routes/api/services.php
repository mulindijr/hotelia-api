<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Services\ServiceController;
use App\Constants\Permissions;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/services')->scopeBindings()->group(function () {
    Route::get('/', [ServiceController::class, 'index'])->middleware('permission:' . Permissions::VIEW_SERVICES)->name('services.index');
    Route::post('/', [ServiceController::class, 'store'])->middleware('permission:' . Permissions::CREATE_SERVICES)->name('services.store');
    Route::get('/{service}', [ServiceController::class, 'show'])->middleware('permission:' . Permissions::VIEW_SERVICES)->name('services.show');
    Route::match(['put', 'patch'], '/{service}', [ServiceController::class, 'update'])->middleware('permission:' . Permissions::UPDATE_SERVICES)->name('services.update');
    Route::delete('/{service}', [ServiceController::class, 'destroy'])->middleware('permission:' . Permissions::DELETE_SERVICES)->name('services.destroy');
});
