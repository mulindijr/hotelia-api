<?php

use App\Constants\Permissions;
use App\Http\Controllers\Api\V1\Hotels\HotelController;
use App\Http\Controllers\Api\V1\Hotels\HotelSettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Rooms\RoomTypeImageController;
use App\Http\Controllers\Api\V1\Billing\HotelPaymentController;

Route::middleware(['auth:sanctum'])->prefix('hotels')->group(function () {

    Route::get('/', [HotelController::class, 'index'])->name('hotels.index');

    Route::post('/', [HotelController::class, 'store'])->middleware('permission:'.Permissions::CREATE_HOTELS)->name('hotels.store');

    Route::get('/{hotel}', [HotelController::class, 'show'])->middleware('permission:'.Permissions::VIEW_HOTELS)->name('hotels.show');

    Route::match(['put', 'patch'], '/{hotel}', [HotelController::class, 'update'])->middleware('permission:'.Permissions::UPDATE_HOTELS)->name('hotels.update');

    Route::delete('/{hotel}', [HotelController::class, 'destroy'])->middleware('permission:'.Permissions::DELETE_HOTELS)->name('hotels.destroy');

    // Hotel settings nested routes
    Route::get('/{hotel}/settings', [HotelSettingController::class, 'show'])
        ->middleware('permission:'.Permissions::VIEW_HOTELS)
        ->name('hotels.settings.show');

    Route::match(['put', 'patch'], '/{hotel}/settings', [HotelSettingController::class, 'update'])
        ->middleware('permission:'.Permissions::UPDATE_HOTELS)
        ->name('hotels.settings.update');


    // Room Type Images
    Route::get('/{hotel}/room-types/{room_type}/images', [RoomTypeImageController::class, 'index'])
        ->middleware('permission:'.Permissions::VIEW_HOTELS)->name('hotels.room-types.images.index');
    Route::post('/{hotel}/room-types/{room_type}/images', [RoomTypeImageController::class, 'store'])
        ->middleware('permission:'.Permissions::UPDATE_HOTELS)->name('hotels.room-types.images.store');
    Route::post('/{hotel}/room-types/{room_type}/images/{image}/primary', [RoomTypeImageController::class, 'setPrimary'])
        ->middleware('permission:'.Permissions::UPDATE_HOTELS)->name('hotels.room-types.images.primary');
    Route::delete('/{hotel}/room-types/{room_type}/images/{image}', [RoomTypeImageController::class, 'destroy'])
        ->middleware('permission:'.Permissions::UPDATE_HOTELS)->name('hotels.room-types.images.destroy');
    // Hotel-wide payments
    Route::get('/{hotel}/payments', [HotelPaymentController::class, 'index'])->name('hotels.payments.index');
});
