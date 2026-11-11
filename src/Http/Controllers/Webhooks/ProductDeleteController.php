<?php

namespace StatamicRadPack\Shopify\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use StatamicRadPack\Shopify\Events;
use StatamicRadPack\Shopify\Support\StoreConfig;

class ProductDeleteController extends WebhooksController
{
    public function __invoke(Request $request)
    {
        // Decode data
        $data = json_decode($request->getContent());

        if (! is_object($data) || empty($data->id)) {
            return;
        }

        Events\ProductDelete::dispatch($data);

        $storeHandle = $request->attributes->get('shopify_store_handle');
        $isMultiStore = $storeHandle && StoreConfig::isMultiStore();

        // Products live in the default site, except in localized multi-store mode
        // where each store maps to its own site
        $site = $isMultiStore && StoreConfig::getMode() === 'localized'
            ? (StoreConfig::findByHandle($storeHandle)['site'] ?? null)
            : Site::default()->handle();

        $productEntry = Entry::query()
            ->where('collection', config('shopify.collection_handle', 'products'))
            ->where('product_id', $data->id)
            ->when($site, fn ($query) => $query->where('site', $site))
            ->first();

        if (! $productEntry || ! $productEntry->slug()) {
            return $this->deleted();
        }

        $variants = Entry::query()
            ->where('collection', 'variants')
            ->where('product_slug', $productEntry->slug())
            ->where('site', $productEntry->locale())
            ->get();

        // In unified multi-store mode the entries are shared between stores, so only
        // drop this store's data unless no other store still sells the product
        if ($isMultiStore && StoreConfig::getMode() === 'unified' && $this->otherStoresRemain($variants, $storeHandle)) {
            return $this->deleted();
        }

        foreach ($variants as $variant) {
            $variant->deleteDescendants();
            $variant->delete();
        }

        $productEntry->deleteDescendants();
        $productEntry->delete();

        return $this->deleted();
    }

    /**
     * Remove the store's data from each variant, returning whether any other store still has data.
     */
    private function otherStoresRemain($variants, string $storeHandle): bool
    {
        $remaining = false;

        foreach ($variants as $variant) {
            $storeData = $variant->get('multi_store_data', []);
            unset($storeData[$storeHandle]);

            if ($storeData) {
                $remaining = true;
            }

            $variant->set('multi_store_data', $storeData);
        }

        if ($remaining) {
            $variants->each->save();
        }

        return $remaining;
    }

    private function deleted()
    {
        return response()->json([
            'message' => 'Product has been deleted',
        ], 200);
    }
}
