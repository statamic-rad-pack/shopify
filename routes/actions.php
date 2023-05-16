<?php

use App\Http\Middleware\VerifyCsrfToken;
use Jackabox\Shopify\Http\Controllers\Webhooks\OrderCreateController;
use Jackabox\Shopify\Http\Controllers\Webhooks\ProductCreateUpdateController;
use Jackabox\Shopify\Http\Controllers\Webhooks\ProductDeleteController;
use Illuminate\Support\Facades\Route;

Route::name('shopify.')->group(function () {
    Route::post('/webhook/order', OrderCreateController::class)
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webhook.order.created');

    Route::post('/webhook/product/create', ProductCreateUpdateController::class)
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webhook.product.create');

    Route::post('/webhook/product/update', ProductCreateUpdateController::class)
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webhook.product.update');

    Route::post('/webhook/product/delete', ProductDeleteController::class)
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webhook.product.delete');

    Route::get('/variants/{product}', 'Actions\VariantsController@fetch')
        ->name('variants.fetch');
});

