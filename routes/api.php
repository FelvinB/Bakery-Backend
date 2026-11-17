<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\MarketingController;
use App\Http\Controllers\Api\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::apiResource('customers', CustomerController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('sales', SaleController::class);
Route::apiResource('inventory', InventoryController::class);
Route::apiResource('marketing', MarketingController::class);

Route::prefix('analytics')->group(function () {
    Route::get('/dashboard', [AnalyticsController::class, 'dashboard']);
    Route::get('/descriptive', [AnalyticsController::class, 'descriptive']);
    Route::get('/predictive', [AnalyticsController::class, 'predictive']);
    Route::get('/prescriptive', [AnalyticsController::class, 'prescriptive']);
});
