<template>
    <div class="flex">
        <section class="flex-grow">
            <table class="data-table">
                <tr v-for="(variant, index) in variants" :key="index">
                    <td>
                        <a href="">{{ variant.title }}</a>
                        <dropdown-item
                            :text="__('Edit')"
                            @click="openEditVariantStack(variant)"
                        ></dropdown-item>
                    </td>
                </tr>
            </table>
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
    }
}
</script>
