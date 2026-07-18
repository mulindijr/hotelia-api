<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Hotels\InvoiceController;
use App\Http\Controllers\Api\V1\Hotels\PaymentController;

Route::middleware(['auth:sanctum'])->prefix('hotels/{hotel}/bookings/{booking}')->group(function () {
    Route::get('/invoice', [InvoiceController::class, 'show'])->name('bookings.invoice.show');
    Route::post('/invoice/regenerate', [InvoiceController::class, 'regenerate'])->name('bookings.invoice.regenerate');
    Route::get('/payments', [PaymentController::class, 'index'])->name('bookings.payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('bookings.payments.store');
    Route::post('/payments/{payment}/status', [PaymentController::class, 'updateStatus'])->name('bookings.payments.status');
});
