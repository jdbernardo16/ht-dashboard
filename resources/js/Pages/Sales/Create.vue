<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Create New Sale
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Record a new sales transaction
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
                        Back to Sales
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
                    class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4"
                >
                    <h2 class="text-xl font-semibold text-white">
                        Sale Information
                    </h2>
                    <p class="text-green-100 text-sm mt-1">
                        Fill in the details for this sales transaction
                    </p>
                </div>

                <div class="p-6 text-gray-900">
                    <form @submit.prevent="submitForm" class="space-y-8">
                        <!-- Basic Information Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-green-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-green-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
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
                                        Enter client and product details
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Client -->
                                <div>
                                    <label
                                        for="client_id"
                                        class="block text-sm font-medium text-gray-700"
                                        >Client *</label
                                    >
                                    <select
                                        id="client_id"
                                        v-model="form.client_id"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    >
                                        <option value="">
                                            Select a client...
                                        </option>
                                        <option
                                            v-for="client in clients"
                                            :key="client.id"
                                            :value="client.id"
                                        >
                                            {{ client.name }} ({{
                                                client.email
                                            }})
                                        </option>
                                    </select>
                                    <p
                                        v-if="errors.client_id"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.client_id }}
                                    </p>
                                </div>

                                <!-- Product Name -->
                                <div>
                                    <label
                                        for="product_name"
                                        class="block text-sm font-medium text-gray-700"
                                        >Product Name *</label
                                    >
                                    <input
                                        type="text"
                                        id="product_name"
                                        v-model="form.product_name"
                                        required
                                        placeholder="Enter product name..."
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="errors.product_name"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.product_name }}
                                    </p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mt-6">
                                <label
                                    for="description"
                                    class="block text-sm font-medium text-gray-700"
                                    >Description</label
                                >
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    placeholder="Describe the product or service..."
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

                        <!-- Financial Details Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-emerald-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-emerald-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Financial Details
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Set amount and payment information
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Amount -->
                                <div>
                                    <label
                                        for="amount"
                                        class="block text-sm font-medium text-gray-700"
                                        >Amount *</label
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
                                            id="amount"
                                            v-model="form.amount"
                                            required
                                            min="0"
                                            step="0.01"
                                            placeholder="0.00"
                                            class="mt-1 block w-full pl-7 pr-3 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                        />
                                    </div>
                                    <p
                                        v-if="errors.amount"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.amount }}
                                    </p>
                                </div>

                                <!-- Sale Date -->
                                <div>
                                    <label
                                        for="sale_date"
                                        class="block text-sm font-medium text-gray-700"
                                        >Sale Date *</label
                                    >
                                    <input
                                        type="date"
                                        id="sale_date"
                                        v-model="form.sale_date"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="errors.sale_date"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.sale_date }}
                                    </p>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label
                                        for="status"
                                        class="block text-sm font-medium text-gray-700"
                                        >Status *</label
                                    >
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    >
                                        <option value="pending">Pending</option>
                                        <option value="completed">
                                            Completed
                                        </option>
                                        <option value="cancelled">
                                            Cancelled
                                        </option>
                                    </select>
                                    <p
                                        v-if="errors.status"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.status }}
                                    </p>
                                </div>

                                <!-- Payment Method -->
                                <div>
                                    <label
                                        for="payment_method"
                                        class="block text-sm font-medium text-gray-700"
                                        >Payment Method *</label
                                    >
                                    <select
                                        id="payment_method"
                                        v-model="form.payment_method"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    >
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="online">Online</option>
                                    </select>
                                    <p
                                        v-if="errors.payment_method"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.payment_method }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-blue-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Additional Information
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Add any notes or comments
                                    </p>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label
                                    for="notes"
                                    class="block text-sm font-medium text-gray-700"
                                    >Notes</label
                                >
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="4"
                                    placeholder="Add any additional notes about this sale..."
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                ></textarea>
                                <p
                                    v-if="errors.notes"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.notes }}
                                </p>
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
                                class="px-6 py-3 text-sm font-medium text-white bg-green-600 border border-transparent rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 transition-all duration-200"
                            >
                                {{ loading ? "Creating..." : "Create Sale" }}
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

const props = defineProps({
    clients: {
        type: Array,
        default: () => [],
    },
});

const loading = ref(false);
const errors = ref({});

const form = useForm({
    client_id: "",
    product_name: "",
    description: "",
    amount: 0,
    sale_date: new Date().toISOString().split("T")[0],
    status: "pending",
    payment_method: "cash",
    notes: "",
});

const submitForm = () => {
    loading.value = true;
    errors.value = {};

    form.post(route("sales.web.store"), {
        onSuccess: () => {
            router.visit(route("sales.web.index"));
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
    router.visit(route("sales.web.index"));
};
</script>
