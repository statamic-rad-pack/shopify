<?php

namespace Jackabox\Shopify\Http\Controllers\CP;

use Statamic\Http\Controllers\CP\CpController;

class DashboardController extends CpController
{
    public function index()
    {
        $shopify_url = 'https://' . config('shopify.app_url') . '/admin';

        return view('shopify::cp.dashboard', [
            'shopify_url' => $shopify_url
        ]);
    }
}
