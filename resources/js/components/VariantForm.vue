 <template>
    <ui-stack :open="true" :size="narrow ? 'narrow' : null" @update:open="close">
        <ui-publish-form
            name="variant-publish-form"
            :title="title"
            :submit-url="action"
            :submit-method="method"
            :blueprint="blueprint"
            :initial-meta="meta"
            :initial-values="values"
            @saved="saved"
        ></ui-publish-form>
    </ui-stack>
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
