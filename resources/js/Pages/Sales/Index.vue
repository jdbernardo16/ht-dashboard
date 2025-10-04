<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Sales Management
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Track and manage all your sales transactions
                    </p>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
                    <button
                        @click="createSale"
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
                                d="M12 4v16m8-8H4"
                            ></path>
                        </svg>
                        New Sale
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Sales Statistics Cards -->
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
            >
                <div
                    class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-3 border border-green-100"
                >
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full"
                            >
                                <svg
                                    class="w-6 h-6 text-green-600"
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
                            <p class="text-sm font-medium text-green-600">
                                Total Revenue
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                ${{ getTotalRevenue() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-3 border border-blue-100"
                >
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full"
                            >
                                <svg
                                    class="w-6 h-6 text-blue-600"
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
                            <p class="text-sm font-medium text-blue-600">
                                Total Sales
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ sales.length }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-3 border border-purple-100"
                >
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 bg-purple-100 rounded-full"
                            >
                                <svg
                                    class="w-6 h-6 text-purple-600"
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
                            <p class="text-sm font-medium text-purple-600">
                                Average Sale
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                ${{ getAverageSale() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-3 border border-yellow-100"
                >
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-full"
                            >
                                <svg
                                    class="w-6 h-6 text-yellow-600"
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
                        <div class="ml-4">
                            <p class="text-sm font-medium text-yellow-600">
                                Pending
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ getPendingSalesCount() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Card -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8"
            >
                <div
                    class="bg-gradient-to-r from-green-500 to-teal-600 px-6 py-4"
                >
                    <h2 class="text-xl font-semibold text-white">
                        Search & Filter
                    </h2>
                </div>
                <div class="p-6">
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                    >
                        <!-- Search Input -->
                        <div class="lg:col-span-2">
                            <label
                                for="search"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Search sales
                            </label>
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
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                        ></path>
                                    </svg>
                                </div>
                                <input
                                    id="search"
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Search by product name or client..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-colors duration-200"
                                    @input="handleSearchInput"
                                    aria-describedby="search-description"
                                />
                            </div>
                            <p
                                id="search-description"
                                class="mt-1 text-xs text-gray-500"
                            >
                                Search by product name, client, or description
                            </p>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label
                                for="status"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Status
                            </label>
                            <select
                                id="status"
                                v-model="filters.status"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                                @change="handleFilterChange"
                            >
                                <option value="">All Status</option>
                                <option
                                    v-for="status in statusOptions"
                                    :key="status.value"
                                    :value="status.value"
                                >
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Payment Method Filter -->
                        <div>
                            <label
                                for="payment_method"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Payment Method
                            </label>
                            <select
                                id="payment_method"
                                v-model="filters.payment_method"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                                @change="handleFilterChange"
                            >
                                <option value="">All Methods</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                    </div>

                    <!-- Advanced Filters Toggle -->
                    <div class="mt-4">
                        <button
                            @click="showAdvancedFilters = !showAdvancedFilters"
                            class="text-sm text-green-600 hover:text-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors duration-200"
                            :aria-expanded="showAdvancedFilters"
                        >
                            {{ showAdvancedFilters ? "Hide" : "Show" }} advanced
                            filters
                        </button>
                    </div>

                    <!-- Advanced Filters -->
                    <div
                        v-if="showAdvancedFilters"
                        class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                    >
                        <!-- Date Range -->
                        <div>
                            <label
                                for="date_from"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Date From
                            </label>
                            <input
                                id="date_from"
                                v-model="filters.date_from"
                                type="date"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                                @change="handleFilterChange"
                            />
                        </div>

                        <div>
                            <label
                                for="date_to"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Date To
                            </label>
                            <input
                                id="date_to"
                                v-model="filters.date_to"
                                type="date"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                                @change="handleFilterChange"
                            />
                        </div>

                        <!-- Amount Range -->
                        <div>
                            <label
                                for="min_amount"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Min Amount
                            </label>
                            <input
                                id="min_amount"
                                v-model="filters.min_amount"
                                type="number"
                                placeholder="0.00"
                                step="0.01"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                                @change="handleFilterChange"
                            />
                        </div>

                        <div>
                            <label
                                for="max_amount"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Max Amount
                            </label>
                            <input
                                id="max_amount"
                                v-model="filters.max_amount"
                                type="number"
                                placeholder="0.00"
                                step="0.01"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                                @change="handleFilterChange"
                            />
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div
                        class="mt-6 flex flex-col sm:flex-row gap-3 justify-end"
                    >
                        <button
                            @click="clearFilters"
                            :disabled="!hasActiveFilters"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                            aria-label="Clear all filters"
                        >
                            Clear Filters
                        </button>
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm"
                            aria-label="Apply filters"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div
                v-if="loading && sales.length === 0"
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
            >
                <div class="animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                    <div class="space-y-3">
                        <div
                            v-for="i in 5"
                            :key="i"
                            class="h-12 bg-gray-200 rounded"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else-if="sales.length === 0 && !hasActiveFilters"
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center"
            >
                <svg
                    class="mx-auto h-16 w-16 text-gray-400 mb-4"
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
                <h3 class="mt-2 text-lg font-medium text-gray-900">
                    No sales yet
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Get started by creating your first sale.
                </p>
                <div class="mt-6">
                    <button
                        @click="createSale"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200"
                        aria-label="Create new sale"
                    >
                        <svg
                            class="-ml-1 mr-2 h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            ></path>
                        </svg>
                        New Sale
                    </button>
                </div>
            </div>

            <!-- No Results State -->
            <div
                v-else-if="sales.length === 0 && hasActiveFilters"
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center"
            >
                <svg
                    class="mx-auto h-16 w-16 text-gray-400 mb-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    ></path>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">
                    No sales found
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Try adjusting your search criteria or clear filters.
                </p>
                <div class="mt-6">
                    <button
                        @click="clearFilters"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200"
                        aria-label="Clear filters"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Data Table Card -->
            <div
                v-else
                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
            >
                <div
                    class="bg-gradient-to-r from-gray-700 to-gray-900 px-6 py-4"
                >
                    <h2 class="text-xl font-semibold text-white">Sales List</h2>
                </div>
                <div class="relative">
                    <!-- Loading Overlay -->
                    <div
                        v-if="loading"
                        class="absolute inset-0 bg-white bg-opacity-50 z-10 flex items-center justify-center"
                    >
                        <div
                            class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"
                        ></div>
                    </div>

                    <DataTable
                        :data="sales"
                        :columns="columns"
                        :filters="tableFilters"
                        :loading="loading"
                        @create="createSale"
                        @view="viewSale"
                        @edit="editSale"
                        @delete="deleteSale"
                    >
                        <template #amount="{ item }">
                            <span class="font-semibold text-green-600 text-lg">
                                ${{ formatCurrency(item.amount) }}
                            </span>
                        </template>

                        <template #status="{ item }">
                            <span
                                :class="getStatusClass(item.status)"
                                class="px-3 py-1 text-xs font-medium rounded-full"
                            >
                                {{ formatStatus(item.status) }}
                            </span>
                        </template>

                        <template #sale_date="{ item }">
                            <div class="text-sm text-gray-900">
                                {{ formatDate(item.sale_date) }}
                            </div>
                        </template>

                        <template #client="{ item }">
                            <div class="font-medium text-gray-900">
                                {{ item.client?.name || "N/A" }}
                            </div>
                        </template>

                        <template #payment_method="{ item }">
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                            >
                                {{ formatPaymentMethod(item.payment_method) }}
                            </span>
                        </template>
                    </DataTable>
                </div>

                <!-- Pagination -->
                <div class="border-t border-gray-200">
                    <Pagination
                        :links="props.sales.links"
                        :from="props.sales.from"
                        :to="props.sales.to"
                        :total="props.sales.total"
                        @navigate="handlePageChange"
                        class="bg-gray-50 px-6 py-3"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import SearchFilter from "@/Components/SearchFilter.vue";
import Pagination from "@/Components/Pagination.vue";

// Props from server
const props = defineProps({
    sales: {
        type: Object,
        required: true,
    },
    clients: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Reactive data
const loading = ref(false);
const showAdvancedFilters = ref(false);
const filters = ref({
    search: props.filters.search || "",
    status: props.filters.status || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
    min_amount: props.filters.min_amount || "",
    max_amount: props.filters.max_amount || "",
    payment_method: props.filters.payment_method || "",
});

// Computed properties
const sales = computed(() => props.sales.data || []);
const hasActiveFilters = computed(() => {
    return Object.values(filters.value).some((value) => value !== "");
});

// Table columns
const columns = [
    { key: "id", label: "ID", sortable: true },
    { key: "product_name", label: "Product", sortable: true },
    { key: "client", label: "Client", sortable: true },
    { key: "amount", label: "Amount", type: "currency", sortable: true },
    { key: "sale_date", label: "Date", type: "date", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "payment_method", label: "Payment", sortable: true },
];

// Status options
const statusOptions = [
    { value: "pending", label: "Pending" },
    { value: "completed", label: "Completed" },
    { value: "cancelled", label: "Cancelled" },
];

// Table filters
const tableFilters = [
    { value: "pending", label: "Pending" },
    { value: "completed", label: "Completed" },
    { value: "cancelled", label: "Cancelled" },
];

// Methods
const createSale = () => {
    router.visit("/sales/create");
};

const editSale = (sale) => {
    router.visit(`/sales/${sale.id}/edit`);
};

const viewSale = (sale) => {
    router.visit(`/sales/${sale.id}`);
};

const deleteSale = async (sale) => {
    if (confirm("Are you sure you want to delete this sale?")) {
        try {
            loading.value = true;
            await router.delete(`/sales/${sale.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    // The page will automatically refresh with new data
                },
                onError: (errors) => {
                    console.error("Error deleting sale:", errors);
                    if (errors.message?.includes("403")) {
                        alert("You don't have permission to delete this sale.");
                    }
                },
                onFinish: () => {
                    loading.value = false;
                },
            });
        } catch (error) {
            loading.value = false;
            console.error("Error deleting sale:", error);
        }
    }
};

const handleSearchInput = () => {
    clearTimeout(window.searchTimeout);
    window.searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const handleFilterChange = () => {
    applyFilters();
};

const applyFilters = () => {
    loading.value = true;
    const params = {};
    Object.keys(filters.value).forEach((key) => {
        if (filters.value[key]) params[key] = filters.value[key];
    });

    router.get("/sales", params, {
        preserveState: true,
        preserveScroll: true,
        only: ["sales", "filters"],
        onError: (errors) => {
            console.error("Error applying filters:", errors);
            if (errors.message?.includes("403")) {
                alert("You don't have permission to view sales.");
            }
        },
        onFinish: () => {
            loading.value = false;
        },
    });
};

const clearFilters = () => {
    filters.value = {
        search: "",
        status: "",
        date_from: "",
        date_to: "",
        min_amount: "",
        max_amount: "",
        payment_method: "",
    };
    applyFilters();
};

const handlePageChange = (url) => {
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        only: ["sales", "clients", "filters"],
    });
};

// Statistics methods
const getTotalRevenue = () => {
    return sales.value
        .reduce((total, sale) => total + parseFloat(sale.amount || 0), 0)
        .toFixed(2);
};

const getAverageSale = () => {
    if (sales.value.length === 0) return "0.00";
    const total = sales.value.reduce(
        (total, sale) => total + parseFloat(sale.amount || 0),
        0
    );
    return (total / sales.value.length).toFixed(2);
};

const getPendingSalesCount = () => {
    return sales.value.filter((sale) => sale.status === "pending").length;
};

// Utility functions
const formatCurrency = (value) => {
    return parseFloat(value).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString();
};

const formatStatus = (status) => {
    if (!status) return "";
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

// Watch for filter changes
watch(
    filters,
    () => {
        clearTimeout(window.filterTimeout);
        window.filterTimeout = setTimeout(() => {
            applyFilters();
        }, 300);
    },
    { deep: true }
);

// Lifecycle
onMounted(() => {
    // Initialize any required data
});
</script>
