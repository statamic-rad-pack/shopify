<template>
    <div>
        <div v-if="loading" class="text-sm text-gray-500">Loading webhook status…</div>

        <ui-error-message v-else-if="error">{{ error }}</ui-error-message>

        <template v-else>
            <ui-table v-if="webhooks.length || missingTopics.length">
                <ui-table-columns>
                    <ui-table-column>Topic</ui-table-column>
                    <ui-table-column>Callback URL</ui-table-column>
                    <ui-table-column>Status</ui-table-column>
                    <ui-table-column>Last received</ui-table-column>
                </ui-table-columns>
                <ui-table-rows>
                    <ui-table-row v-for="webhook in webhooks" :key="webhook.id">
                        <ui-table-cell class="font-mono text-xs">{{ webhook.topic }}</ui-table-cell>
                        <ui-table-cell class="font-mono text-xs">{{ webhook.callbackUrl }}</ui-table-cell>
                        <ui-table-cell>
                            <ui-badge v-if="webhook.expected" color="green" icon="check" text="Registered" />
                            <ui-badge v-else color="amber" icon="warning" text="Unknown URL" />
                        </ui-table-cell>
                        <ui-table-cell class="text-xs">{{ formatLastReceived(webhook.last_received_at) }}</ui-table-cell>
                    </ui-table-row>
                    <ui-table-row v-for="topic in missingTopics" :key="topic">
                        <ui-table-cell class="font-mono text-xs">{{ topic }}</ui-table-cell>
                        <ui-table-cell class="font-mono text-xs">{{ expected[topic] }}</ui-table-cell>
                        <ui-table-cell>
                            <ui-badge color="red" icon="warning-diamond" text="Not registered" />
                        </ui-table-cell>
                        <ui-table-cell class="text-xs">—</ui-table-cell>
                    </ui-table-row>
                </ui-table-rows>
            </ui-table>

            <p v-else class="text-sm text-gray-500">
                No webhooks are registered in Shopify yet. Run <code>php artisan shopify:webhooks:register</code> to set them up.
            </p>
        </template>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: {
        url: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            loading: true,
            error: null,
            webhooks: [],
            expected: {},
        };
    },

    computed: {
        missingTopics() {
            const registeredTopics = this.webhooks
                .filter(w => w.expected)
                .map(w => w.topic);

            return Object.keys(this.expected).filter(t => !registeredTopics.includes(t));
        },
    },

    methods: {
        formatLastReceived(timestamp) {
            if (!timestamp) return 'Never';
            return new Date(timestamp).toLocaleString();
        },
    },

    mounted() {
        axios.get(this.url)
            .then(res => {
                this.webhooks = res.data.webhooks;
                this.expected = res.data.expected;
            })
            .catch(err => {
                this.error = err.response?.data?.error ?? 'Could not load webhook status.';
            })
            .finally(() => {
                this.loading = false;
            });
    },
};
</script>
