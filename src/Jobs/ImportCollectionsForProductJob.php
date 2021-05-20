<?php

namespace Jackabox\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Facades\Term;

class ImportCollectionsForProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
            $term = Term::findBySlug($collection['handle'], config('shopify.taxonomies.collections'));

            if (!$term) {
                $term = Term::make()
                    ->taxonomy(config('shopify.taxonomies.collections'))
                    ->slug($collection['handle'])
                    ->data([
                        'title' => $collection['title'],
                        'collection_id' => $collection['id'],
                        'content' => $collection['body_html']
                    ]);

                $term->save();
            }

            $product_collections->push($collection['handle']);
        }

        $this->product->set(config('shopify.taxonomies.collections'), $product_collections->toArray());
        $this->product->save();
    }
}
