<?php

Route::get('/shopify', 'DashboardController@index')
    ->name('shopify.index');

Route::get('/shopify/variants/{product}', 'CP\VariantsController@fetch')
    ->name('shopify.variants.index');

Route::post('/shopify/variants', 'CP\VariantsController@store')
    ->name('shopify.variants.store');

Route::patch('/shopify/variants/{id}', 'CP\VariantsController@update')
    ->name('shopify.variants.edit');
