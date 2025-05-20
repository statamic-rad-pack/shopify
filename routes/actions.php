<?php

use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;
use StatamicRadPack\Shopify\Http\Controllers\Actions\AddressController;
use StatamicRadPack\Shopify\Http\Controllers\Actions\VariantsController;
use StatamicRadPack\Shopify\Http\Controllers\Webhooks;
use StatamicRadPack\Shopify\Http\Middleware\VerifyShopifyHeaders;

Route::name('shopify.')
    ->group(function () {
        Route::prefix('address')
            ->middleware([HandlePrecognitiveRequests::class])
            ->group(function () {
                Route::post('/', [AddressController::class, 'create'])
                    ->name('address.create');

                Route::post('{id}', [AddressController::class, 'store'])
                    ->name('address.store');

                Route::delete('{id}', [AddressController::class, 'destroy'])
                    ->name('address.destroy');
            });

        Route::get('variants/{product}', [VariantsController::class, 'fetch'])
            ->name('variants.fetch');

        Route::prefix('webhook')
            ->withoutMiddleware(['App\Http\Middleware\VerifyCsrfToken', 'Illuminate\Foundation\Http\Middleware\VerifyCsrfToken'])
            ->middleware([VerifyShopifyHeaders::class])
            ->group(function () {
                Route::post('collection/create', [Webhooks\CollectionCreateUpdateController::class, 'create'])
                    ->name('webhook.collection.create');

                Route::post('collection/delete', Webhooks\CollectionDeleteController::class)
                    ->name('webhook.collection.delete');

                Route::post('collection/update', [Webhooks\CollectionCreateUpdateController::class, 'update'])
                    ->name('webhook.collection.update');

                Route::post('customer/create', [Webhooks\CustomerCreateUpdateController::class, 'create'])
                    ->name('webhook.customer.create');

                Route::post('customer/delete', Webhooks\CustomerDeleteController::class)
                    ->name('webhook.customer.delete');

                Route::post('customer/update', [Webhooks\CustomerCreateUpdateController::class, 'update'])
                    ->name('webhook.customer.update');

                Route::post('order', Webhooks\OrderCreateController::class)
                    ->name('webhook.order.created');

                Route::post('product/create', [Webhooks\ProductCreateUpdateController::class, 'create'])
                    ->name('webhook.product.create');

                Route::post('product/delete', Webhooks\ProductDeleteController::class)
                    ->name('webhook.product.delete');

                Route::post('product/update', [Webhooks\ProductCreateUpdateController::class, 'update'])
                    ->name('webhook.product.update');
            });
    });
