<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Create New Goal
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Set and track business goals and targets
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
                        Define your goal objectives and tracking metrics
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
                                        class="block text-sm font-medium text-gray-700"
                                        >Title *</label
                                    >
                                    <input
                                        type="text"
                                        id="title"
                                        v-model="form.title"
                                        required
                                        placeholder="Enter goal title..."
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="errors.title"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.title }}
                                    </p>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label
                                        for="description"
                                        class="block text-sm font-medium text-gray-700"
                                        >Description</label
                                    >
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        placeholder="Describe the goal objectives..."
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    ></textarea>
                                    <p
                                        v-if="errors.description"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.description }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Target Metrics Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-cyan-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-cyan-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Target Metrics
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Set measurable targets and current
                                        progress
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Target Value -->
                                <div>
                                    <label
                                        for="target_value"
                                        class="block text-sm font-medium text-gray-700"
                                        >Target Value *</label
                                    >
                                    <input
                                        type="number"
                                        id="target_value"
                                        v-model="form.target_value"
                                        required
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="errors.target_value"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.target_value }}
                                    </p>
                                </div>

                                <!-- Current Value -->
                                <div>
                                    <label
                                        for="current_value"
                                        class="block text-sm font-medium text-gray-700"
                                        >Current Value</label
                                    >
                                    <input
                                        type="number"
                                        id="current_value"
                                        v-model="form.current_value"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="errors.current_value"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.current_value }}
                                    </p>
                                </div>

                                <!-- Budget -->
                                <div>
                                    <label
                                        for="budget"
                                        class="block text-sm font-medium text-gray-700"
                                        >Budget ($)</label
                                    >
                                    <div
                                        class="mt-1 relative rounded-md shadow-sm"
                                    >
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
                                            placeholder="0.00"
                                            class="mt-1 block w-full pl-7 pr-3 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                        />
                                    </div>
                                    <p
                                        v-if="errors.budget"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.budget }}
                                    </p>
                                </div>

                                <!-- Labor Hours -->
                                <div>
                                    <label
                                        for="labor_hours"
                                        class="block text-sm font-medium text-gray-700"
                                        >Labor Hours</label
                                    >
                                    <input
                                        type="number"
                                        id="labor_hours"
                                        v-model="form.labor_hours"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.0"
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="errors.labor_hours"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.labor_hours }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-indigo-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-indigo-600"
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
                                        Set the goal period and deadline
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Quarter -->
                                <div>
                                    <label
                                        for="quarter"
                                        class="block text-sm font-medium text-gray-700"
                                        >Quarter *</label
                                    >
                                    <select
                                        id="quarter"
                                        v-model="form.quarter"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    >
                                        <option value="">Select quarter</option>
                                        <option value="Q1">Q1</option>
                                        <option value="Q2">Q2</option>
                                        <option value="Q3">Q3</option>
                                        <option value="Q4">Q4</option>
                                    </select>
                                    <p
                                        v-if="errors.quarter"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.quarter }}
                                    </p>
                                </div>

                                <!-- Year -->
                                <div>
                                    <label
                                        for="year"
                                        class="block text-sm font-medium text-gray-700"
                                        >Year *</label
                                    >
                                    <input
                                        type="number"
                                        id="year"
                                        v-model="form.year"
                                        required
                                        min="2020"
                                        max="2030"
                                        placeholder="2024"
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="errors.year"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.year }}
                                    </p>
                                </div>

                                <!-- Deadline -->
                                <div class="md:col-span-2">
                                    <label
                                        for="deadline"
                                        class="block text-sm font-medium text-gray-700"
                                        >Deadline *</label
                                    >
                                    <input
                                        type="date"
                                        id="deadline"
                                        v-model="form.deadline"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="errors.deadline"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.deadline }}
                                    </p>
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
                                class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="loading"
                                class="px-6 py-3 text-sm font-medium text-white bg-teal-600 border border-transparent rounded-lg hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:opacity-50 transition-all duration-200"
                            >
                                {{ loading ? "Creating..." : "Create Goal" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const loading = ref(false);
const errors = ref({});

const form = useForm({
    title: "",
    description: "",
    target_value: 0,
    current_value: 0,
    budget: 0,
    labor_hours: 0,
    quarter: "",
    year: new Date().getFullYear(),
    deadline: new Date().toISOString().split("T")[0],
    type: "other",
    priority: "medium",
    status: "not_started",
    progress: 0,
});

const submitForm = () => {
    loading.value = true;
    errors.value = {};

    form.post(route("goals.web.store"), {
        onSuccess: () => {
            router.visit(route("goals.web.index"));
        },
        onError: (error) => {
            errors.value = error;
        },
        onFinish: () => {
            loading.value = false;
        },
    });
};

const goBack = () => {
    router.visit(route("goals.web.index"));
};
</script>
