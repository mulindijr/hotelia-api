<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Users\UserController;
use App\Constants\Permissions;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/users')->scopeBindings()->group(function () {
    Route::get('/', [UserController::class, 'index'])->middleware('permission:' . Permissions::VIEW_USERS)->name('hotels.users.index');
    Route::post('/', [UserController::class, 'store'])->middleware('permission:' . Permissions::CREATE_USERS)->name('hotels.users.store');
    Route::get('/{user}', [UserController::class, 'show'])->middleware('permission:' . Permissions::VIEW_USERS)->name('hotels.users.show');
    Route::match(['put', 'patch'], '/{user}', [UserController::class, 'update'])->middleware('permission:' . Permissions::UPDATE_USERS)->name('hotels.users.update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('permission:' . Permissions::DELETE_USERS)->name('hotels.users.destroy');
});
