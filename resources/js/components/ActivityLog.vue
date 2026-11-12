<template>
    <div>
        <div v-if="filterable" class="mb-4 flex flex-wrap gap-2">
            <ui-input v-model="filters.search" icon-prepend="magnifying-glass" placeholder="Search title, ID or message" class="w-64" @update:model-value="debouncedLoad" />
            <ui-select v-model="filters.status" :options="options.statuses" placeholder="Any status" clearable class="w-40" @update:model-value="reload" />
            <ui-select v-model="filters.topic" :options="options.topics" placeholder="Any topic" clearable class="w-48" @update:model-value="reload" />
            <ui-select v-model="filters.source" :options="options.sources" placeholder="Any source" clearable class="w-40" @update:model-value="reload" />
            <ui-select v-if="options.stores.length" v-model="filters.store" :options="options.stores" placeholder="Any store" clearable class="w-40" @update:model-value="reload" />
        </div>

        <div v-if="loading && ! items.length" class="text-sm text-gray-500">Loading activity…</div>

        <ui-error-message v-else-if="error">{{ error }}</ui-error-message>

        <p v-else-if="! items.length" class="text-sm text-gray-500">No Shopify activity yet.</p>

        <ui-table v-else>
            <ui-table-columns>
                <ui-table-column>When</ui-table-column>
                <ui-table-column>Source</ui-table-column>
                <ui-table-column>Resource</ui-table-column>
                <ui-table-column>Status</ui-table-column>
                <ui-table-column>Details</ui-table-column>
                <ui-table-column v-if="hasRetry"></ui-table-column>
            </ui-table-columns>
            <ui-table-rows>
                <ui-table-row v-for="item in items" :key="item.id">
                    <ui-table-cell class="text-xs whitespace-nowrap" :title="item.created_at">{{ formatDate(item.created_at) }}</ui-table-cell>
                    <ui-table-cell class="text-xs">
                        <div>{{ sourceLabel(item.source) }}</div>
                        <div v-if="item.topic" class="font-mono text-gray-500">{{ item.topic }}</div>
                        <div v-if="item.store" class="text-gray-500">{{ item.store }}</div>
                    </ui-table-cell>
                    <ui-table-cell class="text-xs">
                        <a v-if="item.edit_url" :href="item.edit_url" class="underline">{{ resourceLabel(item) }}</a>
                        <span v-else>{{ resourceLabel(item) }}</span>
                        <div v-if="item.resource_id" class="font-mono text-gray-500">{{ item.resource_type }} {{ item.resource_id }}</div>
                    </ui-table-cell>
                    <ui-table-cell>
                        <ui-badge :color="statusColor(item.status)" :text="statusLabel(item.status)" />
                    </ui-table-cell>
                    <ui-table-cell class="text-xs">
                        <div v-if="item.message">{{ item.message }}</div>
                        <div v-if="changesLabel(item.changes)" class="text-gray-500">{{ changesLabel(item.changes) }}</div>
                    </ui-table-cell>
                    <ui-table-cell v-if="hasRetry">
                        <ui-button v-if="item.retry_url" size="sm" :disabled="retrying === item.id" @click="retry(item)">Retry</ui-button>
                    </ui-table-cell>
                </ui-table-row>
            </ui-table-rows>
        </ui-table>

        <ui-pagination v-if="paginate && meta && meta.last_page > 1" class="mt-4" :resource-meta="meta" :per-page="perPage" @page-selected="selectPage" />
    </div>
</template>

<script>
import axios from 'axios';

const STATUS_COLORS = {
    received: 'blue',
    queued: 'blue',
    updated: 'green',
    deleted: 'amber',
    skipped: 'gray',
    duplicate: 'gray',
    rejected: 'red',
    failed: 'red',
};

const SOURCE_LABELS = {
    webhook: 'Webhook',
    cp: 'Control panel',
    cli: 'Command line',
};

export default {
    props: {
        url: { type: String, required: true },
        perPage: { type: Number, default: 25 },
        filterable: { type: Boolean, default: false },
        paginate: { type: Boolean, default: false },
    },

    data() {
        return {
            loading: true,
            error: null,
            items: [],
            meta: null,
            page: 1,
            retrying: null,
            filters: { search: '', status: null, topic: null, source: null, store: null },
            options: { statuses: [], topics: [], sources: [], stores: [] },
            searchTimer: null,
        };
    },

    computed: {
        hasRetry() {
            return this.items.some(item => item.retry_url);
        },
    },

    methods: {
        load() {
            this.loading = true;

            const params = { page: this.page, per_page: this.perPage };

            for (const [key, value] of Object.entries(this.filters)) {
                if (value) params[key] = value;
            }

            axios.get(this.url, { params })
                .then(res => {
                    this.items = res.data.data;
                    this.meta = res.data.meta;
                    this.error = null;
                    this.options = {
                        statuses: res.data.filters.statuses.map(value => ({ value, label: this.statusLabel(value) })),
                        topics: res.data.filters.topics.map(value => ({ value, label: value })),
                        sources: res.data.filters.sources.map(value => ({ value, label: this.sourceLabel(value) })),
                        stores: res.data.filters.stores.map(value => ({ value, label: value })),
                    };
                })
                .catch(err => {
                    this.error = err.response?.data?.message ?? 'Could not load activity.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        reload() {
            this.page = 1;
            this.load();
        },

        debouncedLoad() {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => this.reload(), 300);
        },

        selectPage(page) {
            this.page = page;
            this.load();
        },

        retry(item) {
            this.retrying = item.id;

            axios.post(item.retry_url)
                .then(res => {
                    Statamic.$toast.success(res.data.message);
                    this.load();
                })
                .catch(err => {
                    Statamic.$toast.error(err.response?.data?.message ?? 'Could not retry this import.');
                })
                .finally(() => {
                    this.retrying = null;
                });
        },

        formatDate(timestamp) {
            return timestamp ? new Date(timestamp).toLocaleString() : '—';
        },

        statusColor(status) {
            return STATUS_COLORS[status] ?? 'gray';
        },

        statusLabel(status) {
            return status.charAt(0).toUpperCase() + status.slice(1);
        },

        sourceLabel(source) {
            return SOURCE_LABELS[source] ?? source;
        },

        resourceLabel(item) {
            return item.resource_title || (item.resource_type ? `${item.resource_type} ${item.resource_id ?? ''}`.trim() : '—');
        },

        changesLabel(changes) {
            if (! changes) return null;

            const parts = ['added', 'updated', 'removed']
                .filter(key => changes[key])
                .map(key => `${changes[key]} variant${changes[key] === 1 ? '' : 's'} ${key}`);

            return parts.length ? parts.join(', ') : null;
        },
    },

    mounted() {
        this.load();
    },
};
</script>
