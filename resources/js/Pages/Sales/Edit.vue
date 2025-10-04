<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Edit Sale
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Update the sales transaction details
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
                        Update the details for this sales transaction
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
                                <div class="md:col-span-2">
                                    <label
                                        for="client_id"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Client *</label
                                    >
                                    <Autocomplete
                                        id="client_id"
                                        v-model="form.client_id"
                                        placeholder="Search for a client by name or email..."
                                        class="w-full"
                                        @select="handleClientSelect"
                                        @create="handleClientCreate"
                                    />
                                    <p
                                        v-if="form.errors.client_id"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.client_id }}
                                    </p>
                                </div>

                                <!-- Product Name -->
                                <div>
                                    <label
                                        for="product_name"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Product Name *</label
                                    >
                                    <input
                                        type="text"
                                        id="product_name"
                                        v-model="form.product_name"
                                        required
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500 transition-colors"
                                    />
                                    <p
                                        v-if="form.errors.product_name"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.product_name }}
                                    </p>
                                </div>

                                <!-- Amount -->
                                <div>
                                    <label
                                        for="amount"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Amount *</label
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
                                            id="amount"
                                            v-model="form.amount"
                                            required
                                            min="0"
                                            step="0.01"
                                            class="mt-1 block w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500 transition-colors"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.amount"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.amount }}
                                    </p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mt-6">
                                <label
                                    for="description"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Description</label
                                >
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500 px-3 py-2 transition-colors"
                                ></textarea>
                                <p
                                    v-if="form.errors.description"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Transaction Details Section -->
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
                                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Transaction Details
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Configure sale date, status, and payment
                                        method
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Sale Date -->
                                <div>
                                    <label
                                        for="sale_date"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Sale Date *</label
                                    >
                                    <input
                                        type="date"
                                        id="sale_date"
                                        v-model="form.sale_date"
                                        required
                                        class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500 px-3 py-2 transition-colors"
                                    />
                                    <p
                                        v-if="form.errors.sale_date"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.sale_date }}
                                    </p>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label
                                        for="status"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Status *</label
                                    >
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        required
                                        class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500 px-3 py-2 transition-colors"
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
                                        v-if="form.errors.status"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.status }}
                                    </p>
                                </div>

                                <!-- Payment Method -->
                                <div>
                                    <label
                                        for="payment_method"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Payment Method *</label
                                    >
                                    <select
                                        id="payment_method"
                                        v-model="form.payment_method"
                                        required
                                        class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500 px-3 py-2 transition-colors"
                                    >
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="online">Online</option>
                                    </select>
                                    <p
                                        v-if="form.errors.payment_method"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.payment_method }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information Section -->
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
                                        Add any extra notes or details about
                                        this sale
                                    </p>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label
                                    for="notes"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Notes</label
                                >
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500 px-3 py-2 transition-colors"
                                    placeholder="Add any additional notes or special instructions..."
                                ></textarea>
                                <p
                                    v-if="form.errors.notes"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ form.errors.notes }}
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
                                class="inline-flex items-center justify-center px-6 py-3 bg-green-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50"
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
                                        : "Update Sale"
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
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Autocomplete from "@/Components/UI/Autocomplete.vue";

const props = defineProps({
    sale: {
        type: Object,
        required: true,
    },
});

// Helper function to convert ISO datetime to date input format (YYYY-MM-DD)
const formatDateForInput = (isoDate) => {
    if (!isoDate) return "";
    const date = new Date(isoDate);
    return date.toISOString().split("T")[0];
};

const form = useForm({
    client_id: props.sale.client_id || "",
    product_name: props.sale.product_name || "",
    description: props.sale.description || "",
    amount: props.sale.amount || 0,
    sale_date: formatDateForInput(props.sale.sale_date),
    status: props.sale.status || "pending",
    payment_method: props.sale.payment_method || "cash",
    notes: props.sale.notes || "",
});

const submitForm = () => {
    form.put(`/sales/${props.sale.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(`/sales/${props.sale.id}`);
        },
        onError: (errors) => {
            console.error("Error updating sale:", errors);
        },
    });
};

const handleClientSelect = (client) => {
    console.log("Client selected:", client);
    // The client_id is already set by v-model
};

const handleClientCreate = (client) => {
    console.log("Client created:", client);
    // The client_id is already set by v-model
};

const goBack = () => {
    router.visit(`/sales/${props.sale.id}`);
};
</script>
