<template>
    <ui-panel heading="Import" subheading="Fetch data from Shopify. Imports are queued and appear in Recent Activity.">
        <ui-card>
            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                <div class="flex flex-wrap items-center justify-between gap-4 pb-4">
                    <div>
                        <ui-heading>All products</ui-heading>
                        <ui-description>Every product in Shopify, with its variants and images. Fields with an <code>overwrite</code> option set to true in the config are replaced.</ui-description>
                    </div>
                    <ui-button :disabled="processing.products" @click="run('products', productsUrl)">{{ label('products', 'Import products') }}</ui-button>
                </div>

                <form class="flex flex-wrap items-center justify-between gap-4 py-4" @submit.prevent="run('product', productUrl, { product: selectedProduct })">
                    <div>
                        <ui-heading>One product</ui-heading>
                        <ui-description>Update a single product that's already been imported.</ui-description>
                    </div>
                    <div class="flex items-center gap-2">
                        <ui-combobox
                            class="w-64"
                            :clearable="true"
                            :placeholder="__('Select a product')"
                            v-model="selectedProduct"
                            option-label="title"
                            option-value="product_id"
                            :options="products"
                            :searchable="true"
                        />
                        <ui-button type="submit" :disabled="processing.product || ! selectedProduct">{{ label('product', 'Import product') }}</ui-button>
                    </div>
                </form>

                <div class="flex flex-wrap items-center justify-between gap-4 pt-4">
                    <div>
                        <ui-heading>Collections</ui-heading>
                        <ui-description>Shopify collections, imported as taxonomy terms and assigned to products.</ui-description>
                    </div>
                    <ui-button :disabled="processing.collections" @click="run('collections', collectionsUrl)">{{ label('collections', 'Import collections') }}</ui-button>
                </div>
            </div>
        </ui-card>
    </ui-panel>
</template>

<script>
import axios from 'axios';

export default {
    props: {
        productsUrl: String,
        productUrl: String,
        collectionsUrl: String,
        listUrl: String,
    },

    emits: ['queued'],

    data() {
        return {
            products: [],
            selectedProduct: null,
            processing: { products: false, product: false, collections: false },
        };
    },

    mounted() {
        axios.get(this.listUrl).then(res => this.products = res.data.products);
    },

    methods: {
        label(key, text) {
            return this.processing[key] ? 'Please wait' : text;
        },

        run(key, url, data = {}) {
            this.processing[key] = true;

            axios.post(url, data)
                .then(res => {
                    Statamic.$toast.success(res.data.message);
                    this.$emit('queued');
                })
                .catch(err => Statamic.$toast.error(err.response?.data?.message ?? 'Something went wrong. Please try again.'))
                .finally(() => this.processing[key] = false);
        },
    },
};
</script>
