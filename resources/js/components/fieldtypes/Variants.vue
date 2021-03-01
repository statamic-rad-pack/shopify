<template>
    <div class="flex">
        <section class="flex-grow">
            <table class="data-table">
                <tr v-for="(variant, index) in variants" :key="index">
                    <td>{{ variant.title }}</td>
                </tr>
            </table>
        </section>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    mixins: [Fieldtype],

    data() {
        return {
            variants: [],
            variantIndexRoute: this.meta.variantIndexRoute
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
        }
    }
}
</script>
