<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Shopify\Jobs;

class ImportCollectionsController extends CpController
{
    public function fetchAll(Request $request): JsonResponse
    {
        if ($request->user()->cannot('access shopify')) {
            abort(403);
        }

        Jobs\ImportAllCollectionsJob::dispatch();

        return response()->json([
            'message' => 'Import has been queued.',
        ]);
    }
}
