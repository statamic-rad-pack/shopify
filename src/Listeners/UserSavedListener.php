<?php

namespace StatamicRadPack\Shopify\Listeners;

use Statamic\Events\UserSaved;
use StatamicRadPack\Shopify\Jobs\CreateOrUpdateShopifyUser;

class UserSavedListener
{
    public function handle(UserSaved $event)
    {
        if (! config('shopify.update_users_in_shopify')) {
            return;
        }

        app(config('shopify.update_shopify_user_job'))::dispatch($event->user);
    }
}
