@extends('statamic::layout')

@section('content')
    <div class="flex items-center justify-between mb-3">
        <h1>Shopify</h1>

        @if ($shopify_url)
            <a href="{{ $shopify_url }}" class="btn-primary" target="_blank">Go To Shopify Admin</a>
        @endif
    </div>

    @if ($can_run_import)
        <div class="card">
            <h2 class="mb-2">Import Products</h2>
            <p class="mb-2 text-sm max-w-md leading-loose">This will fetch all of your product data from Shopify. Please note if you have any `overwrite` option set to true in the config product data will be replaced.</p>
            <shopify-import-button url="{{ cp_route('shopify.products.fetchAll') }}"></shopify-import-button>
        </div>

        <div class="card mt-4">
            <h2 class="mb-2">Import Single Product</h2>
            <p class="mb-2 text-sm max-w-md leading-loose">Fetch a single product's data from Shopify. Please note if you have any `overwrite` option set to true in the config product data will be replaced.</p>
            <shopify-import-product-button url="{{ cp_route('shopify.products.fetch') }}"></shopify-import-product-button>
        </div>

        <div class="card mt-4">
            <h2 class="mb-2">Import Collections</h2>
            <p class="mb-2 text-sm max-w-md leading-loose">Fetch the collections data from Shopify - these will be imported as taxonomies and assigned to the products.</p>
            <shopify-import-button url="{{ cp_route('shopify.collections.fetchAll') }}"></shopify-import-button>
        </div>
    @else
        <div class="card">
            <h2 class="mb-2">Set Things Up</h2>

            <p class="mb-2 text-sm max-w-md leading-loose">Looks like you haven't set everything up yet. You might be missing one of the following:</p>

            <ul class="mb-2 pl-3 list-decimal text-sm max-w-md leading-loose">
                <li>Have you updated your `env` variables?</li>
                <li>Have you published the assets?</li>
            </ul>

            <p class="text-sm max-w-md leading-loose">If not, ensure you've ran through the <a href="https://statamic-shopify.jackwhiting.co.uk/setup" class="text-blue-700">Quickstart</a> guide on the documentation.</p>
        </div>
    @endif
@endsection
