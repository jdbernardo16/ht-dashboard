<template>
    <DashboardModule
        title="Activity Distribution"
        :loading="loading"
        :error="error"
    >
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Pie Chart -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg p-6 border">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Activity Breakdown
                    </h3>
                    <div class="relative h-64">
                        <canvas ref="pieChartRef"></canvas>
                    </div>
                </div>
            </div>

            <!-- Activity List -->
            <div class="space-y-4">
                <div class="bg-white rounded-lg p-4 border">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">
                        Top Activities
                    </h4>
                    <div class="space-y-3">
                        <div
                            v-for="activity in activities"
                            :key="activity.name"
                            class="flex items-center justify-between"
                        >
                            <div class="flex items-center">
                                <div
                                    class="w-3 h-3 rounded-full mr-2"
                                    :style="{ backgroundColor: activity.color }"
                                ></div>
                                <span class="text-sm text-gray-700">{{
                                    activity.name
                                }}</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ activity.percentage }}%
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ activity.hours }}h
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Stats -->
                <div class="bg-white rounded-lg p-4 border">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">
                        Summary
                    </h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600"
                                >Total Activities</span
                            >
                            <span class="text-sm font-medium text-gray-900">{{
                                totalActivities
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600"
                                >Most Active</span
                            >
                            <span class="text-sm font-medium text-gray-900">{{
                                mostActiveActivity
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600"
                                >Avg. per Day</span
                            >
                            <span class="text-sm font-medium text-gray-900"
                                >{{ averagePerDay }}h</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardModule>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Chart, registerables } from "chart.js";
import DashboardModule from "./DashboardModule.vue";

// Register Chart.js components
Chart.register(...registerables);

const props = defineProps({
    activities: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
});

const pieChartRef = ref(null);
let chartInstance = null;

const activities = computed(
    () =>
        props.activities || [
            {
                name: "Development",
                hours: 4.5,
                percentage: 35,
                color: "#3B82F6",
            },
            { name: "Meetings", hours: 2.0, percentage: 15, color: "#10B981" },
            { name: "Planning", hours: 3.0, percentage: 23, color: "#F59E0B" },
            { name: "Testing", hours: 1.5, percentage: 12, color: "#EF4444" },
            {
                name: "Documentation",
                hours: 1.0,
                percentage: 8,
                color: "#8B5CF6",
            },
            { name: "Other", hours: 1.0, percentage: 7, color: "#6B7280" },
        ]
);

const totalActivities = computed(() => {
    return activities.value.reduce((sum, activity) => sum + activity.hours, 0);
});

const mostActiveActivity = computed(() => {
    if (!activities.value.length) return "N/A";
    return activities.value.reduce((max, activity) =>
        activity.hours > max.hours ? activity : max
    ).name;
});

const averagePerDay = computed(() => {
    return (totalActivities.value / 7).toFixed(1);
});

const createChart = () => {
    if (!pieChartRef.value || !activities.value.length) return;

    const ctx = pieChartRef.value.getContext("2d");

    if (chartInstance) {
        chartInstance.destroy();
    }

    chartInstance = new Chart(ctx, {
        type: "pie",
        data: {
            labels: activities.value.map((a) => a.name),
            datasets: [
                {
                    data: activities.value.map((a) => a.percentage),
                    backgroundColor: activities.value.map((a) => a.color),
                    borderWidth: 2,
                    borderColor: "#ffffff",
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12,
                        },
                    },
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const activity =
                                activities.value[context.dataIndex];
                            return `${activity.name}: ${activity.hours}h (${activity.percentage}%)`;
                        },
                    },
                },
            },
        },
    });
};

onMounted(() => {
    createChart();
});

watch(
    () => props.activities,
    () => {
        createChart();
    },
    { deep: true }
);
</script>
