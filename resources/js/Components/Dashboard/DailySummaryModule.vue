<template>
    <DashboardModule
        title="Summary"
        :loading="loading"
        :error="error"
        :show-time-period="true"
        @period-change="$emit('period-change', $event)"
    >
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Tasks Summary -->
            <div class="bg-white rounded-lg p-4 border">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-600">Tasks</h3>
                    <svg
                        class="w-5 h-5 text-blue-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                        />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ summary.tasks.completed }}/{{ summary.tasks.total }}
                </div>
                <p class="text-sm text-gray-500">Completed today</p>
                <div class="mt-2">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div
                            class="bg-blue-500 h-2 rounded-full"
                            :style="{
                                width: `${
                                    (summary.tasks.completed /
                                        summary.tasks.total) *
                                    100
                                }%`,
                            }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Revenue -->
            <div class="bg-white rounded-lg p-4 border">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-600">Revenue</h3>
                    <svg
                        class="w-5 h-5 text-green-500"
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
                <div class="text-2xl font-bold text-gray-900">
                    ${{ formatCurrency(summary.revenue.today) }}
                </div>
                <p class="text-sm text-gray-500">Today's revenue</p>
                <div class="mt-2 flex items-center">
                    <span
                        :class="revenueChange.colorClass"
                        class="text-sm font-medium"
                    >
                        {{ revenueChange.formatted }}
                    </span>
                    <span class="text-sm text-gray-500 ml-1">vs yesterday</span>
                </div>
            </div>

            <!-- Active Projects -->
            <div class="bg-white rounded-lg p-4 border">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-600">
                        Active Projects
                    </h3>
                    <svg
                        class="w-5 h-5 text-purple-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                        />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ summary.projects.active }}
                </div>
                <p class="text-sm text-gray-500">Currently active</p>
                <div class="mt-2">
                    <div class="text-sm text-gray-600">
                        {{ summary.projects.completed }} completed this week
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Row -->
        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-lg font-semibold text-gray-900">
                    {{ summary.hoursWorked }}
                </div>
                <div class="text-sm text-gray-500">Hours Worked</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-semibold text-gray-900">
                    {{ summary.meetings }}
                </div>
                <div class="text-sm text-gray-500">Meetings</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-semibold text-gray-900">
                    {{ summary.emails }}
                </div>
                <div class="text-sm text-gray-500">Emails Sent</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-semibold text-gray-900">
                    {{ summary.deadlines }}
                </div>
                <div class="text-sm text-gray-500">Deadlines</div>
            </div>
        </div>
    </DashboardModule>
</template>

<script setup>
import { computed } from "vue";
import DashboardModule from "./DashboardModule.vue";
import { formatPercentageWithColor } from "../../Utils/utils.js";

const props = defineProps({
    summary: {
        type: Object,
        required: true,
        default: () => ({
            tasks: { completed: 0, total: 0 },
            revenue: { today: 0, change: 0 },
            projects: { active: 0, completed: 0 },
            hoursWorked: 0,
            meetings: 0,
            emails: 0,
            deadlines: 0,
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

defineEmits(["period-change"]);

const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-US", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

// Computed properties for percentage formatting
const revenueChange = computed(() => {
    return formatPercentageWithColor(props.summary.revenue.change);
});
</script>
