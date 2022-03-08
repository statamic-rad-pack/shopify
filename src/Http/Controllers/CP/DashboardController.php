<?php

namespace Jackabox\Shopify\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class DashboardController extends CpController
{

    public function index(Request $request)
    {
        if ($request->user()->cannot('access shopify')) {
            abort(403);
        }

        $shopify_url = (config('shopify.url')) ? 'https://' . config('shopify.url') . '/admin' : null;
        $has_auth_key = (config('shopify.auth_key') && config('shopify.auth_password'));
        $can_run_import = (config('shopify.url') && ($has_auth_key || config('shopify.admin_token')));

        return view('shopify::cp.dashboard', [
            'title' => 'Shopify',
            'shopify_url' => $shopify_url,
            'can_run_import' => $can_run_import
        ]);
    }
}
