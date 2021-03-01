<?php

namespace Jackabox\Shopify\Blueprints;

use Illuminate\Validation\Rule;

abstract class Blueprint
{
    /**
     * Check if the actual route is equal to the given route.
     * @param $route
     * @return bool
     */
    protected function isRoute($route): bool
    {
        if (! isset(request()->route()->action['as'])) {
            return false;
        }

        return request()->route()->action['as'] === $route;
    }
}
