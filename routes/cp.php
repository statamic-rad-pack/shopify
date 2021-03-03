<?php

Route::get('/shopify', 'CP\DashboardController@index')
    ->name('shopify.index');

Route::get('/shopify/import/fetch-all', 'CP\ImportProductsController@fetchAll')
    ->name('shopify.products.fetchAll');

Route::get('/shopify/import/fetch', 'CP\ImportProductsController@fetchSingleProduct')
    ->name('shopify.products.fetch');

Route::get('/shopify/products', 'CP\ProductsController@index')
    ->name('shopify.products');

Route::get('/shopify/variants/{product}', 'CP\VariantsController@fetch')
    ->name('shopify.variants.index');

Route::post('/shopify/variants', 'CP\VariantsController@store')
    ->name('shopify.variants.store');

Route::patch('/shopify/variants/{id}', 'CP\VariantsController@update')
    ->name('shopify.variants.edit');
