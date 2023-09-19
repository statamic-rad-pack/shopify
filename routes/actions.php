<?php

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use StatamicRadPack\Shopify\Http\Controllers\Actions\VariantsController;
use StatamicRadPack\Shopify\Http\Controllers\Webhooks\OrderCreateController;
use StatamicRadPack\Shopify\Http\Controllers\Webhooks\ProductCreateUpdateController;
use StatamicRadPack\Shopify\Http\Controllers\Webhooks\ProductDeleteController;

Route::name('shopify.')->group(function () {
    Route::post('/webhook/order', OrderCreateController::class)
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webhook.order.created');

    Route::post('/webhook/product/create', [ProductCreateUpdateController::class, 'create'])
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webhook.product.create');

    Route::post('/webhook/product/update', [ProductCreateUpdateController::class, 'update'])
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webhook.product.update');

    Route::post('/webhook/product/delete', ProductDeleteController::class)
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webhook.product.delete');

    Route::get('/variants/{product}', [VariantsController::class, 'fetch'])
        ->name('variants.fetch');
});

