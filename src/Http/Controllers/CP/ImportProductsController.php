<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopify\Clients\Rest;
use Statamic\Support\Arr;
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
        // Fetch Single Product
        $client = app(Rest::class);
        $response = $client->get(path: 'products/'.$request->get('product'));

        if ($response->getStatusCode() != 200) {
            return response()->json([
                'message' => 'Failed to retrieve product',
            ]);
        }

        $product = Arr::get($response->getDecodedBody(), 'product', []);

        if (!$product) {
            return response()->json([
                'message' => 'Failed to retrieve product',
            ]);
        }

        // Pass to import Job.
        ImportSingleProductJob::dispatch($product)->onQueue(config('shopify.queue'));

        return response()->json([
            'message' => 'Product import has been queued.',
        ]);
    }
}
