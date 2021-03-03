<?php

use App\Http\Middleware\VerifyCsrfToken;

Route::post('/webhooks/order', 'Webhooks\OrderCreationController@listen')
    ->withoutMiddleware([VerifyCsrfToken::class]);
