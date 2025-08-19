<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Sale Details
                </h2>
                <div class="flex space-x-2">
                    <button
                        @click="editSale"
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Edit
                    </button>
                    <button
                        @click="deleteSale"
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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Sale Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Sale Information
                                </h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Product Name
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ props.sale.product_name }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Client
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                props.sale.client?.name || "N/A"
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Amount
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            ${{
                                                formatCurrency(
                                                    props.sale.amount
                                                )
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Sale Date
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatDate(props.sale.sale_date)
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
                                                        props.sale.status
                                                    )
                                                "
                                                class="px-2 py-1 text-xs font-medium rounded-full"
                                            >
                                                {{
                                                    formatStatus(
                                                        props.sale.status
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
                                                    props.sale.payment_method
                                                )
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
                                            Description
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                props.sale.description ||
                                                "No description provided"
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
                                                props.sale.notes ||
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
                                                props.sale.user?.name ||
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
                                                    props.sale.created_at
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
                                                    props.sale.updated_at
                                                )
                                            }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Statistics Card -->
                        <div class="mt-8 bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Sale Statistics
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Sale Value
                                    </dt>
                                    <dd
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        ${{ formatCurrency(props.sale.amount) }}
                                    </dd>
                                </div>

                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Days Since Sale
                                    </dt>
                                    <dd
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        {{ daysSinceSale }}
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
                                                    props.sale.status
                                                )
                                            "
                                            class="px-2 py-1 text-xs font-medium rounded-full"
                                        >
                                            {{
                                                formatStatus(props.sale.status)
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
