<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Guests\GuestController;

Route::middleware(['auth:sanctum'])->prefix('guests')->group(function () {
    Route::get('/', [GuestController::class, 'index'])->middleware('permission:view guests')->name('guests.index');
    Route::post('/', [GuestController::class, 'store'])->middleware('permission:create guests')->name('guests.store');
    Route::get('/{guest}', [GuestController::class, 'show'])->middleware('permission:view guests')->name('guests.show');
    Route::put('/{guest}', [GuestController::class, 'update'])->middleware('permission:update guests')->name('guests.update');
    Route::delete('/{guest}', [GuestController::class, 'destroy'])->middleware('permission:delete guests')->name('guests.destroy');
});
