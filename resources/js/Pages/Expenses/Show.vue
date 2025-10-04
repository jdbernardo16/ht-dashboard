<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ props.expense.description }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Expense ID: #{{ props.expense.id }} •
                        {{ formatDate(props.expense.expense_date) }}
                    </p>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
                    <button
                        @click="editExpense"
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
                        Edit Expense
                    </button>
                    <button
                        @click="deleteExpense"
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
            <!-- Expense Value and Status Card -->
            <div
                class="bg-gradient-to-r from-red-50 to-orange-50 rounded-xl p-8 mb-8 border border-red-100"
            >
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <div>
                        <p class="text-sm font-medium text-red-600 mb-1">
                            Expense Amount
                        </p>
                        <p class="text-4xl font-bold text-red-900">
                            ${{ formatCurrency(props.expense.amount) }}
                        </p>
                        <p class="text-sm text-red-600 mt-2">
                            {{ daysSinceExpense }}
                            {{ daysSinceExpense === 1 ? "day" : "days" }} ago
                        </p>
                    </div>
                    <div class="flex flex-col items-center space-y-4">
                        <span
                            :class="getStatusClass(props.expense.status)"
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
                            {{ formatStatus(props.expense.status) }}
                        </span>
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Payment Method</p>
                            <p class="font-medium text-gray-900">
                                {{
                                    formatPaymentMethod(
                                        props.expense.payment_method
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
                    <!-- Expense Information Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-red-500 to-orange-600 px-6 py-4"
                        >
                            <h2 class="text-xl font-semibold text-white">
                                Expense Information
                            </h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-6">
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Description
                                    </dt>
                                    <dd
                                        class="text-xl font-semibold text-gray-900"
                                    >
                                        {{ props.expense.description }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Category
                                    </dt>
                                    <dd class="text-lg text-gray-900">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800"
                                        >
                                            {{ props.expense.category }}
                                        </span>
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Amount
                                    </dt>
                                    <dd class="text-3xl font-bold text-red-600">
                                        ${{
                                            formatCurrency(props.expense.amount)
                                        }}
                                    </dd>
                                </div>

                                <div v-if="props.expense.notes">
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Notes
                                    </dt>
                                    <dd
                                        class="text-gray-900 leading-relaxed bg-gray-50 p-4 rounded-lg"
                                    >
                                        {{ props.expense.notes }}
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
                                        Tax Amount
                                    </dt>
                                    <dd
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        ${{
                                            formatCurrency(
                                                props.expense.tax_amount || 0
                                            )
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Merchant
                                    </dt>
                                    <dd class="text-lg text-gray-900">
                                        {{ props.expense.merchant || "N/A" }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Receipt Number
                                    </dt>
                                    <dd class="text-lg text-gray-900">
                                        {{
                                            props.expense.receipt_number ||
                                            "N/A"
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Expense Date
                                    </dt>
                                    <dd class="text-lg text-gray-900">
                                        {{
                                            formatDate(
                                                props.expense.expense_date
                                            )
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Created By
                                    </dt>
                                    <dd class="text-lg text-gray-900">
                                        {{
                                            props.expense.user?.name || "System"
                                        }}
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
                                                props.expense.created_at
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
                                    <dd class="text-lg text-gray-900">
                                        {{
                                            formatDateTime(
                                                props.expense.updated_at
                                            )
                                        }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Receipts Section -->
                    <div
                        v-if="
                            props.expense.media &&
                            props.expense.media.length > 0
                        "
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-yellow-500 to-orange-600 px-6 py-4"
                        >
                            <h2 class="text-xl font-semibold text-white">
                                Receipts
                            </h2>
                        </div>
                        <div class="p-6">
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                            >
                                <div
                                    v-for="media in props.expense.media"
                                    :key="media.id"
                                    class="bg-gray-50 rounded-xl overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-200"
                                >
                                    <div class="p-4">
                                        <div
                                            class="flex items-center space-x-3 mb-4"
                                        >
                                            <div class="flex-shrink-0">
                                                <img
                                                    v-if="
                                                        media.mime_type.startsWith(
                                                            'image/'
                                                        )
                                                    "
                                                    :src="media.url"
                                                    :alt="media.original_name"
                                                    class="h-16 w-16 object-cover rounded-lg"
                                                />
                                                <div
                                                    v-else
                                                    class="h-16 w-16 bg-blue-100 rounded-lg flex items-center justify-center"
                                                >
                                                    <svg
                                                        class="w-8 h-8 text-blue-600"
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
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p
                                                    class="text-sm font-medium text-gray-900 truncate"
                                                >
                                                    {{ media.original_name }}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-500"
                                                >
                                                    {{ media.formatted_size }}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-400"
                                                >
                                                    {{ media.mime_type }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a
                                                :href="media.url"
                                                target="_blank"
                                                class="flex-1 text-center px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
                                            >
                                                <svg
                                                    class="w-4 h-4 inline mr-1"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                                    />
                                                </svg>
                                                View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Expense Statistics Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-green-500 to-teal-600 px-6 py-4"
                        >
                            <h3 class="text-lg font-semibold text-white">
                                Expense Statistics
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-6">
                                <div
                                    class="text-center p-4 bg-red-50 rounded-lg border border-red-200"
                                >
                                    <p
                                        class="text-sm font-medium text-red-600 mb-1"
                                    >
                                        Total Expense
                                    </p>
                                    <p class="text-3xl font-bold text-red-900">
                                        ${{
                                            formatCurrency(props.expense.amount)
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200"
                                >
                                    <p
                                        class="text-sm font-medium text-blue-600 mb-1"
                                    >
                                        Days Since Expense
                                    </p>
                                    <p class="text-2xl font-bold text-blue-900">
                                        {{ daysSinceExpense }}
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
                                            getStatusClass(props.expense.status)
                                        "
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                    >
                                        {{ formatStatus(props.expense.status) }}
                                    </span>
                                </div>

                                <div
                                    v-if="props.expense.tax_amount"
                                    class="text-center p-4 bg-green-50 rounded-lg border border-green-200"
                                >
                                    <p
                                        class="text-sm font-medium text-green-600 mb-1"
                                    >
                                        Tax Amount
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-green-900"
                                    >
                                        ${{
                                            formatCurrency(
                                                props.expense.tax_amount
                                            )
                                        }}
                                    </p>
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
                                Generate Report
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
                                View Expense History
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
    expense: {
        type: Object,
        required: true,
    },
});

const editExpense = () => {
    router.visit(route("expenses.web.edit", props.expense.id));
};

const deleteExpense = () => {
    if (confirm("Are you sure you want to delete this expense?")) {
        router.delete(route("expenses.web.destroy", props.expense.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route("expenses.web.index"));
            },
        });
    }
};

const goBack = () => {
    window.history.back();
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
        bank_transfer: "Bank Transfer",
    };
    return methods[method] || method;
};

const getStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        paid: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const daysSinceExpense = computed(() => {
    const expenseDate = new Date(props.expense.expense_date);
    const today = new Date();
    const diffTime = Math.abs(today - expenseDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
});
</script>
