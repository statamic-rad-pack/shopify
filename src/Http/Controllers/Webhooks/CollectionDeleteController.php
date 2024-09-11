<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Statamic\Facades\Term;
use StatamicRadPack\Shopify\Events;

class CollectionDeleteController extends WebhooksController
{
    public function __invoke(Request $request)
    {
        // Decode data
        $data = json_decode($request->getContent());

        if (! is_object($data) || ! $data->handle) {
            return;
        }

        Events\CollectionDelete::dispatch($data);

        $term = Term::query()
            ->where('slug', $data->handle)
            ->where('taxonomy', config('shopify.taxonomies.collections'))
            ->first();

        if ($term) {
            $term->delete();
        }

        return response()->json([
            'message' => 'Collection has been deleted',
        ], 200);
    }
}
