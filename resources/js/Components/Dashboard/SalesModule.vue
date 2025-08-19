<template>
    <DashboardModule title="Sales Overview" :loading="loading" :error="error">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-lg p-6 border">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Revenue Trend
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Last 7 days</span>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                        >
                            +{{ sales.growth }}%
                        </span>
                    </div>
                </div>
                <div class="relative h-64">
                    <canvas ref="revenueChartRef"></canvas>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white rounded-lg p-4 border">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Total Revenue
                                </p>
                                <p class="text-2xl font-bold text-gray-900">
                                    ${{ formatCurrency(sales.totalRevenue) }}
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
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="text-sm text-green-600 font-medium"
                                >+{{ sales.growth }}%</span
                            >
                            <span class="text-sm text-gray-500">
                                from last month</span
                            >
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-4 border">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    New Customers
                                </p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ sales.newCustomers }}
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
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="text-sm text-blue-600 font-medium"
                                >+{{ sales.customerGrowth }}%</span
                            >
                            <span class="text-sm text-gray-500">
                                from last month</span
                            >
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-4 border">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">
                        Sales Breakdown
                    </h4>
                    <div class="space-y-3">
                        <div
                            v-for="item in sales.breakdown"
                            :key="item.category"
                        >
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-gray-600">{{
                                    item.category
                                }}</span>
                                <span class="text-sm font-medium text-gray-900"
                                    >${{ formatCurrency(item.amount) }}</span
                                >
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div
                                    class="bg-blue-500 h-2 rounded-full"
                                    :style="{
                                        width: `${
                                            (item.amount / sales.totalRevenue) *
                                            100
                                        }%`,
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="mt-6 bg-white rounded-lg border">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">
                    Recent Sales
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Customer
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Product
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Amount
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Date
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="sale in sales.recentSales" :key="sale.id">
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                            >
                                {{ sale.customer }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                            >
                                {{ sale.product }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                            >
                                ${{ formatCurrency(sale.amount) }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                            >
                                {{ formatDate(sale.date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="{
                                        'bg-green-100 text-green-800':
                                            sale.status === 'completed',
                                        'bg-yellow-100 text-yellow-800':
                                            sale.status === 'pending',
                                        'bg-blue-100 text-blue-800':
                                            sale.status === 'processing',
                                    }"
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{ sale.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
    sales: {
        type: Object,
        default: () => ({
            totalRevenue: 0,
            growth: 0,
            newCustomers: 0,
            customerGrowth: 0,
            breakdown: [],
            recentSales: [],
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
});

const revenueChartRef = ref(null);
let chartInstance = null;

const sales = computed(
    () =>
        props.sales || {
            totalRevenue: 125000,
            growth: 12.5,
            newCustomers: 45,
            customerGrowth: 8.3,
            breakdown: [
                { category: "Product Sales", amount: 75000 },
                { category: "Services", amount: 35000 },
                { category: "Subscriptions", amount: 15000 },
            ],
            recentSales: [
                {
                    id: 1,
                    customer: "Acme Corp",
                    product: "Enterprise Plan",
                    amount: 5000,
                    date: "2024-01-15",
                    status: "completed",
                },
                {
                    id: 2,
                    customer: "Tech Solutions",
                    product: "Pro Plan",
                    amount: 2500,
                    date: "2024-01-14",
                    status: "completed",
                },
                {
                    id: 3,
                    customer: "StartupXYZ",
                    product: "Starter Plan",
                    amount: 1000,
                    date: "2024-01-13",
                    status: "pending",
                },
            ],
        }
);

const createChart = () => {
    if (!revenueChartRef.value) return;

    const ctx = revenueChartRef.value.getContext("2d");

    if (chartInstance) {
        chartInstance.destroy();
    }

    const labels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
    const data = [12000, 19000, 15000, 25000, 22000, 30000, 28000];

    chartInstance = new Chart(ctx, {
        type: "line",
        data: {
            labels,
            datasets: [
                {
                    label: "Revenue",
                    data,
                    borderColor: "#3B82F6",
                    backgroundColor: "rgba(59, 130, 246, 0.1)",
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return "$" + value / 1000 + "k";
                        },
                    },
                },
            },
        },
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-US", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
    });
};

onMounted(() => {
    createChart();
});

watch(
    () => props.sales,
    () => {
        createChart();
    },
    { deep: true }
);
</script>
