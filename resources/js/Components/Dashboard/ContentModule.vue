<template>
    <DashboardModule
        title="Content Performance"
        :loading="loading"
        :error="error"
        :show-time-period="true"
        :current-period="currentPeriod"
        :current-start-date="currentStartDate"
        :current-end-date="currentEndDate"
        @period-change="$emit('period-change', $event)"
    >
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Performance Metrics -->
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white rounded-lg p-4 border">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Total Views
                                </p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{
                                        formatNumber(content.metrics.totalViews)
                                    }}
                                </p>
                            </div>
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg
                                    class="w-6 h-6 text-blue-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span
                                :class="viewsGrowth.colorClass"
                                class="text-sm font-medium"
                            >
                                {{ viewsGrowth.formatted }}
                            </span>
                            <span class="text-sm text-gray-500">
                                vs last month</span
                            >
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-4 border">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Engagement
                                </p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ content.metrics.engagementRate }}%
                                </p>
                            </div>
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg
                                    class="w-6 h-6 text-green-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="text-sm text-green-600 font-medium"
                                >+{{ content.metrics.engagementGrowth }}%</span
                            >
                            <span class="text-sm text-gray-500">
                                vs last month</span
                            >
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-4 border">
                    <h4 class="text-md font-semibold text-gray-900 mb-3">
                        Top Performing Content
                    </h4>
                    <div class="space-y-3">
                        <div
                            v-for="item in content.topContent"
                            :key="item.id"
                            class="flex items-center justify-between"
                        >
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ item.title }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ item.type }} •
                                    {{ formatDate(item.date) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ formatNumber(item.views) }} views
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ item.engagement }}% engagement
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Chart -->
            <div class="bg-white rounded-lg p-6 border">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Content Performance
                </h3>
                <div class="relative h-64">
                    <canvas ref="contentChartRef"></canvas>
                </div>
            </div>
        </div>

        <!-- Content Types -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div
                v-for="type in content.types"
                :key="type.name"
                class="bg-white rounded-lg p-4 border"
            >
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-gray-900">
                        {{ type.name }}
                    </h4>
                    <div
                        class="p-2 rounded-lg"
                        :style="{ backgroundColor: type.color + '20' }"
                    >
                        <svg
                            class="w-4 h-4"
                            :style="{ color: type.color }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="type.icon"
                            />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ formatNumber(type.count) }}
                </div>
                <p class="text-sm text-gray-500">{{ type.name }} published</p>
                <div class="mt-2">
                    <span
                        class="text-sm font-medium"
                        :style="{
                            color: type.growth >= 0 ? '#10B981' : '#EF4444',
                        }"
                    >
                        {{ type.growth >= 0 ? "+" : "" }}{{ type.growth }}%
                    </span>
                    <span class="text-sm text-gray-500"> vs last month</span>
                </div>
            </div>
        </div>
    </DashboardModule>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Chart, registerables } from "chart.js";
import { formatPercentageWithColor } from "../../Utils/utils.js";
import DashboardModule from "./DashboardModule.vue";

// Register Chart.js components
Chart.register(...registerables);

const props = defineProps({
    data: {
        type: Object,
        default: () => ({
            content_by_type: [],
            recent_content: [],
            summary: {
                total_content: 0,
                total_views: 0,
                average_views: 0,
            },
            month: "",
        }),
    },
    loading: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
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

defineEmits(["period-change"]);

const contentChartRef = ref(null);
let chartInstance = null;

const content = computed(() => {
    if (!props.data.summary) {
        return {
            metrics: {
                totalViews: 125000,
                viewsGrowth: 15.3,
                engagementRate: 8.7,
                engagementGrowth: 12.1,
            },
            topContent: [
                {
                    id: 1,
                    title: "Getting Started Guide",
                    type: "Blog Post",
                    date: "2024-01-15",
                    views: 15420,
                    engagement: 12.5,
                },
                {
                    id: 2,
                    title: "Product Launch Video",
                    type: "Video",
                    date: "2024-01-12",
                    views: 8930,
                    engagement: 9.8,
                },
                {
                    id: 3,
                    title: "Best Practices Guide",
                    type: "Article",
                    date: "2024-01-10",
                    views: 6720,
                    engagement: 15.2,
                },
            ],
            types: [
                {
                    name: "Blog Posts",
                    count: 45,
                    growth: 12,
                    color: "#3B82F6",
                    icon: "M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253",
                },
                {
                    name: "Videos",
                    count: 12,
                    growth: 8,
                    color: "#EF4444",
                    icon: "M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z",
                },
                {
                    name: "Infographics",
                    count: 8,
                    growth: -5,
                    color: "#F59E0B",
                    icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
                },
            ],
        };
    }

    // Calculate growth rates (mock for now since we don't have historical data)
    const viewsGrowth = Math.random() * 20 + 5; // Random growth between 5% and 25%
    const engagementGrowth = Math.random() * 15 + 2; // Random growth between 2% and 17%
    const engagementRate = Math.random() * 10 + 5; // Random rate between 5% and 15%

    // Map content types with icons and colors
    const typeIcons = {
        Blog: "M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253",
        Video: "M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z",
        Article:
            "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
        Social: "M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z",
    };

    const typeColors = ["#3B82F6", "#EF4444", "#F59E0B", "#10B981", "#8B5CF6"];

    return {
        metrics: {
            totalViews: props.data.summary.total_views,
            viewsGrowth: Math.round(viewsGrowth * 10) / 10,
            engagementRate: Math.round(engagementRate * 10) / 10,
            engagementGrowth: Math.round(engagementGrowth * 10) / 10,
        },
        topContent: props.data.recent_content.map((item, index) => ({
            id: index + 1,
            title: item.title,
            type: item.type,
            date: item.created_at,
            views: item.views || 0,
            engagement: Math.round((Math.random() * 10 + 5) * 10) / 10, // Mock engagement rate
        })),
        types: props.data.content_by_type.map((type, index) => ({
            name: type.type,
            count: type.count,
            growth: Math.round((Math.random() * 20 - 5) * 10) / 10, // Random growth between -5% and 15%
            color: typeColors[index % typeColors.length],
            icon: typeIcons[type.type] || typeIcons["Article"],
        })),
    };
});

const formatNumber = (value) => {
    return new Intl.NumberFormat("en-US").format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
    });
};

// Computed properties for percentage formatting
const viewsGrowth = computed(() => {
    return formatPercentageWithColor(content.value.metrics.viewsGrowth);
});

const engagementGrowth = computed(() => {
    return formatPercentageWithColor(content.value.metrics.engagementGrowth);
});

const createChart = () => {
    if (!contentChartRef.value) return;

    const ctx = contentChartRef.value.getContext("2d");

    if (chartInstance) {
        chartInstance.destroy();
    }

    const labels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun"];
    const viewsData = [15000, 22000, 18000, 35000, 28000, 42000];
    const engagementData = [5.2, 6.8, 7.1, 8.9, 8.2, 8.7];

    chartInstance = new Chart(ctx, {
        type: "line",
        data: {
            labels,
            datasets: [
                {
                    label: "Views",
                    data: viewsData,
                    borderColor: "#3B82F6",
                    backgroundColor: "rgba(59, 130, 246, 0.1)",
                    borderWidth: 2,
                    fill: true,
                    yAxisID: "y",
                },
                {
                    label: "Engagement %",
                    data: engagementData,
                    borderColor: "#10B981",
                    backgroundColor: "rgba(16, 185, 129, 0.1)",
                    borderWidth: 2,
                    fill: false,
                    yAxisID: "y1",
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                },
            },
            scales: {
                y: {
                    type: "linear",
                    display: true,
                    position: "left",
                    ticks: {
                        callback: function (value) {
                            return value / 1000 + "k";
                        },
                    },
                },
                y1: {
                    type: "linear",
                    display: true,
                    position: "right",
                    ticks: {
                        callback: function (value) {
                            return value + "%";
                        },
                    },
                    grid: {
                        drawOnChartArea: false,
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
    () => props.data,
    () => {
        createChart();
    },
    { deep: true }
);
</script>
