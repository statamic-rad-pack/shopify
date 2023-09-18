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
            ->mapWithKeys(fn ($field) => [$field['key'] => $field['value']])
            ->all();
    }
}
