<?php

use App\Http\Middleware\VerifyCsrfToken;

Route::post('/webhooks/order', 'Webhooks\OrderCreationController@listen')
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::post('/webhooks/product-deletion', 'Webhooks\ProductDeletionController@listen')
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::get('/variants/{product}', 'Actions\VariantsController@fetch')
    ->name('shopify.variants.fetch');

