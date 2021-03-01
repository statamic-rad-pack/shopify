<?php

Route::get('/shopify', 'DashboardController@index')
    ->name('shopify.index');

Route::get('/shopify/api/variants/{product}', 'Api\VariantsController@fetch')
    ->name('shopify.variants.index');
