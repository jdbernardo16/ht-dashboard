<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";

// Dashboard Components
import DashboardGrid from "@/Components/Dashboard/DashboardGrid.vue";
import DailySummaryModule from "@/Components/Dashboard/DailySummaryModule.vue";
import ActivityDistributionModule from "@/Components/Dashboard/ActivityDistributionModule.vue";
import SalesModule from "@/Components/Dashboard/SalesModule.vue";
import ExpensesModule from "@/Components/Dashboard/ExpensesModule.vue";
import ContentModule from "@/Components/Dashboard/ContentModule.vue";
import QuarterlyGoalsModule from "@/Components/Dashboard/QuarterlyGoalsModule.vue";

// Props from Laravel controller
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

// Computed properties for dashboard modules
const dailySummary = computed(() => props.dashboardData.dailySummary);
const activityDistribution = computed(
    () => props.dashboardData.activityDistribution
);
const salesMetrics = computed(() => props.dashboardData.salesMetrics);
const expenses = computed(() => props.dashboardData.expenses);
const contentStats = computed(() => props.dashboardData.contentStats);
const quarterlyGoals = computed(() => props.dashboardData.quarterlyGoals);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div style="margin-bottom: 2rem">
                <h1
                    style="
                        font-size: 1.875rem;
                        font-weight: 700;
                        color: #111827;
                    "
                >
                    Dashboard
                </h1>
                <p
                    style="
                        font-size: 0.875rem;
                        color: #4b5563;
                        margin-top: 0.25rem;
                    "
                >
                    Welcome back! Here's your performance overview.
                </p>
            </div>
        </template>

        <div
            style="
                padding: 2rem 1rem;
                max-width: 80rem;
                margin-left: auto;
                margin-right: auto;
            "
        >
            <DashboardGrid>
                <!-- Row 1 -->
                <DailySummaryModule
                    :summary="dailySummary"
                    style="height: 100%"
                />
                <ActivityDistributionModule
                    :data="activityDistribution"
                    style="height: 100%"
                />
                <SalesModule :data="salesMetrics" style="height: 100%" />

                <!-- Row 2 -->
                <ExpensesModule :data="expenses" style="height: 100%" />
                <ContentModule :data="contentStats" style="height: 100%" />
                <QuarterlyGoalsModule
                    :data="quarterlyGoals"
                    style="height: 100%"
                />
            </DashboardGrid>

            <!-- Last Updated Info -->
            <div style="margin-top: 2rem; text-align: center">
                <p style="font-size: 0.875rem; color: #6b7280">
                    Last updated: {{ new Date(lastUpdated).toLocaleString() }}
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.dashboard-container {
    @apply py-8 px-4 sm:px-6 lg:px-8;
    @apply mx-auto max-w-7xl;
}

.dashboard-header {
    @apply mb-8;
}

.dashboard-module {
    @apply h-full;
}
</style>
