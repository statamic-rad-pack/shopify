<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use StatamicRadPack\Shopify\Jobs\ImportSingleProductJob;

class ProductCreateUpdateController extends WebhooksController
{
    public function __invoke(Request $request)
    {
        $hmac_header = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        $verified = $this->verify($data, $hmac_header);

        if (! $verified) {
            return response()->json(['error' => true], 403);
        }

        // Decode data
        $data = json_decode($data, true);

        // Dispatch job
        ImportSingleProductJob::dispatch($data)->onQueue(config('shopify.queue'));

        return response()->json([
            'message' => 'Product has been dispatched to the queue for update',
        ], 200);
    }
}
