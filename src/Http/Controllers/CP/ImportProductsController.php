<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Shopify\Activity\Activity;
use StatamicRadPack\Shopify\Activity\ActivityLog;
use StatamicRadPack\Shopify\Jobs;

class ImportProductsController extends CpController
{
    public function fetchAll(Request $request): JsonResponse
    {
        if ($request->user()->cannot('access shopify')) {
            abort(403);
        }

        Jobs\ImportAllProductsJob::dispatch();

        return response()->json([
            'message' => 'Product import has been queued.',
        ]);
    }

    public function fetchSingleProduct(Request $request): JsonResponse
    {
        if ($request->user()->cannot('access shopify')) {
            abort(403);
        }

        ActivityLog::queueProductImport((int) $request->get('product'), source: Activity::SOURCE_CP);

        return response()->json([
            'message' => 'Product import has been queued.',
        ]);
    }
}
