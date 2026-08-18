<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Http\Controllers\CP\CpController;

class DashboardController extends CpController
{
    public function index(Request $request)
    {
        if ($request->user()->cannot('access shopify')) {
            abort(403);
        }

        $shopifyUrl = (config('shopify.url')) ? 'https://'.config('shopify.url').'/admin' : null;
        $hasAuthKey = (config('shopify.auth_key') && config('shopify.auth_password'));
        $canRunImport = (config('shopify.url') && ($hasAuthKey || config('shopify.admin_token')));

        return Inertia::render('shopify::Dashboard', [
            'shopifyUrl' => $shopifyUrl,
            'canRunImport' => $canRunImport,
            'importProductsUrl' => cp_route('shopify.products.fetchAll'),
            'importSingleProductUrl' => cp_route('shopify.products.fetch'),
            'importCollectionsUrl' => cp_route('shopify.collections.fetchAll'),
            'productsUrl' => cp_route('shopify.products'),
            'webhookStatusUrl' => cp_route('shopify.webhooks.status'),
        ]);
    }
}
