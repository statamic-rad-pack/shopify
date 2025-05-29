<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Shopify\Traits\FetchAllProducts;

class ImportProductsController extends CpController
{
    use FetchAllProducts;

    public function fetchAll(): JsonResponse
    {
        collect($this->fetchProducts())
            ->each(function ($productId) {
                $this->callJob($productId);
            });

        return response()->json([
            'message' => 'Product import has been queued.',
        ]);
    }

    public function fetchSingleProduct(Request $request): JsonResponse
    {
        $this->callJob((int) $request->get('product'));

        return response()->json([
            'message' => 'Product import has been queued.',
        ]);
    }
}
