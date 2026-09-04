<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Shopify\Jobs;
use StatamicRadPack\Shopify\Traits\FetchCollections;

class ImportCollectionsController extends CpController
{
    use FetchCollections;

    public function fetchAll(Request $request): JsonResponse
    {
        if ($request->user()->cannot('access shopify')) {
            abort(403);
        }

        collect($this->getManualCollections())
            ->merge($this->getSmartCollections())
            ->each(function ($collectionId) {
                Jobs\ImportCollectionJob::dispatch($collectionId);
            });

        return response()->json([
            'message' => 'Import has been queued.',
        ]);
    }
}
