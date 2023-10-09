<?php

namespace StatamicRadPack\Shopify\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Shopify\Clients\Rest;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class CreateOrUpdateShopifyUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public $user)
    {
    }

    public function handle()
    {
        if (! $this->user->get('shopify_id')) {
            $shopifyUser = $this->createOrAssociateUser();
        }

        $response = app(Rest::class)->put(path: 'customers/'.$this->user->get('shopify_id'), body: $this->generatePayloadBody());
    }

    private function createOrAssociateUser()
    {
        $response = app(Rest::class)->get(path: 'customers/search', query: ['query' => 'email:'.$this->user->email(), 'fields' => 'id']);

        if ($response->getStatusCode() == 200) {
            $data = Arr::get($response->getDecodedBody(), 'customers', []);

            if (count($data)) {
                $this->user->set('shopify_id', $data[0]['id']);
                $this->user->saveQuietly();
                return;
            }

        }

        $response = app(Rest::class)->post(path: 'customers', body: $this->generatePayloadBody());

        if ($response->getStatusCode() == 201) {
            $id = Arr::get($response->getDecodedBody(), 'customer.id', false);

            if ($id) {
                $this->user->set('shopify_id', $id);
                $this->user->saveQuietly();
            }
        }

        throw new \Exception('Could not create user in Shopify');
    }

    private function generatePayloadBody()
    {
        return [
            'first_name' => Str::before($this->user->name(), ' '),
            'last_name' => Str::after($this->user->name(), ' '),
            'email' => $this->user->email(),
            'verified_email' => true,
        ];
    }
}
