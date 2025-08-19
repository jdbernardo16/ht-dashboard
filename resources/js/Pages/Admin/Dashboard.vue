<script setup>
import Dashboard from "@/Pages/Dashboard.vue";
import { Head } from "@inertiajs/vue3";

// Receive Inertia props at this page level and forward them to the inner Dashboard component
const props = defineProps({
    dashboardData: {
        type: Object,
        required: true,
    },
    lastUpdated: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Admin Dashboard" />
    <!-- Forward server-provided props to the actual Dashboard page component -->
    <Dashboard
        :dashboard-data="props.dashboardData"
        :last-updated="props.lastUpdated"
    />
</template>
