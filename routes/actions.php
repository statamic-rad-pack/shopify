<?php

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use StatamicRadPack\Shopify\Http\Controllers\Actions\AddressController;
use StatamicRadPack\Shopify\Http\Controllers\Actions\VariantsController;
use StatamicRadPack\Shopify\Http\Controllers\Webhooks\CustomerCreateUpdateController;
use StatamicRadPack\Shopify\Http\Controllers\Webhooks\CustomerDeleteController;
use StatamicRadPack\Shopify\Http\Controllers\Webhooks\OrderCreateController;
use StatamicRadPack\Shopify\Http\Controllers\Webhooks\ProductCreateUpdateController;
use StatamicRadPack\Shopify\Http\Controllers\Webhooks\ProductDeleteController;

Route::name('shopify.')
    ->group(function () {
        Route::prefix('/address')
            ->group(function () {
                Route::post('/', [AddressController::class, 'create'])
                    ->name('address.create');

                Route::post('/{id}', [AddressController::class, 'store'])
                    ->name('address.store');

                Route::delete('/{id}', [AddressController::class, 'destroy'])
                    ->name('address.destroy');
            });

        Route::get('/variants/{product}', [VariantsController::class, 'fetch'])
            ->name('variants.fetch');

        Route::prefix('/webhook')
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->group(function () {

                Route::post('/order', OrderCreateController::class)
                    ->name('webhook.order.created');

                Route::post('/customer/create', [CustomerCreateUpdateController::class, 'create'])
                    ->name('webhook.customer.create');

                Route::post('/customer/delete', CustomerDeleteController::class)
                    ->name('webhook.customer.delete');

                Route::post('/customer/update', [CustomerCreateUpdateController::class, 'update'])
                    ->name('webhook.customer.update');

                Route::post('/product/create', [ProductCreateUpdateController::class, 'create'])
                    ->name('webhook.product.create');

                Route::post('/product/delete', ProductDeleteController::class)
                    ->name('webhook.product.delete');

                Route::post('/product/update', [ProductCreateUpdateController::class, 'update'])
                    ->name('webhook.product.update');
            });
    });

