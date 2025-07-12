<template>
    <form @submit.prevent="fetchProduct()" >
        <p class="mb-1 max-w-sm"><v-select
              placeholder="Select a product"
              :options="products"
              :get-option-key="(option) => option.product_id"
              :get-option-label="(option) => option.title"
              :searchable="true"
              @input="input"
        /></p>

        <div class="flex items-center">
            <button type="submit" class="btn-primary" >Import Product</button>
            <p class="ml-2 text-sm" :class="messageColor" v-if="message">{{ message }}</p>
        </div>
    </form>
</template>

<script>
import axios from "axios";

export default {
    props: {
        url: String,
        listUrl: String,
        product: String
    },

    data() {
        return {
            products: [],
            message: null,
            messageColor: 'text-black',
            selectedProduct: null
        }
    },

    mounted() {
        this.fetch()
    },

    methods: {
        fetch() {
            axios.get(this.listUrl)
                .then(res => {
                    console.log(res)
                    this.products = res.data.products
                })
        },

        input(value) {
            this.selectedProduct = value
        },

        fetchProduct() {
            this.message = 'working....'
            this.messageColor = 'text-black'

            axios.get(`${this.url}?product=${this.selectedProduct.product_id}`)
                .then(res => {
                    console.log(res)
                    this.message = res.data.message
                    this.messageColor = 'text-green'

                    setTimeout(() => this.message = null, 3000)
                }).catch(err => {
                    this.message = 'Something went wrong. Please try again.'
                    this.messageColor = 'text-red'
                    setTimeout(() => this.message = null, 5000)
                })
        }
    }
}
</script>
