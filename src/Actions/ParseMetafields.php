<?php

namespace StatamicRadPack\Shopify\Actions;

class ParseMetafields
{
    /**
     * Parse any metafields
     *
     * @return array
     */
    public function execute(array $metafields, string $context)
    {
        return collect($metafields)
            ->mapWithKeys(function ($field) {
                return [$field['key'] => $field['value']];
            })
            ->all();
    }
}
