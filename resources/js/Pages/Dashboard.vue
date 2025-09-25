<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";

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
    currentPeriod: {
        type: String,
        default: "daily",
    },
    currentStartDate: {
        type: String,
        default: "",
    },
    currentEndDate: {
        type: String,
        default: "",
    },
});

// Time period state
const selectedPeriod = ref(props.currentPeriod);
const customStartDate = ref(props.currentStartDate);
const customEndDate = ref(props.currentEndDate);
const dashboardData = ref(props.dashboardData);

// Computed properties for dashboard modules
const dailySummary = computed(() => dashboardData.value.dailySummary);
const activityDistribution = computed(
    () => dashboardData.value.activityDistribution
);
const salesMetrics = computed(() => dashboardData.value.salesMetrics);
const expenses = computed(() => dashboardData.value.expenses);
const contentStats = computed(() => dashboardData.value.contentStats);
const quarterlyGoals = computed(() => dashboardData.value.quarterlyGoals);

// Reload dashboard with new period parameters
const reloadDashboard = () => {
    const params = {
        period: selectedPeriod.value,
    };

    if (
        selectedPeriod.value === "custom" &&
        customStartDate.value &&
        customEndDate.value
    ) {
        params.start_date = customStartDate.value;
        params.end_date = customEndDate.value;
    }

    router.visit(window.location.pathname, {
        data: params,
        preserveState: false,
        preserveScroll: true,
    });
};

// Handle period change from any module
const handlePeriodChange = (event) => {
    selectedPeriod.value = event.period;

    if (event.period === "custom") {
        customStartDate.value = event.startDate;
        customEndDate.value = event.endDate;
    } else {
        customStartDate.value = "";
        customEndDate.value = "";
    }

    reloadDashboard();
};
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

        <div style="padding: 1rem; margin-left: auto; margin-right: auto">
            <div>
                <DashboardGrid>
                    <!-- Row 1 -->
                    <DailySummaryModule
                        :summary="dailySummary"
                        :current-period="selectedPeriod"
                        :current-start-date="customStartDate"
                        :current-end-date="customEndDate"
                        style="height: 100%"
                        @period-change="handlePeriodChange"
                    />
                    <ActivityDistributionModule
                        :data="activityDistribution"
                        :current-period="selectedPeriod"
                        :current-start-date="customStartDate"
                        :current-end-date="customEndDate"
                        style="height: 100%"
                        @period-change="handlePeriodChange"
                    />
                    <SalesModule
                        :data="salesMetrics"
                        :current-period="selectedPeriod"
                        :current-start-date="customStartDate"
                        :current-end-date="customEndDate"
                        style="height: 100%"
                        @period-change="handlePeriodChange"
                    />

                    <!-- Row 2 -->
                    <ExpensesModule
                        :data="expenses"
                        :current-period="selectedPeriod"
                        :current-start-date="customStartDate"
                        :current-end-date="customEndDate"
                        style="height: 100%"
                        @period-change="handlePeriodChange"
                    />
                    <ContentModule
                        :data="contentStats"
                        :current-period="selectedPeriod"
                        :current-start-date="customStartDate"
                        :current-end-date="customEndDate"
                        style="height: 100%"
                        @period-change="handlePeriodChange"
                    />
                    <QuarterlyGoalsModule
                        :data="quarterlyGoals"
                        :current-period="selectedPeriod"
                        :current-start-date="customStartDate"
                        :current-end-date="customEndDate"
                        style="height: 100%"
                        @period-change="handlePeriodChange"
                    />
                </DashboardGrid>
            </div>

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
