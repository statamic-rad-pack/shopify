<template>
    <div class="flex items-center">
        <button type="button" class="btn-primary" @click="fetchProducts()">Import All</button>
        <p class="ml-2 text-sm" :class="messageColor" v-if="message">{{ message }}</p>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: {
        url: String
    },

    data() {
        return {
            message: null,
            messageColor: 'text-black'
        }
    },

    methods: {
        fetchProducts() {
            this.message = 'working....'
            this.messageColor = 'text-black'

            axios.get(this.url)
                .then(res => {
                    console.log(res)
                    this.message = res.data.message
                    this.messageColor = 'text-green'

                    setTimeout(() => this.message = null, 3000)
                })
                .catch(err => {
                    this.message = 'Something went wrong. Please try again.'
                    this.messageColor = 'text-red'

                    setTimeout(() => this.message = null, 5000)
                })
        }
    }
}
</script>
