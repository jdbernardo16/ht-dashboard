<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Goal
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submitForm" class="space-y-6">
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <p
                                    v-if="errors.description"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.description }}
                                </p>
                            </div>

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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="errors.current_value"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.current_value }}
                                </p>
                            </div>

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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="errors.year"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.year }}
                                </p>
                            </div>

                            <!-- Deadline -->
                            <div>
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="errors.deadline"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.deadline }}
                                </p>
                            </div>

                            <!-- Submit Buttons -->
                            <div
                                class="flex items-center justify-end space-x-3"
                            >
                                <button
                                    type="button"
                                    @click="goBack"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="loading"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    {{
                                        loading ? "Creating..." : "Create Goal"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
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

    form.post(route("goals.store"), {
        onSuccess: () => {
            router.visit(route("goals.index"));
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
    router.visit(route("goals.index"));
};
</script>
