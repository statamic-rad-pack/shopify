 <template>
    <stack name="variant-form-stack" :narrow="narrow" @closed="close">
        <div class="h-full overflow-auto p-6 bg-white">
            <header class="-mt-2 mb-3 flex justify-end">
                <button type="button" class="btn-close" @click="close">×</button>
            </header>
            <ui-publish-form
                name="variant-publish-form"
                :title="title"
                :submit-url="action"
                :submit-method="method"
                :blueprint="blueprint"
                :initial-meta="meta"
                :initial-values="values"
                @saved="saved"
                class="max-w-3xl mx-auto"
            >
            </ui-publish-form>
        </div>
    </stack>
</template>

<script>
export default {
    props: {
        blueprint: null,
        meta: null,
        values: null,
        method: { default: 'post' },
        title: String,
        action: String,
        narrow: Boolean,
    },

    methods: {
        close() {
            this.$emit('closed', true)
        },

        saved() {
            this.$emit('saved', true)
        }
    }
}
</script>
