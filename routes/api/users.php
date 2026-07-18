<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Hotels\UserController;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->middleware('permission:view users')->name('hotels.users.index');
    Route::post('/', [UserController::class, 'store'])->middleware('permission:create users')->name('hotels.users.store');
    Route::get('/{user}', [UserController::class, 'show'])->middleware('permission:view users')->name('hotels.users.show');
    Route::put('/{user}', [UserController::class, 'update'])->middleware('permission:update users')->name('hotels.users.update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('permission:delete users')->name('hotels.users.destroy');
});
