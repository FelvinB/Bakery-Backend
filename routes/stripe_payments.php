<?php

use App\Http\Controllers\Api\StripePaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('payments/stripe')->group(function () {
    Route::post('/', [StripePaymentController::class, 'store']);
    Route::get('/{reference}', [StripePaymentController::class, 'show']);
    Route::post('/webhook', [StripePaymentController::class, 'webhook']);
});
