<?php

namespace StatamicRadPack\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Shopify\Clients\Rest;
use Statamic\Facades\Term;
use Statamic\Support\Arr;
use StatamicRadPack\Shopify\Traits\SavesImagesAndMetafields;

class ImportCollectionsForProductJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use SavesImagesAndMetafields;

    public $product;

    public $collections;

    public function __construct($collections, $product)
    {
        $this->product = $product;
        $this->collections = $collections;
    }

    public function handle()
    {
        $product_collections = collect();

        foreach ($this->collections as $collection) {
            $term = Term::query()
                ->where('slug', $collection['handle'])
                ->where('taxonomy', config('shopify.taxonomies.collections'))
                ->first();

            if (! $term) {
                $term = Term::make()
                    ->taxonomy(config('shopify.taxonomies.collections'))
                    ->slug($collection['handle']);
            }

            $data = [
                'title' => $collection['title'],
                'collection_id' => $collection['id'],
                'content' => $collection['body_html'],
            ];

            // Import Images
            if (isset($collection['image'])) {
                $asset = $this->importImages($collection['image']);
                $data['featured_image'] = $asset->path();
            }

            try {
                $response = app(Rest::class)->get(path: 'metafields', query: ['metafield' => ['owner_id' => $collection['id'], 'owner_resource' => 'collection']]);

                if ($response->getStatusCode() == 200) {
                    $metafields = Arr::get($response->getDecodedBody(), 'metafields', []);

                    if ($metafields) {
                        $metafields = $this->parseMetafields($collectionMetafields, 'collection');

                        if ($metafields) {
                            $data = array_merge($metafields, $data);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Could not retrieve metafields for product'.$this->data['id']);
            }

            $term->merge($data)->save();

            $product_collections->push($collection['handle']);
        }

        $this->product->set(config('shopify.taxonomies.collections'), $product_collections->toArray());
        $this->product->save();
    }
}
