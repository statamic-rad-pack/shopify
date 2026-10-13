<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use StatamicRadPack\Shopify\Tests\TestCase;

class VariantBlueprintTest extends TestCase
{
    #[Test]
    public function variant_slugs_are_validated_as_unique_when_saved_in_the_control_panel()
    {
        Storage::fake('assets');
        AssetContainer::make('shopify')->disk('assets')->save();

        $user = tap(User::make()->email('admin@example.com')->makeSuper())->save();

        Entry::make()->collection('variants')->slug('111')->data(['title' => 'Small', 'price' => '10.00'])->save();
        $variant = tap(Entry::make()->collection('variants')->slug('222')->data(['title' => 'Medium', 'price' => '10.00']))->save();

        $url = cp_route('collections.entries.update', ['variants', $variant->id()]);

        $this->actingAs($user)
            ->patchJson($url, ['title' => 'Medium', 'slug' => '222', 'price' => '10.00'])
            ->assertOk();

        $this->actingAs($user)
            ->patchJson($url, ['title' => 'Medium', 'slug' => '111', 'price' => '10.00'])
            ->assertJsonValidationErrors('slug');
    }
}
