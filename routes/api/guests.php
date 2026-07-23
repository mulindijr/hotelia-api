<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Guests\GuestController;
use App\Constants\Permissions;

Route::middleware(['auth:sanctum'])->prefix('guests')->group(function () {
    Route::get('/', [GuestController::class, 'index'])->middleware('permission:' . Permissions::VIEW_GUESTS)->name('guests.index');
    Route::post('/', [GuestController::class, 'store'])->middleware('permission:' . Permissions::CREATE_GUESTS)->name('guests.store');
    Route::get('/{guest}', [GuestController::class, 'show'])->middleware('permission:' . Permissions::VIEW_GUESTS)->name('guests.show');
    Route::get('/{guest}/bookings', [GuestController::class, 'bookings'])->middleware('permission:' . Permissions::VIEW_BOOKINGS)->name('guests.bookings');
    Route::match(['put', 'patch'], '/{guest}', [GuestController::class, 'update'])->middleware('permission:' . Permissions::UPDATE_GUESTS)->name('guests.update');
    Route::delete('/{guest}', [GuestController::class, 'destroy'])->middleware('permission:' . Permissions::DELETE_GUESTS)->name('guests.destroy');
});
