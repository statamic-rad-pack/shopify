 <template>
    <form @submit.prevent="fetchProduct()" >
        <div class="mb-1 max-w-sm">
            <ui-combobox
                class="w-full"
                clearable="true"
                :label="__('Select a product')"
                v-model="selectedProduct"
                optionLabel="title"
                :options="products"
                optionValue="product_id"
                searchable="true"
            />
        </div>

        <div class="flex items-center">
            <ui-button type="button" @click="fetch()" :disabled="processing">{{ processing ? 'Please wait' : 'Import product' }}</ui-button>
            <ui-error-message class="ml-2" v-if="message">{{ message }}</ui-error-message>
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
            message: null,
            selectedProduct: null,
            processing: false,
            products: [],
        }
    },

    mounted() {
        this.fetch()
    },

    methods: {
        fetch() {
            axios.get(this.listUrl)
                .then(res => {
                    this.products = res.data.products
                })
        },

        fetchProduct() {
            this.message = '';
            this.processing = true;

            axios.get(`${this.url}?product=${this.selectedProduct.product_id}`)
                .then(res => {
                    this.message = res.data.message

                    setTimeout(() => this.message = null, 3000)
                }).catch(err => {
                    this.message = 'Something went wrong. Please try again.'
                    setTimeout(() => this.message = null, 5000)
                })
                .finally(() => this.processing = false)
        }
    }
}
</script>
