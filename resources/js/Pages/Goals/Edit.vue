<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Edit Goal
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Update the goal objectives and tracking metrics
                    </p>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
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
                        Back to Goals
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Form Container -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
            >
                <!-- Form Header -->
                <div
                    class="bg-gradient-to-r from-teal-500 to-cyan-600 px-6 py-4"
                >
                    <h2 class="text-xl font-semibold text-white">
                        Goal Details
                    </h2>
                    <p class="text-teal-100 text-sm mt-1">
                        Update your goal objectives and tracking metrics
                    </p>
                </div>

                <div class="p-6 text-gray-900">
                    <form @submit.prevent="submitForm" class="space-y-8">
                        <!-- Basic Information Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-teal-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-teal-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Basic Information
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Define the goal objectives
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <!-- Title -->
                                <div>
                                    <label
                                        for="title"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Title *</label
                                    >
                                    <input
                                        type="text"
                                        id="title"
                                        v-model="form.title"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 px-3 py-2 transition-colors"
                                    />
                                    <p
                                        v-if="form.errors.title"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.title }}
                                    </p>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label
                                        for="description"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Description *</label
                                    >
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="4"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 px-3 py-2 transition-colors"
                                        placeholder="Describe what you want to achieve with this goal..."
                                    ></textarea>
                                    <p
                                        v-if="form.errors.description"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.description }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Goal Metrics Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-teal-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-teal-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Goal Metrics
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Set target and current values to track
                                        progress
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Target Value -->
                                <div>
                                    <label
                                        for="target_value"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Target Value *</label
                                    >
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <svg
                                                class="h-5 w-5 text-gray-400"
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
                                        <input
                                            type="number"
                                            id="target_value"
                                            v-model="form.target_value"
                                            required
                                            min="0"
                                            step="0.01"
                                            class="mt-1 block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.target_value"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.target_value }}
                                    </p>
                                </div>

                                <!-- Current Value -->
                                <div>
                                    <label
                                        for="current_value"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Current Value *</label
                                    >
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <svg
                                                class="h-5 w-5 text-gray-400"
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
                                        </div>
                                        <input
                                            type="number"
                                            id="current_value"
                                            v-model="form.current_value"
                                            required
                                            min="0"
                                            step="0.01"
                                            class="mt-1 block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.current_value"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.current_value }}
                                    </p>
                                </div>

                                <!-- Budget -->
                                <div>
                                    <label
                                        for="budget"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Budget ($)</label
                                    >
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <span
                                                class="text-gray-500 sm:text-sm"
                                                >$</span
                                            >
                                        </div>
                                        <input
                                            type="number"
                                            id="budget"
                                            v-model="form.budget"
                                            min="0"
                                            step="0.01"
                                            class="mt-1 block w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.budget"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.budget }}
                                    </p>
                                </div>

                                <!-- Labor Hours -->
                                <div>
                                    <label
                                        for="labor_hours"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Labor Hours</label
                                    >
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <svg
                                                class="h-5 w-5 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                        </div>
                                        <input
                                            type="number"
                                            id="labor_hours"
                                            v-model="form.labor_hours"
                                            min="0"
                                            step="0.01"
                                            class="mt-1 block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.labor_hours"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.labor_hours }}
                                    </p>
                                </div>
                            </div>

                            <!-- Progress Indicator -->
                            <div class="mt-6">
                                <div
                                    class="bg-white rounded-lg border border-gray-200 p-4"
                                >
                                    <div
                                        class="flex items-center justify-between mb-2"
                                    >
                                        <span
                                            class="text-sm font-medium text-gray-700"
                                            >Current Progress</span
                                        >
                                        <span class="text-sm text-gray-500">
                                            {{
                                                Math.round(progressPercentage)
                                            }}%
                                        </span>
                                    </div>
                                    <div
                                        class="w-full bg-gray-200 rounded-full h-2"
                                    >
                                        <div
                                            class="bg-gradient-to-r from-teal-500 to-cyan-600 h-2 rounded-full transition-all duration-300"
                                            :style="{
                                                width: progressPercentage + '%',
                                            }"
                                        ></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">
                                        {{ form.current_value || 0 }} of
                                        {{ form.target_value || 0 }} achieved
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-teal-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-teal-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Timeline
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Set the time period and deadline for
                                        this goal
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Quarter -->
                                <div>
                                    <label
                                        for="quarter"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Quarter *</label
                                    >
                                    <select
                                        id="quarter"
                                        v-model="form.quarter"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 px-3 py-2 transition-colors"
                                    >
                                        <option value="Q1">Q1</option>
                                        <option value="Q2">Q2</option>
                                        <option value="Q3">Q3</option>
                                        <option value="Q4">Q4</option>
                                    </select>
                                    <p
                                        v-if="form.errors.quarter"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.quarter }}
                                    </p>
                                </div>

                                <!-- Year -->
                                <div>
                                    <label
                                        for="year"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Year *</label
                                    >
                                    <input
                                        type="number"
                                        id="year"
                                        v-model="form.year"
                                        required
                                        min="2020"
                                        max="2030"
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 px-3 py-2 transition-colors"
                                    />
                                    <p
                                        v-if="form.errors.year"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.year }}
                                    </p>
                                </div>

                                <!-- Deadline -->
                                <div>
                                    <label
                                        for="deadline"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Deadline *</label
                                    >
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <svg
                                                class="h-5 w-5 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                        </div>
                                        <input
                                            type="date"
                                            id="deadline"
                                            v-model="form.deadline"
                                            required
                                            class="mt-1 block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.deadline"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.deadline }}
                                    </p>
                                </div>
                            </div>

                            <!-- Timeline Status -->
                            <div class="mt-6">
                                <div
                                    class="bg-white rounded-lg border border-gray-200 p-4"
                                >
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full"
                                                :class="{
                                                    'bg-green-100': !isOverdue,
                                                    'bg-red-100': isOverdue,
                                                }"
                                            >
                                                <svg
                                                    class="w-4 h-4"
                                                    :class="{
                                                        'text-green-600':
                                                            !isOverdue,
                                                        'text-red-600':
                                                            isOverdue,
                                                    }"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                    ></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{
                                                    isOverdue
                                                        ? "Overdue"
                                                        : "On Track"
                                                }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ timeRemaining }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div
                            class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200"
                        >
                            <button
                                type="button"
                                @click="goBack"
                                class="inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
                                :disabled="form.processing"
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
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center justify-center px-6 py-3 bg-teal-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50"
                            >
                                <svg
                                    v-if="!form.processing"
                                    class="w-4 h-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                <svg
                                    v-if="form.processing"
                                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                {{
                                    form.processing
                                        ? "Updating..."
                                        : "Update Goal"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    goal: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    title: props.goal.title || "",
    description: props.goal.description || "",
    target_value: props.goal.target_value || 0,
    current_value: props.goal.current_value || 0,
    budget: props.goal.budget || 0,
    labor_hours: props.goal.labor_hours || 0,
    quarter: props.goal.quarter || "Q1",
    year: props.goal.year || new Date().getFullYear(),
    deadline: props.goal.deadline ? props.goal.deadline.split("T")[0] : "",
});

// Computed properties for progress tracking
const progressPercentage = computed(() => {
    if (!form.target_value || form.target_value === 0) return 0;
    return Math.min((form.current_value / form.target_value) * 100, 100);
});

const isOverdue = computed(() => {
    if (!form.deadline) return false;
    return new Date(form.deadline) < new Date();
});

const timeRemaining = computed(() => {
    if (!form.deadline) return "No deadline set";

    const deadline = new Date(form.deadline);
    const now = new Date();
    const diffTime = deadline - now;

    if (diffTime < 0) {
        const daysOverdue = Math.abs(
            Math.ceil(diffTime / (1000 * 60 * 60 * 24))
        );
        return `${daysOverdue} days overdue`;
    }

    const daysRemaining = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    if (daysRemaining === 0) return "Due today";
    if (daysRemaining === 1) return "1 day remaining";
    return `${daysRemaining} days remaining`;
});

const submitForm = () => {
    form.put(`/goals/${props.goal.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(`/goals/${props.goal.id}`);
        },
        onError: (errors) => {
            console.error("Error updating goal:", errors);
        },
    });
};

const goBack = () => {
    router.visit(`/goals/${props.goal.id}`);
};
</script>
