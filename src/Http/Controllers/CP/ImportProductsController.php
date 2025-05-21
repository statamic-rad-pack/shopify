<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;
use StatamicRadPack\Shopify\Traits\FetchAllProducts;

class ImportProductsController extends CpController
{
    use FetchAllProducts;

    public function fetchAll(): JsonResponse
    {
        $this->fetchProducts();

        return response()->json([
            'message' => 'Import has been queued.',
        ]);
    }

    public function fetchSingleProduct(Request $request): JsonResponse
    {
        // Pass to import Job.
        ImportSingleProductJob::dispatch((int) $request->get('product'));

        return response()->json([
            'message' => 'Product import has been queued.',
        ]);
    }
}
