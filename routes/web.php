<?php

Route::get('/api/shopify/variants/{product}', 'Api\VariantsController@fetch')
    ->name('shopify.variants.index');

