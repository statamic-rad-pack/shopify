<?php

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::name('shopify.')->group(function () {
    Route::post('/webhook/order', 'Webhooks\OrderCreationController@listen')
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webhook.order.created');

    Route::post('/webhook/product/create', 'Webhooks\ProductCreateUpdateController')
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webhook.product.create');

    Route::post('/webhook/product/update', 'Webhooks\ProductCreateUpdateController')
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webhook.product.update');

    Route::post('/webhook/product-deletion', 'Webhooks\ProductDeletionController@listen')
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webhook.product.deleted');

    Route::get('/variants/{product}', 'Actions\VariantsController@fetch')
        ->name('variants.fetch');
});

