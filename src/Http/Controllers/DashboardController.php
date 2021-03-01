<?php

namespace Jackabox\Shopify\Http\Controllers;

use Jackabox\Shopify\Jobs\ImportAllProductsJob;
use Statamic\Http\Controllers\CP\CpController;

class DashboardController extends CpController
{
    public function index()
    {
        ImportAllProductsJob::dispatch();

        return view('shopify::cp.dashboard');
    }
}
