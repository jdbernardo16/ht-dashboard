<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Expense Details
                </h2>
                <div class="flex space-x-2">
                    <button
                        @click="editExpense"
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Edit
                    </button>
                    <button
                        @click="deleteExpense"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Delete
                    </button>
                    <button
                        @click="goBack"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Back
                    </button>
                </div>
            </div>
        </template>

        <div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Expense Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Expense Information
                                </h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Category
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ props.expense.category }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Description
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ props.expense.description }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Amount
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <span
                                                class="font-medium text-red-600"
                                            >
                                                ${{
                                                    formatCurrency(
                                                        props.expense.amount
                                                    )
                                                }}
                                            </span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Expense Date
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatDate(
                                                    props.expense.expense_date
                                                )
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Status
                                        </dt>
                                        <dd class="mt-1">
                                            <span
                                                :class="
                                                    getStatusClass(
                                                        props.expense.status
                                                    )
                                                "
                                                class="px-2 py-1 text-xs font-medium rounded-full"
                                            >
                                                {{
                                                    formatStatus(
                                                        props.expense.status
                                                    )
                                                }}
                                            </span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Payment Method
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatPaymentMethod(
                                                    props.expense.payment_method
                                                )
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Merchant
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                props.expense.merchant || "N/A"
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Receipt Number
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                props.expense.receipt_number ||
                                                "N/A"
                                            }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Additional Details
                                </h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Tax Amount
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            ${{
                                                formatCurrency(
                                                    props.expense.tax_amount ||
                                                        0
                                                )
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Notes
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                props.expense.notes ||
                                                "No notes provided"
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Created By
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                props.expense.user?.name ||
                                                "System"
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Created At
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatDateTime(
                                                    props.expense.created_at
                                                )
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Last Updated
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
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
                            class="mt-8"
                        >
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Receipts
                            </h3>
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                            >
                                <div
                                    v-for="media in props.expense.media"
                                    :key="media.id"
                                    class="border rounded-lg overflow-hidden bg-white shadow-sm"
                                >
                                    <div class="p-4">
                                        <div
                                            class="flex items-center space-x-3 mb-3"
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
                                                    class="h-16 w-16 object-cover rounded"
                                                />
                                                <div
                                                    v-else
                                                    class="h-16 w-16 bg-blue-100 rounded flex items-center justify-center"
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
                                                class="flex-1 text-center px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >
                                                View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Card -->
                        <div class="mt-8 bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Expense Statistics
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Expense Value
                                    </dt>
                                    <dd
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        ${{
                                            formatCurrency(props.expense.amount)
                                        }}
                                    </dd>
                                </div>

                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Days Since Expense
                                    </dt>
                                    <dd
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        {{ daysSinceExpense }}
                                    </dd>
                                </div>

                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Payment Status
                                    </dt>
                                    <dd class="mt-1">
                                        <span
                                            :class="
                                                getStatusClass(
                                                    props.expense.status
                                                )
                                            "
                                            class="px-2 py-1 text-xs font-medium rounded-full"
                                        >
                                            {{
                                                formatStatus(
                                                    props.expense.status
                                                )
                                            }}
                                        </span>
                                    </dd>
                                </div>
                            </div>
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
