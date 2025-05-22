<?php

namespace StatamicRadPack\Shopify\UpdateScripts;

use Statamic\Facades\Collection;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\UpdateScripts\UpdateScript;

class UpdateFromFiveToSix extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        if (! $variantCollection = Collection::find('variants')) {
            return;
        }

        if (! $blueprint = $variantCollection->entryBlueprint()) {
            return;
        }

        $currentContents = $blueprint->contents();
        $currentFields = Arr::get($currentContents, 'tabs.main.sections.0.fields', []);

        $newBlueprint = YAML::file(__DIR__.'/../../resources/blueprints/variant.yaml')->parse();

        $newFields = Arr::get($newBlueprint, 'sections.main.fields', []);

        foreach ($newFields as $newField) {
            $found = false;
            foreach ($currentFields as $currentField) {
                if ($currentField['handle'] == $newField['handle']) {
                    $found = true;
                }
            }

            if (! $found) {
                $currentFields[] = $newField;
            }
        }

        Arr::set($currentContents, 'tabs.main.sections.0.fields', $currentFields);
        $blueprint->setContents($currentContents);
        $blueprint->save();
    }
}
