<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Expense Management
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Track and manage all your business expenses
                    </p>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
                    <button
                        @click="createExpense"
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
                        New Expense
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Expense Statistics Cards -->
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
            >
                <div
                    class="bg-gradient-to-r from-red-50 to-orange-50 rounded-xl p-3 border border-red-100"
                >
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 bg-red-100 rounded-full"
                            >
                                <svg
                                    class="w-6 h-6 text-red-600"
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
                            <p class="text-sm font-medium text-red-600">
                                Total Expenses
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                ${{ getTotalExpenses() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl p-3 border border-yellow-100"
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
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-yellow-600">
                                Average Expense
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                ${{ getAverageExpense() }}
                            </p>
                        </div>
                    </div>
                </div>

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
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-green-600">
                                Paid
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ getPaidExpensesCount() }}
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
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-blue-600">
                                Pending
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ getPendingExpensesCount() }}
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
                    class="bg-gradient-to-r from-red-500 to-orange-600 px-6 py-4"
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
                                Search expenses
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
                                    placeholder="Search by description, merchant, or category..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm transition-colors duration-200"
                                    @input="handleSearchInput"
                                    aria-describedby="search-description"
                                />
                            </div>
                            <p
                                id="search-description"
                                class="mt-1 text-xs text-gray-500"
                            >
                                Search by description, merchant, category, or
                                receipt number
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
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

                        <!-- Category Filter -->
                        <div>
                            <label
                                for="category"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Category
                            </label>
                            <select
                                id="category"
                                v-model="filters.category"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
                                @change="handleFilterChange"
                            >
                                <option value="">All Categories</option>
                                <option
                                    v-for="category in categoryOptions"
                                    :key="category.value"
                                    :value="category.value"
                                >
                                    {{ category.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Advanced Filters Toggle -->
                    <div class="mt-4">
                        <button
                            @click="showAdvancedFilters = !showAdvancedFilters"
                            class="text-sm text-red-600 hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200"
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
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
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                            aria-label="Clear all filters"
                        >
                            Clear Filters
                        </button>
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 shadow-sm"
                            aria-label="Apply filters"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div
                v-if="loading && expenses.data.length === 0"
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
                v-else-if="expenses.data.length === 0 && !hasActiveFilters"
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
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    ></path>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">
                    No expenses yet
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Get started by adding your first expense.
                </p>
                <div class="mt-6">
                    <button
                        @click="createExpense"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200"
                        aria-label="Create new expense"
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
                        New Expense
                    </button>
                </div>
            </div>

            <!-- No Results State -->
            <div
                v-else-if="expenses.data.length === 0 && hasActiveFilters"
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
                    No expenses found
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Try adjusting your search criteria or clear filters.
                </p>
                <div class="mt-6">
                    <button
                        @click="clearFilters"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200"
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
                    <h2 class="text-xl font-semibold text-white">
                        Expenses List
                    </h2>
                </div>
                <div class="relative">
                    <!-- Loading Overlay -->
                    <div
                        v-if="loading"
                        class="absolute inset-0 bg-white bg-opacity-50 z-10 flex items-center justify-center"
                    >
                        <div
                            class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600"
                        ></div>
                    </div>

                    <DataTable
                        :data="expenses.data"
                        :columns="columns"
                        :filters="tableFilters"
                        :pagination="expenses"
                        :loading="loading"
                        @create="createExpense"
                        @view="viewExpense"
                        @edit="editExpense"
                        @delete="deleteExpense"
                    >
                        <template #amount="{ item }">
                            <span class="font-bold text-red-600 text-lg">
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

                        <template #expense_date="{ item }">
                            <div class="text-sm text-gray-900">
                                {{ formatDate(item.expense_date) }}
                            </div>
                        </template>

                        <template #category="{ item }">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800"
                            >
                                {{ item.category }}
                            </span>
                        </template>

                        <template #merchant="{ item }">
                            <div class="font-medium text-gray-900">
                                {{ item.merchant || "N/A" }}
                            </div>
                        </template>

                        <template #receipts="{ item }">
                            <div class="flex items-center space-x-1">
                                <div
                                    v-for="media in item.media.slice(0, 3)"
                                    :key="media.id"
                                    class="relative"
                                >
                                    <img
                                        v-if="
                                            media.mime_type.startsWith('image/')
                                        "
                                        :src="media.url"
                                        :alt="media.original_name"
                                        class="h-10 w-10 object-cover rounded border shadow-sm"
                                    />
                                    <div
                                        v-else
                                        class="h-10 w-10 bg-blue-100 rounded border flex items-center justify-center shadow-sm"
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
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <span
                                    v-if="item.media.length > 3"
                                    class="text-xs text-gray-500 ml-1"
                                >
                                    +{{ item.media.length - 3 }}
                                </span>
                                <span
                                    v-else-if="item.media.length === 0"
                                    class="text-xs text-gray-400"
                                >
                                    No receipts
                                </span>
                            </div>
                        </template>
                    </DataTable>
                </div>

                <!-- Pagination -->
                <div class="border-t border-gray-200">
                    <Pagination
                        :links="expenses.links"
                        :from="expenses.from"
                        :to="expenses.to"
                        :total="expenses.total"
                        @navigate="handlePageChange"
                        class="bg-gray-50 px-6 py-3"
                    />
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <FormModal
            :show="showModal"
            :title="isEdit ? 'Edit Expense' : 'Create New Expense'"
            :fields="formFields"
            :initial-data="form"
            :loading="form.processing"
            @close="closeModal"
            @submit="handleSubmit"
            ref="formModal"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import FormModal from "@/Components/FormModal.vue";
import SearchFilter from "@/Components/SearchFilter.vue";
import Pagination from "@/Components/Pagination.vue";

// Props from Inertia
const { expenses, filters: initialFilters } = usePage().props;

// Reactive data
const loading = ref(false);
const showAdvancedFilters = ref(false);
const showModal = ref(false);
const isEdit = ref(false);
const formModal = ref(null);

// Form setup with Inertia useForm
const form = useForm({
    id: null,
    description: "",
    amount: 0,
    expense_date: new Date().toISOString().split("T")[0],
    category: "",
    status: "pending",
    payment_method: "cash",
    merchant: "",
    receipt_number: "",
    tax_amount: 0,
    notes: "",
});

// Filters
const filters = ref({
    search: initialFilters.search || "",
    status: initialFilters.status || "",
    category: initialFilters.category || "",
    date_from: initialFilters.date_from || "",
    date_to: initialFilters.date_to || "",
    min_amount: initialFilters.min_amount || "",
    max_amount: initialFilters.max_amount || "",
});

// Computed properties
const hasActiveFilters = computed(() => {
    return Object.values(filters.value).some((value) => value !== "");
});

// Table columns
const columns = [
    { key: "id", label: "ID", sortable: true },
    { key: "category", label: "Category", sortable: true },
    { key: "amount", label: "Amount", type: "currency", sortable: true },
    { key: "expense_date", label: "Date", type: "date", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "merchant", label: "Merchant", sortable: true },
    { key: "receipts", label: "Receipts", sortable: false },
];

// Status options
const statusOptions = [
    { value: "pending", label: "Pending" },
    { value: "paid", label: "Paid" },
    { value: "cancelled", label: "Cancelled" },
];

// Category options
const categoryOptions = [
    { value: "Labor", label: "Labor" },
    { value: "Software", label: "Software" },
    { value: "Table", label: "Table" },
    { value: "Advertising", label: "Advertising" },
    { value: "Office Supplies", label: "Office Supplies" },
    { value: "Travel", label: "Travel" },
    { value: "Utilities", label: "Utilities" },
    { value: "Marketing", label: "Marketing" },
    { value: "Inventory", label: "Inventory" },
];

// Form fields
const formFields = [
    {
        name: "category",
        label: "Category",
        type: "select",
        required: true,
        options: categoryOptions,
    },
    { name: "title", label: "Title", type: "text", required: true },
    {
        name: "description",
        label: "Description",
        type: "textarea",
        required: true,
    },
    {
        name: "amount",
        label: "Amount",
        type: "number",
        required: true,
        min: 0,
        step: 0.01,
    },
    {
        name: "expense_date",
        label: "Expense Date",
        type: "date",
        required: true,
    },
    { name: "merchant", label: "Merchant", type: "text", required: false },
    {
        name: "status",
        label: "Status",
        type: "select",
        required: true,
        options: statusOptions,
    },
    {
        name: "payment_method",
        label: "Payment Method",
        type: "select",
        required: true,
        options: [
            { value: "cash", label: "Cash" },
            { value: "card", label: "Card" },
            { value: "online", label: "Online" },
            { value: "bank_transfer", label: "Bank Transfer" },
        ],
    },
    {
        name: "receipt_number",
        label: "Receipt Number",
        type: "text",
        required: false,
    },
    {
        name: "tax_amount",
        label: "Tax Amount",
        type: "number",
        required: false,
        min: 0,
        step: 0.01,
    },
    { name: "notes", label: "Notes", type: "textarea", required: false },
];

// Table filters
const tableFilters = [
    { value: "pending", label: "Pending" },
    { value: "cancelled", label: "Cancelled" },
];

// Methods
const createExpense = () => {
    router.visit("/expenses/create");
};

const editExpense = (expense) => {
    router.visit(`/expenses/${expense.id}/edit`);
};

const viewExpense = (expense) => {
    router.visit(`/expenses/${expense.id}`);
};

const deleteExpense = (expense) => {
    if (confirm("Are you sure you want to delete this expense?")) {
        router.delete(`/expenses/${expense.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                // Expenses will be reloaded automatically
            },
        });
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

    router.get("/expenses", params, {
        preserveState: true,
        preserveScroll: true,
        only: ["expenses", "filters"],
        onError: (errors) => {
            console.error("Error applying filters:", errors);
            if (errors.message?.includes("403")) {
                alert("You don't have permission to view expenses.");
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
        category: "",
        date_from: "",
        date_to: "",
        min_amount: "",
        max_amount: "",
    };
    applyFilters();
};

const handlePageChange = (url) => {
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        only: ["expenses", "filters"],
    });
};

const handleSubmit = (formData) => {
    if (isEdit.value) {
        form.put(`/expenses/${form.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
            onError: (errors) => {
                if (formModal.value) {
                    formModal.value.setErrors(errors);
                }
            },
        });
    } else {
        form.post("/expenses", {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
            onError: (errors) => {
                if (formModal.value) {
                    formModal.value.setErrors(errors);
                }
            },
        });
    }
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    if (formModal.value) {
        formModal.value.resetForm();
    }
};

// Statistics methods
const getTotalExpenses = () => {
    return expenses.data
        .reduce((total, expense) => total + parseFloat(expense.amount || 0), 0)
        .toFixed(2);
};

const getAverageExpense = () => {
    if (expenses.data.length === 0) return "0.00";
    const total = expenses.data.reduce(
        (total, expense) => total + parseFloat(expense.amount || 0),
        0
    );
    return (total / expenses.data.length).toFixed(2);
};

const getPaidExpensesCount = () => {
    return expenses.data.filter((expense) => expense.status === "paid").length;
};

const getPendingExpensesCount = () => {
    return expenses.data.filter((expense) => expense.status === "pending")
        .length;
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
    return status.charAt(0).toUpperCase() + status.slice(1).replace("_", " ");
};

const getStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        paid: "bg-green-100 text-green-800",
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
