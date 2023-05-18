<?php

use Jackabox\Shopify\Http\Controllers\CP\DashboardController;
use Jackabox\Shopify\Http\Controllers\CP\ImportProductsController;
use Jackabox\Shopify\Http\Controllers\CP\ImportCollectionsController;
use Jackabox\Shopify\Http\Controllers\CP\ProductsController;
use Jackabox\Shopify\Http\Controllers\CP\VariantsController;

Route::get('/shopify', [DashboardController::class, 'index'])
    ->name('shopify.index');

// Dashboard Imports
Route::get('/shopify/import/products/all', [ImportProductsController::class, 'fetchAll'])
    ->name('shopify.products.fetchAll');

Route::get('/shopify/import/products/single', [ImportProductsController::class, 'fetchSingleProduct'])
    ->name('shopify.products.fetch');

Route::get('/shopify/import/collections/all', [ImportCollectionsController::class, 'fetchAll'])
    ->name('shopify.collections.fetchAll');

// Others
Route::get('/shopify/products', [ProductsController::class, 'index'])
    ->name('shopify.products');

Route::get('/shopify/variants/{product}', [VariantsController::class, 'fetch'])
    ->name('shopify.variants.index');

Route::post('/shopify/variants', [VariantsController::class, 'store'])
    ->name('shopify.variants.store');

Route::patch('/shopify/variants/{id}', [VariantsController::class, 'update'])
    ->name('shopify.variants.edit');


