<script setup>
import { Head } from '@statamic/cms/inertia';
import ActivityLog from '../components/ActivityLog.vue';

defineProps({
    activityUrl: String,
    dashboardUrl: String,
});
</script>

<template>
    <Head title="Shopify Activity" />

    <ui-header title="Shopify Activity" icon="history">
        <ui-button :href="dashboardUrl">Shopify settings</ui-button>
    </ui-header>

    <ui-card>
        <ui-description class="mb-4">Every webhook received from Shopify and every import run from the control panel or command line, and what it changed in Statamic.</ui-description>
        <ActivityLog :url="activityUrl" filterable paginate />
    </ui-card>
</template>
