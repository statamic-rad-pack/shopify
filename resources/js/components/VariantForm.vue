<template>
    <stack name="Form Stack" :narrow="narrow" @closed="close">
        <div class="h-full overflow-auto p-6 bg-gray-300">
            <header class="-mt-2 mb-3 flex justify-end">
                <button type="button" class="btn-close" @click="close">×</button>
            </header>
            <publish-form
                name="Form Stack publish form"
                :title="title"
                :action="action"
                :method="method"
                :blueprint="blueprint"
                :meta="meta"
                :values="values"
                @saved="saved"
                class="max-w-3xl mx-auto"
            >
            </publish-form>
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
