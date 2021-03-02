<?php

Route::get('/shopify', 'DashboardController@index')
    ->name('shopify.index');

Route::get('/shopify/api/variants/{product}', 'Api\VariantsController@fetch')
    ->name('shopify.variants.index');

Route::post('/shopify/api/variants', 'Api\VariantsController@store')
    ->name('shopify.variants.store');

Route::patch('/shopify/api/variants/{id}', 'Api\VariantsController@update')
    ->name('shopify.variants.edit');
