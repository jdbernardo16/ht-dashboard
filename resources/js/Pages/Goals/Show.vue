<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ props.goal.title }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Goal ID: #{{ props.goal.id }} •
                        {{ props.goal.quarter }} {{ props.goal.year }}
                    </p>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
                    <button
                        @click="editGoal"
                        class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 shadow-sm"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                            />
                        </svg>
                        Edit Goal
                    </button>
                    <button
                        @click="deleteGoal"
                        class="inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                        Delete
                    </button>
                    <button
                        @click="goBack"
                        class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                        Back
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Progress and Status Card -->
            <div
                class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-8 mb-8 border border-blue-100"
            >
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center"
                                >
                                    <span class="text-white font-bold text-xl"
                                        >{{ progressPercentage }}%</span
                                    >
                                </div>
                            </div>
                            <div>
                                <p
                                    class="text-sm font-medium text-blue-600 mb-1"
                                >
                                    Goal Progress
                                </p>
                                <p class="text-3xl font-bold text-blue-900">
                                    ${{
                                        formatCurrency(props.goal.current_value)
                                    }}
                                    / ${{
                                        formatCurrency(props.goal.target_value)
                                    }}
                                </p>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div
                                class="bg-gradient-to-r from-blue-500 to-indigo-600 h-4 rounded-full transition-all duration-500"
                                :style="{ width: progressPercentage + '%' }"
                            ></div>
                        </div>
                    </div>
                    <div class="flex flex-col items-center space-y-4">
                        <span
                            :class="getStatusClass(props.goal.status)"
                            class="inline-flex items-center px-6 py-3 rounded-full text-lg font-medium"
                        >
                            <svg
                                class="w-5 h-5 mr-2"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            {{ formatStatus(props.goal.status) }}
                        </span>
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Time Remaining</p>
                            <p
                                class="font-medium text-gray-900"
                                :class="daysUntilDeadlineClass"
                            >
                                {{ daysUntilDeadline }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content Area -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Goal Information Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4"
                        >
                            <h2 class="text-xl font-semibold text-white">
                                Goal Information
                            </h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-6">
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Title
                                    </dt>
                                    <dd
                                        class="text-xl font-semibold text-gray-900"
                                    >
                                        {{ props.goal.title }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Description
                                    </dt>
                                    <dd
                                        class="text-gray-900 leading-relaxed bg-gray-50 p-4 rounded-lg"
                                    >
                                        {{
                                            props.goal.description ||
                                            "No description provided"
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Progress
                                    </dt>
                                    <dd class="space-y-3">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span class="text-sm text-gray-600"
                                                >Completion</span
                                            >
                                            <span
                                                class="text-sm font-medium text-gray-900"
                                                >{{ progressPercentage }}%</span
                                            >
                                        </div>
                                        <div
                                            class="w-full bg-gray-200 rounded-full h-3"
                                        >
                                            <div
                                                class="bg-gradient-to-r from-blue-500 to-indigo-600 h-3 rounded-full transition-all duration-500"
                                                :style="{
                                                    width:
                                                        progressPercentage +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Financial Details Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-green-500 to-teal-600 px-6 py-4"
                        >
                            <h2 class="text-xl font-semibold text-white">
                                Financial Details
                            </h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div
                                    class="text-center p-4 bg-green-50 rounded-lg border border-green-200"
                                >
                                    <p
                                        class="text-sm font-medium text-green-600 mb-2"
                                    >
                                        Target Value
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-green-900"
                                    >
                                        ${{
                                            formatCurrency(
                                                props.goal.target_value
                                            )
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200"
                                >
                                    <p
                                        class="text-sm font-medium text-blue-600 mb-2"
                                    >
                                        Current Value
                                    </p>
                                    <p class="text-2xl font-bold text-blue-900">
                                        ${{
                                            formatCurrency(
                                                props.goal.current_value
                                            )
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="text-center p-4 bg-purple-50 rounded-lg border border-purple-200"
                                >
                                    <p
                                        class="text-sm font-medium text-purple-600 mb-2"
                                    >
                                        Remaining
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-purple-900"
                                    >
                                        ${{ formatCurrency(remainingValue) }}
                                    </p>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Timeline Information Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-yellow-500 to-orange-600 px-6 py-4"
                        >
                            <h2 class="text-xl font-semibold text-white">
                                Timeline Information
                            </h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Quarter
                                    </dt>
                                    <dd
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800"
                                        >
                                            {{ props.goal.quarter }}
                                        </span>
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Year
                                    </dt>
                                    <dd
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800"
                                        >
                                            {{ props.goal.year }}
                                        </span>
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Deadline
                                    </dt>
                                    <dd
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ formatDate(props.goal.deadline) }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Days Until Deadline
                                    </dt>
                                    <dd
                                        class="text-lg font-semibold"
                                        :class="daysUntilDeadlineClass"
                                    >
                                        {{ daysUntilDeadline }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Created By
                                    </dt>
                                    <dd class="text-lg text-gray-900">
                                        {{ props.goal.user?.name || "System" }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Created At
                                    </dt>
                                    <dd class="text-lg text-gray-900">
                                        {{
                                            formatDateTime(
                                                props.goal.created_at
                                            )
                                        }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Goal Statistics Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6"
                    >
                        <div
                            class="bg-gradient-to-r from-gray-700 to-gray-900 px-6 py-4"
                        >
                            <h3 class="text-lg font-semibold text-white">
                                Goal Statistics
                            </h3>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Status
                                    </dt>
                                    <dd>
                                        <span
                                            :class="
                                                getStatusClass(
                                                    props.goal.status
                                                )
                                            "
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            {{
                                                formatStatus(props.goal.status)
                                            }}
                                        </span>
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Progress
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ progressPercentage }}%
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Target Value
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        ${{
                                            formatCurrency(
                                                props.goal.target_value
                                            )
                                        }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Current Value
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        ${{
                                            formatCurrency(
                                                props.goal.current_value
                                            )
                                        }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Remaining Value
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        ${{ formatCurrency(remainingValue) }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Quarter
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ props.goal.quarter }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Year
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ props.goal.year }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Deadline
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ formatDate(props.goal.deadline) }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Created By
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ props.goal.user?.name || "System" }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Last Updated
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{
                                            formatDateTime(
                                                props.goal.updated_at
                                            )
                                        }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4"
                        >
                            <h3 class="text-lg font-semibold text-white">
                                Quick Actions
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <button
                                class="w-full flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200"
                            >
                                <svg
                                    class="w-4 h-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                                Update Progress
                            </button>
                            <button
                                class="w-full flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200"
                            >
                                <svg
                                    class="w-4 h-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v6m9 2V9a1 1 0 00-1-1h-4a1 1 0 00-1 1v6m-3-2h6"
                                    />
                                </svg>
                                Generate Report
                            </button>
                            <button
                                class="w-full flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
                            >
                                <svg
                                    class="w-4 h-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m9.032 4.026a9.001 9.001 0 01-7.432 0m9.032-4.026A9.001 9.001 0 0112 3c-4.474 0-8.268 3.12-9.032 7.326m0 0A9.001 9.001 0 0012 21c4.474 0 8.268-3.12 9.032-7.326"
                                    />
                                </svg>
                                View All Goals
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    goal: {
        type: Object,
        required: true,
    },
});

const editGoal = () => {
    router.visit(`/goals/${props.goal.id}/edit`);
};

const deleteGoal = () => {
    if (confirm("Are you sure you want to delete this goal?")) {
        router.delete(`/goals/${props.goal.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                router.visit("/goals");
            },
            onError: (errors) => {
                console.error("Error deleting goal:", errors);
                if (errors.message?.includes("403")) {
                    alert("You don't have permission to delete this goal.");
                }
            },
        });
    }
};

const goBack = () => {
    router.visit("/goals");
};

// Utility functions
const formatCurrency = (value) => {
    return parseFloat(value || 0).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString();
};

const formatDateTime = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleString();
};

const formatStatus = (status) => {
    if (!status) return "Unknown";
    return status.charAt(0).toUpperCase() + status.slice(1).replace("_", " ");
};

const getStatusClass = (status) => {
    const classes = {
        draft: "bg-gray-100 text-gray-800",
        published: "bg-green-100 text-green-800",
        archived: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const progressPercentage = computed(() => {
    if (!props.goal.target_value || props.goal.target_value <= 0) {
        return 0;
    }
    return Math.min(
        100,
        Math.round((props.goal.current_value / props.goal.target_value) * 100)
    );
});

const remainingValue = computed(() => {
    return Math.max(0, props.goal.target_value - props.goal.current_value);
});

const daysUntilDeadline = computed(() => {
    if (!props.goal.deadline) return "No deadline set";

    const deadlineDate = new Date(props.goal.deadline);
    const today = new Date();

    if (deadlineDate < today) {
        return "Overdue";
    }

    const diffTime = deadlineDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    return diffDays === 0
        ? "Due today"
        : `${diffDays} day${diffDays > 1 ? "s" : ""}`;
});

const daysUntilDeadlineClass = computed(() => {
    if (!props.goal.deadline) return "text-gray-600";

    const deadlineDate = new Date(props.goal.deadline);
    const today = new Date();

    if (deadlineDate < today) {
        return "text-red-600 font-semibold";
    }

    const diffTime = deadlineDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays <= 7) {
        return "text-orange-600 font-semibold";
    }

    return "text-green-600";
});
</script>
