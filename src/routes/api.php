<?php

use Illuminate\Support\Facades\Route;
use Molitor\Purchase\Http\Controllers\Api\PurchaseApiController;
use Molitor\Purchase\Http\Controllers\Api\PurchaseExtraItemTypeApiController;
use Molitor\Purchase\Http\Controllers\Api\PurchaseStatusApiController;

Route::prefix('admin/purchase')
    ->middleware(['api', 'auth:sanctum'])
    ->name('purchase.')
    ->group(function () {
        Route::get('purchases/requirements/list', [PurchaseApiController::class, 'requirements'])->middleware('permission:purchase');
        Route::post('purchases/{purchase}/status', [PurchaseApiController::class, 'updateStatus'])->middleware('permission:purchase_status');
        Route::post('purchases/{purchase}/close', [PurchaseApiController::class, 'close'])->middleware('permission:purchase');
        Route::resource('purchases', PurchaseApiController::class)->middleware('permission:purchase');
        Route::resource('purchase-statuses', PurchaseStatusApiController::class)->middleware('permission:purchase_status');
        Route::resource('purchase-extra-item-types', PurchaseExtraItemTypeApiController::class)->middleware('permission:purchase');
    });
