<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ props.sale.product_name }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Sale ID: #{{ props.sale.id }} •
                        {{ formatDate(props.sale.sale_date) }}
                    </p>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
                    <button
                        @click="editSale"
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
                        Edit Sale
                    </button>
                    <button
                        @click="deleteSale"
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
            <!-- Sale Value and Status Card -->
            <div
                class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-8 mb-8 border border-green-100"
            >
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <div>
                        <p class="text-sm font-medium text-green-600 mb-1">
                            Sale Value
                        </p>
                        <p class="text-4xl font-bold text-green-900">
                            ${{ formatCurrency(props.sale.amount) }}
                        </p>
                        <p class="text-sm text-green-600 mt-2">
                            {{ daysSinceSale }}
                            {{ daysSinceSale === 1 ? "day" : "days" }} ago
                        </p>
                    </div>
                    <div class="flex flex-col items-center space-y-4">
                        <span
                            :class="getStatusClass(props.sale.status)"
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
                            {{ formatStatus(props.sale.status) }}
                        </span>
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Payment Method</p>
                            <p class="font-medium text-gray-900">
                                {{
                                    formatPaymentMethod(
                                        props.sale.payment_method
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content Area -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Sale Information Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4"
                        >
                            <h2 class="text-xl font-semibold text-white">
                                Sale Information
                            </h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-6">
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Product Name
                                    </dt>
                                    <dd
                                        class="text-xl font-semibold text-gray-900"
                                    >
                                        {{ props.sale.product_name }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Client
                                    </dt>
                                    <dd class="text-lg text-gray-900">
                                        {{ props.sale.client?.name || "N/A" }}
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
                                            props.sale.description ||
                                            "No description provided"
                                        }}
                                    </dd>
                                </div>

                                <div v-if="props.sale.notes">
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Notes
                                    </dt>
                                    <dd
                                        class="text-gray-900 leading-relaxed bg-blue-50 p-4 rounded-lg border border-blue-200"
                                    >
                                        {{ props.sale.notes }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Additional Details Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-gray-700 to-gray-900 px-6 py-4"
                        >
                            <h2 class="text-xl font-semibold text-white">
                                Additional Details
                            </h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Created By
                                    </dt>
                                    <dd class="text-gray-900">
                                        {{ props.sale.user?.name || "System" }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Created At
                                    </dt>
                                    <dd class="text-gray-900">
                                        {{
                                            formatDateTime(
                                                props.sale.created_at
                                            )
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Last Updated
                                    </dt>
                                    <dd class="text-gray-900">
                                        {{
                                            formatDateTime(
                                                props.sale.updated_at
                                            )
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Sale Date
                                    </dt>
                                    <dd class="text-gray-900">
                                        {{ formatDate(props.sale.sale_date) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Sale Statistics Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-green-500 to-teal-600 px-6 py-4"
                        >
                            <h3 class="text-lg font-semibold text-white">
                                Sale Statistics
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-6">
                                <div
                                    class="text-center p-4 bg-green-50 rounded-lg border border-green-200"
                                >
                                    <p
                                        class="text-sm font-medium text-green-600 mb-1"
                                    >
                                        Total Sale Value
                                    </p>
                                    <p
                                        class="text-3xl font-bold text-green-900"
                                    >
                                        ${{ formatCurrency(props.sale.amount) }}
                                    </p>
                                </div>

                                <div
                                    class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200"
                                >
                                    <p
                                        class="text-sm font-medium text-blue-600 mb-1"
                                    >
                                        Days Since Sale
                                    </p>
                                    <p class="text-2xl font-bold text-blue-900">
                                        {{ daysSinceSale }}
                                    </p>
                                </div>

                                <div
                                    class="text-center p-4 bg-purple-50 rounded-lg border border-purple-200"
                                >
                                    <p
                                        class="text-sm font-medium text-purple-600 mb-1"
                                    >
                                        Payment Status
                                    </p>
                                    <span
                                        :class="
                                            getStatusClass(props.sale.status)
                                        "
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                    >
                                        {{ formatStatus(props.sale.status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-yellow-500 to-orange-600 px-6 py-4"
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
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                                Generate Invoice
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
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                                Download Receipt
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
                                View Client Profile
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
    sale: {
        type: Object,
        required: true,
    },
});

const editSale = () => {
    router.visit(`/sales/${props.sale.id}/edit`);
};

const deleteSale = () => {
    if (confirm("Are you sure you want to delete this sale?")) {
        router.delete(`/sales/${props.sale.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                router.visit("/sales");
            },
            onError: (errors) => {
                console.error("Error deleting sale:", errors);
                if (errors.message?.includes("403")) {
                    alert("You don't have permission to delete this sale.");
                }
            },
        });
    }
};

const goBack = () => {
    router.visit("/sales");
};

// Utility functions
const formatCurrency = (value) => {
    return parseFloat(value).toFixed(2);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleString();
};

const formatStatus = (status) => {
    if (!status) return "Unknown";
    return status.charAt(0).toUpperCase() + status.slice(1).replace("_", " ");
};

const formatPaymentMethod = (method) => {
    const methods = {
        cash: "Cash",
        card: "Card",
        online: "Online",
    };
    return methods[method] || method;
};

const getStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const daysSinceSale = computed(() => {
    const saleDate = new Date(props.sale.sale_date);
    const today = new Date();
    const diffTime = Math.abs(today - saleDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
});
</script>
