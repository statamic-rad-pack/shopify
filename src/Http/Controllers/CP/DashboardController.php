<?php

namespace Jackabox\Shopify\Http\Controllers\CP;

use Statamic\Http\Controllers\CP\CpController;

class DashboardController extends CpController
{
    public function index()
    {
        $shopify_url = (config('shopify.url')) ? 'https://' . config('shopify.url') . '/admin' : null;
        $can_run_import = (config('shopify.url') && config('shopify.auth_key') && config('shopify.auth_password'));

        return view('shopify::cp.dashboard', [
            'shopify_url' => $shopify_url,
            'can_run_import' => $can_run_import
        ]);
    }
}
