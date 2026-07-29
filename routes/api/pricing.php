<?php

use App\Http\Controllers\Api\V1\Pricing\PricingRuleController;
use App\Http\Controllers\Api\V1\Pricing\RatePlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Rate Plans
    Route::get('/hotels/{hotel}/rate-plans', [RatePlanController::class, 'index']);
    Route::post('/hotels/{hotel}/rate-plans', [RatePlanController::class, 'store']);
    Route::get('/hotels/{hotel}/rate-plans/{rate_plan}', [RatePlanController::class, 'show']);
    Route::put('/hotels/{hotel}/rate-plans/{rate_plan}', [RatePlanController::class, 'update']);
    Route::delete('/hotels/{hotel}/rate-plans/{rate_plan}', [RatePlanController::class, 'destroy']);

    // Pricing Rules
    Route::get('/hotels/{hotel}/pricing-rules', [PricingRuleController::class, 'index']);
    Route::post('/hotels/{hotel}/pricing-rules', [PricingRuleController::class, 'store']);
    Route::get('/hotels/{hotel}/pricing-rules/{pricing_rule}', [PricingRuleController::class, 'show']);
    Route::put('/hotels/{hotel}/pricing-rules/{pricing_rule}', [PricingRuleController::class, 'update']);
    Route::delete('/hotels/{hotel}/pricing-rules/{pricing_rule}', [PricingRuleController::class, 'destroy']);
});
