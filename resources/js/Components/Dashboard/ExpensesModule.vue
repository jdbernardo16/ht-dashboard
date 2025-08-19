<template>
    <DashboardModule title="Expense Tracking" :loading="loading" :error="error">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Total Expenses -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg p-6 border">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Total Expenses
                    </h3>
                    <div class="text-3xl font-bold text-gray-900 mb-2">
                        ${{ formatCurrency(expenses.total) }}
                    </div>
                    <div class="flex items-center">
                        <span
                            :class="
                                expenses.change >= 0
                                    ? 'text-red-600'
                                    : 'text-green-600'
                            "
                            class="text-sm font-medium"
                        >
                            {{ expenses.change >= 0 ? "+" : ""
                            }}{{ expenses.change }}%
                        </span>
                        <span class="text-sm text-gray-500 ml-1"
                            >vs last month</span
                        >
                    </div>
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600"
                                >This Month</span
                            >
                            <span class="text-sm font-medium"
                                >${{ formatCurrency(expenses.monthly) }}</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600"
                                >Average/Day</span
                            >
                            <span class="text-sm font-medium"
                                >${{
                                    formatCurrency(expenses.dailyAverage)
                                }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Category Breakdown -->
                <div class="bg-white rounded-lg p-6 border mt-4">
                    <h4 class="text-md font-semibold text-gray-900 mb-4">
                        By Category
                    </h4>
                    <div class="space-y-3">
                        <div
                            v-for="category in expenses.categories"
                            :key="category.name"
                        >
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-gray-600">{{
                                    category.name
                                }}</span>
                                <span class="text-sm font-medium text-gray-900"
                                    >${{
                                        formatCurrency(category.amount)
                                    }}</span
                                >
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div
                                    class="h-2 rounded-full"
                                    :style="{
                                        width: `${
                                            (category.amount / expenses.total) *
                                            100
                                        }%`,
                                        backgroundColor: category.color,
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expense Chart -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg p-6 border">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Expense Trend
                        </h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500"
                                >Last 30 days</span
                            >
                        </div>
                    </div>
                    <div class="relative h-80">
                        <canvas ref="expenseChartRef"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Expenses -->
        <div class="mt-6 bg-white rounded-lg border">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">
                    Recent Expenses
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Description
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Category
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
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="expense in expenses.recent"
                            :key="expense.id"
                        >
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                            >
                                {{ expense.description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :style="{
                                        backgroundColor:
                                            expense.categoryColor + '20',
                                        color: expense.categoryColor,
                                    }"
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{ expense.category }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                            >
                                ${{ formatCurrency(expense.amount) }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                            >
                                {{ formatDate(expense.date) }}
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
    data: {
        type: Object,
        default: () => ({
            expenses_by_category: [],
            daily_expenses: [],
            summary: {
                total_expenses: 0,
                budget: 5000,
                remaining: 0,
                percentage_used: 0,
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
});

const expenseChartRef = ref(null);
let chartInstance = null;

const expenses = computed(() => {
    if (!props.data.summary) {
        return {
            total: 28450,
            change: 5.2,
            monthly: 28450,
            dailyAverage: 948,
            categories: [
                { name: "Software & Tools", amount: 8500, color: "#3B82F6" },
                { name: "Marketing", amount: 6200, color: "#10B981" },
                { name: "Office Supplies", amount: 4100, color: "#F59E0B" },
                { name: "Travel", amount: 5600, color: "#EF4444" },
                { name: "Utilities", amount: 4050, color: "#8B5CF6" },
            ],
            recent: [
                {
                    id: 1,
                    description: "Adobe Creative Suite",
                    category: "Software & Tools",
                    amount: 599,
                    date: "2024-01-15",
                    categoryColor: "#3B82F6",
                },
                {
                    id: 2,
                    description: "Google Ads Campaign",
                    category: "Marketing",
                    amount: 1200,
                    date: "2024-01-14",
                    categoryColor: "#10B981",
                },
                {
                    id: 3,
                    description: "Office Supplies",
                    category: "Office Supplies",
                    amount: 250,
                    date: "2024-01-13",
                    categoryColor: "#F59E0B",
                },
            ],
        };
    }

    // Calculate change percentage (mock for now since we don't have historical data)
    const change = Math.random() * 10 - 5; // Random change between -5% and +5%
    const dailyExpenses = props.data.daily_expenses || [];
    const daysInMonth = new Date().getDate();
    const dailyAverage =
        daysInMonth > 0 ? props.data.summary.total_expenses / daysInMonth : 0;

    return {
        total: props.data.summary.total_expenses,
        change: Math.round(change * 10) / 10,
        monthly: props.data.summary.total_expenses,
        dailyAverage: Math.round(dailyAverage),
        categories: props.data.expenses_by_category.map((expense) => ({
            name: expense.category,
            amount: expense.amount,
            color: expense.color,
        })),
        recent: [], // We don't have recent expenses data in the current structure
    };
});

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

const createChart = () => {
    if (!expenseChartRef.value) return;

    const ctx = expenseChartRef.value.getContext("2d");

    if (chartInstance) {
        chartInstance.destroy();
    }

    const dailyExpenses = props.data.daily_expenses || [];
    const labels =
        dailyExpenses.length > 0
            ? dailyExpenses.map((item) =>
                  new Date(item.date).toLocaleDateString("en-US", {
                      month: "short",
                      day: "numeric",
                  })
              )
            : Array.from({ length: 30 }, (_, i) => `Day ${i + 1}`);
    const data =
        dailyExpenses.length > 0
            ? dailyExpenses.map((item) => item.expense)
            : Array.from(
                  { length: 30 },
                  () => Math.floor(Math.random() * 2000) + 500
              );

    chartInstance = new Chart(ctx, {
        type: "bar",
        data: {
            labels,
            datasets: [
                {
                    label: "Daily Expenses",
                    data,
                    backgroundColor: "rgba(59, 130, 246, 0.8)",
                    borderColor: "#3B82F6",
                    borderWidth: 1,
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
                            return "$" + value;
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
    () => props.data,
    () => {
        createChart();
    },
    { deep: true }
);
</script>
