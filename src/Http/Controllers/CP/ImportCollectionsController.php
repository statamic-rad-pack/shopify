<?php

namespace StatamicRadPack\Shopify\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Statamic\Http\Controllers\CP\CpController;
use StatamicRadPack\Shopify\Jobs;
use StatamicRadPack\Shopify\Traits\FetchCollections;

class ImportCollectionsController extends CpController
{
    use FetchCollections;

    public function fetchAll(): JsonResponse
    {
        collect([])
            ->merge($this->getManualCollections())
            ->merge($this->getSmartCollections())
            ->each(function ($collection) {
                Jobs\ImportCollectionJob::dispatch($collection)->onQueue(config('shopify.queue'));
            });

        return response()->json([
            'message' => 'Import has been queued.',
        ]);
    }
}
