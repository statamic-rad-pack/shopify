@extends('statamic::layout')

@section('content')
    <div class="flex items-center justify-between mb-3">
        <h1>Shopify</h1>

        <a href="{{ $shopify_url }}" class="btn-primary" target="_blank">Go To Shopify Admin</a>
    </div>

    <div class="card">
        <h2 class="mb-2">Import Products</h2>
        <p class="mb-2 text-sm max-w-md leading-loose">This will fetch all of your product data from Shopify. Please note if you have `overwrite_content` set to true, this will overwrite any updates made in the dashboard.</p>

        <import-products-button url="{{ cp_route('shopify.products.fetchAll') }}"></import-products-button>
    </div>

    <div class="card mt-4">
        <h2 class="mb-2">Import Single Product</h2>
        <p class="mb-2 text-sm max-w-md leading-loose">Fetch a single product's data from Shopify. Please note if you have `overwrite_content` set to true, this will overwrite any updates made in the dashboard.</p>

        <import-product-button url="{{ cp_route('shopify.products.fetch') }}"></import-product-button>
    </div>
@endsection
