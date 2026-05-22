<?php

use Illuminate\Support\Facades\Route;
use Molitor\Purchase\Http\Controllers\Api\PurchaseApiController;
use Molitor\Purchase\Http\Controllers\Api\PurchaseExtraItemTypeApiController;
use Molitor\Purchase\Http\Controllers\Api\PurchaseStatusApiController;

Route::prefix('admin/purchase')
    ->middleware(['api', 'auth:sanctum'])
    ->name('purchase.')
    ->group(function () {
        Route::post('purchases/{purchase}/status', [PurchaseApiController::class, 'updateStatus']);
        Route::post('purchases/{purchase}/close', [PurchaseApiController::class, 'close']);
        Route::resource('purchases', PurchaseApiController::class);
        Route::resource('purchase-statuses', PurchaseStatusApiController::class);
        Route::resource('purchase-extra-item-types', PurchaseExtraItemTypeApiController::class);
    });
