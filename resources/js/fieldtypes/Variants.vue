<template>
    <div class="flex">
        <section class="flex-grow border rounded">
            <table class="data-table" v-if="variants">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(variant, index) in variants" :key="index" class="cursor-pointer" @click="openEditVariantStack(variant)">
                        <td class="text-base">
                            {{ variant.title }}
                        </td>
                        <td class="text-sm">
                            {{ currencyFormat(variant.price) }}
                        </td>
                        <td class="text-sm">
                            {{ variant.inventory_quantity }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-else class="text-sm">To get started, add some variants to products in Shopify.</p>
        </section>

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
import VariantForm from '../components/VariantForm'

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
        this.fetch()
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
