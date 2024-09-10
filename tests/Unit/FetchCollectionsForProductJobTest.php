<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use Shopify\Clients\Rest;
use Shopify\Clients\RestResponse;
use Statamic\Facades;
use StatamicRadPack\Shopify\Jobs;
use StatamicRadPack\Shopify\Tests\TestCase;

class FetchCollectionsForProductJobTest extends TestCase
{
    /** @test */
    public function imports_collections_for_product()
    {
        Facades\Taxonomy::make()->handle('collections')->save();

        Facades\Term::make()->taxonomy('collections')->slug('ipods')->merge([])->save();
        Facades\Term::make()->taxonomy('collections')->slug('ipods_two')->merge([])->save();
        Facades\Term::make()->taxonomy('collections')->slug('non-ipods')->merge([])->save();

        $product = tap(Facades\Entry::make()
            ->collection('products')
            ->id('product-1')
            ->slug('product-1')
        )->save();

        $this->mock(Rest::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('get')
                ->andReturn(new RestResponse(
                    status: 200,
                    body: '{"custom_collections":[{"id": 841564295,"handle": "ipods","title": "IPods","updated_at": "2008-02-01T19:00:00-05:00","body_html": "<p>The best selling ipod ever</p>","published_at": "2008-02-01T19:00:00-05:00","sort_order": "manual","template_suffix": null,"published_scope": "web","admin_graphql_api_id": "gid://shopify/Collection/841564295"},{"id": 395646240,"handle": "ipods_two","title": "IPods Two","updated_at": "2008-02-01T19:00:00-05:00","body_html": "<p>The best selling ipod ever. Again</p>","published_at": "2008-02-01T19:00:00-05:00","sort_order": "manual","template_suffix": null,"published_scope": "web","admin_graphql_api_id": "gid://shopify/Collection/395646240"},{"id": 691652237,"handle": "non-ipods","title": "Non Ipods","updated_at": "2013-02-01T19:00:00-05:00","body_html": "<p>No ipods here</p>","published_at": "2013-02-01T19:00:00-05:00","sort_order": "manual","template_suffix": null,"published_scope": "web","admin_graphql_api_id": "gid://shopify/Collection/691652237"}]}'
                ));
        });

        Jobs\FetchCollectionsForProductJob::dispatch($product);

        // check collection terms are created
        $this->assertSame(['ipods', 'ipods_two', 'non-ipods'], $product->fresh()->collections);
    }
}
