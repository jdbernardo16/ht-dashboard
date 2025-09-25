<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Goal
            </h2>
        </template>

        <div>
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
                                    v-if="form.errors.title"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label
                                    for="description"
                                    class="block text-sm font-medium text-gray-700"
                                    >Description *</label
                                >
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <p
                                    v-if="form.errors.description"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.description }}
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
                                    v-if="form.errors.target_value"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.target_value }}
                                </p>
                            </div>

                            <!-- Current Value -->
                            <div>
                                <label
                                    for="current_value"
                                    class="block text-sm font-medium text-gray-700"
                                    >Current Value *</label
                                >
                                <input
                                    type="number"
                                    id="current_value"
                                    v-model="form.current_value"
                                    required
                                    min="0"
                                    step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.current_value"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.current_value }}
                                </p>
                            </div>

                            <!-- Budget -->
                            <div>
                                <label
                                    for="budget"
                                    class="block text-sm font-medium text-gray-700"
                                    >Budget ($)</label
                                >
                                <input
                                    type="number"
                                    id="budget"
                                    v-model="form.budget"
                                    min="0"
                                    step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.budget"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.budget }}
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.labor_hours"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.labor_hours }}
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
                                    <option value="Q1">Q1</option>
                                    <option value="Q2">Q2</option>
                                    <option value="Q3">Q3</option>
                                    <option value="Q4">Q4</option>
                                </select>
                                <p
                                    v-if="form.errors.quarter"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.quarter }}
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
                                    v-if="form.errors.year"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.year }}
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
                                    v-if="form.errors.deadline"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.deadline }}
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
                                    :disabled="form.processing"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
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
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
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
