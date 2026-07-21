<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Hotels\BookingController;
use App\Constants\Permissions;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/bookings')->scopeBindings()->group(function () {
    Route::get('/', [BookingController::class, 'index'])->middleware('permission:' . Permissions::VIEW_BOOKINGS)->name('bookings.index');
    Route::post('/', [BookingController::class, 'store'])->middleware('permission:' . Permissions::CREATE_BOOKINGS)->name('bookings.store');
    Route::get('/{booking}', [BookingController::class, 'show'])->middleware('permission:' . Permissions::VIEW_BOOKINGS)->name('bookings.show');
    Route::put('/{booking}', [BookingController::class, 'update'])->middleware('permission:' . Permissions::UPDATE_BOOKINGS)->name('bookings.update');
    Route::post('/{booking}/cancel', [BookingController::class, 'cancel'])->middleware('permission:' . Permissions::CANCEL_BOOKINGS)->name('bookings.cancel');
    Route::post('/{booking}/check-in', [BookingController::class, 'checkIn'])->middleware('permission:' . Permissions::CHECK_IN_GUESTS)->name('bookings.check-in');
    Route::post('/{booking}/check-out', [BookingController::class, 'checkOut'])->middleware('permission:' . Permissions::CHECK_OUT_GUESTS)->name('bookings.check-out');
});
