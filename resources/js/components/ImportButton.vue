<template>
    <div class="flex items-center">
        <ui-button type="button" @click="fetch()" :disabled="processing">{{ processing ? 'Please wait' : 'Run import' }}</ui-button>
        <ui-error-message class="ml-2" v-if="message">{{ message }}</ui-error-message>
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
            processing: false,
        }
    },

    methods: {
        fetch() {
            this.message = '';
            this.processing = true;

            axios.get(this.url)
                .then(res => {
                    this.message = res.data.message

                    setTimeout(() => this.message = null, 3000)
                })
                .catch(err => {
                    this.message = 'Something went wrong. Please try again.'

                    setTimeout(() => this.message = null, 5000)
                })
                .finally(() => this.processing = false)
        }
    }
}
</script>
