<?php

Route::get('/shopify', 'CP\DashboardController@index')
    ->name('shopify.index');

// Dashboard Imports
Route::get('/shopify/import/products/all', 'CP\ImportProductsController@fetchAll')
    ->name('shopify.products.fetchAll');

Route::get('/shopify/import/products/single', 'CP\ImportProductsController@fetchSingleProduct')
    ->name('shopify.products.fetch');

Route::get('/shopify/import/collections/all', 'CP\ImportCollectionsController@fetchAll')
    ->name('shopify.collections.fetchAll');

// Others
Route::get('/shopify/products', 'CP\ProductsController@index')
    ->name('shopify.products');

Route::get('/shopify/variants/{product}', 'CP\VariantsController@fetch')
    ->name('shopify.variants.index');

Route::post('/shopify/variants', 'CP\VariantsController@store')
    ->name('shopify.variants.store');

Route::patch('/shopify/variants/{id}', 'CP\VariantsController@update')
    ->name('shopify.variants.edit');


