<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { computed, ref, onMounted, watch } from "vue";
import axios from "axios";

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

// Time period state
const selectedPeriod = ref("daily");
const customStartDate = ref("");
const customEndDate = ref("");
const dashboardData = ref(props.dashboardData);
const loading = ref(false);
const error = ref(null);

// Computed properties for dashboard modules
const dailySummary = computed(() => dashboardData.value.dailySummary);
const activityDistribution = computed(
    () => dashboardData.value.activityDistribution
);
const salesMetrics = computed(() => dashboardData.value.salesMetrics);
const expenses = computed(() => dashboardData.value.expenses);
const contentStats = computed(() => dashboardData.value.contentStats);
const quarterlyGoals = computed(() => dashboardData.value.quarterlyGoals);

// Fetch dashboard data based on time period
const fetchDashboardData = async () => {
    loading.value = true;
    error.value = null;

    try {
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

        const response = await axios.get("/api/dashboard/data", { params });
        dashboardData.value = response.data;
    } catch (err) {
        console.error("Error fetching dashboard data:", err);
        error.value = "Failed to load dashboard data. Please try again.";
    } finally {
        loading.value = false;
    }
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

    fetchDashboardData();
};

// Watch for period changes
watch(selectedPeriod, fetchDashboardData);

// Initial data fetch on component mount
onMounted(() => {
    // Set default custom dates to current month if needed
    if (
        selectedPeriod.value === "custom" &&
        !customStartDate.value &&
        !customEndDate.value
    ) {
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

        customStartDate.value = firstDay.toISOString().split("T")[0];
        customEndDate.value = lastDay.toISOString().split("T")[0];
    }
});
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
            <!-- Loading and Error States -->
            <div v-if="loading" class="text-center py-8">
                <div
                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"
                ></div>
                <p class="mt-2 text-gray-600">Loading dashboard data...</p>
            </div>

            <div v-else-if="error" class="text-center py-8 text-red-600">
                <p>{{ error }}</p>
                <button
                    @click="fetchDashboardData"
                    class="mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                >
                    Retry
                </button>
            </div>

            <div v-else>
                <DashboardGrid>
                    <!-- Row 1 -->
                    <DailySummaryModule
                        :summary="dailySummary"
                        :loading="loading"
                        :error="error"
                        style="height: 100%"
                        @period-change="handlePeriodChange"
                    />
                    <ActivityDistributionModule
                        :data="activityDistribution"
                        :loading="loading"
                        :error="error"
                        style="height: 100%"
                        @period-change="handlePeriodChange"
                    />
                    <SalesModule
                        :data="salesMetrics"
                        :loading="loading"
                        :error="error"
                        style="height: 100%"
                        @period-change="handlePeriodChange"
                    />

                    <!-- Row 2 -->
                    <ExpensesModule
                        :data="expenses"
                        :loading="loading"
                        :error="error"
                        style="height: 100%"
                        @period-change="handlePeriodChange"
                    />
                    <ContentModule
                        :data="contentStats"
                        :loading="loading"
                        :error="error"
                        style="height: 100%"
                        @period-change="handlePeriodChange"
                    />
                    <QuarterlyGoalsModule
                        :data="quarterlyGoals"
                        :loading="loading"
                        :error="error"
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
