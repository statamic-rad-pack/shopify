<template>
    <div class="flex">
        <ui-table class="w-full" v-if="variants">
            <ui-table-columns>
                <ui-table-column>{{ __('Title') }}</ui-table-column>
                <ui-table-column>{{ __('SKU') }}</ui-table-column>
                <ui-table-column>{{ __('Price') }}</ui-table-column>
                <ui-table-column>{{ __('Stock') }}</ui-table-column>
                <ui-table-column></ui-table-column>
            </ui-table-columns>
            <ui-table-row v-for="(variant, index) in variants" :key="index">
                <ui-table-cell>
                    {{ variant.title }}
                </ui-table-cell>
                <ui-table-cell>
                    {{ variant.sku }}
                </ui-table-cell>
                <ui-table-cell>
                    {{ currencyFormat(variant.price) }}
                </ui-table-cell>
                <ui-table-cell>
                    {{ variant.inventory_quantity }}
                </ui-table-cell>
                <ui-table-cell>
                    <ui-button @click="openEditVariantStack(variant)" size="sm">Edit</ui-button>
                </ui-table-cell>
            </ui-table-row>
        </ui-table>

        <ui-description v-else>To get started, add some variants to products in Shopify.</ui-description>

        <variant-form
            name="variant stack"
            v-if="showVariantStack"
            :action="stackAction"
            title="Edit Variant"
            :blueprint="variantBlueprint"
            :meta="variantMeta"
            :method="stackMethod"
            :values="stackValues"
            @closed="showVariantStack = false"
            @saved="closeVariantStack"
        />
    </div>
</template>

<script>
import axios from 'axios'
import { FieldtypeMixin as Fieldtype } from '@statamic/cms';
import VariantForm from '../components/VariantForm.vue'

export default {
    mixins: [Fieldtype],

    components: {
        VariantForm
    },

    data() {
        return {
            variants: [],
            action: this.meta.action,
            variantIndexRoute: this.meta.variantIndexRoute,
            variantManageRoute: this.meta.variantManageRoute,
            variantMeta: this.meta.variantMeta,
            variantBlueprint: this.meta.variantBlueprint,
            productSlug: this.meta.productSlug,
            showVariantStack: false,
            stackAction: null,
            stackMethod: 'post',
            stackValues: null,
        }
    },

    mounted() {
        if (this.productSlug) {
            this.fetch();
        }
    },

    methods: {
        fetch() {
            axios.get(this.variantIndexRoute)
                .then(res => this.variants = res.data)
                .catch(error => this.$toast.error(error))
        },

        openEditVariantStack(variant) {
            this.stackValues = variant
            this.stackAction = `${this.action}/${variant.id}`
            this.stackMethod = 'patch'
            this.showVariantStack = true
        },

        closeVariantStack() {
            this.fetch()
            this.showVariantStack = false
        },

        currencyFormat(price) {
            return parseFloat(price).toFixed(2);
        }
    }
}
</script>
