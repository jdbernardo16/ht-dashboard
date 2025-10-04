<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Client Management
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage your client relationships and track their
                        activities
                    </p>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
                    <button
                        @click="createClient"
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
                        New Client
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Client Statistics Cards -->
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
            >
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
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-blue-600">
                                Total Clients
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ clients.length }}
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
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-green-600">
                                Total Sales
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ getTotalSales() }}
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
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-purple-600">
                                Content Posts
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ getTotalContentPosts() }}
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
                                New This Month
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ getNewClientsThisMonth() }}
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
                    class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-4"
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
                                Search clients
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
                                    placeholder="Search by name, email, or company..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                                    @input="handleSearchInput"
                                    aria-describedby="search-description"
                                />
                            </div>
                            <p
                                id="search-description"
                                class="mt-1 text-xs text-gray-500"
                            >
                                Search by client name, email, company, or phone
                            </p>
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
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

                        <!-- Company Filter -->
                        <div>
                            <label
                                for="company"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Company
                            </label>
                            <input
                                id="company"
                                v-model="filters.company"
                                type="text"
                                placeholder="Filter by company"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
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
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                            aria-label="Clear all filters"
                        >
                            Clear Filters
                        </button>
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm"
                            aria-label="Apply filters"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div
                v-if="loading && clients.length === 0"
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
                v-else-if="clients.length === 0 && !hasActiveFilters"
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
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                    ></path>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">
                    No clients yet
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Get started by adding your first client.
                </p>
                <div class="mt-6">
                    <button
                        @click="createClient"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                        aria-label="Create new client"
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
                        New Client
                    </button>
                </div>
            </div>

            <!-- No Results State -->
            <div
                v-else-if="clients.length === 0 && hasActiveFilters"
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
                    No clients found
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Try adjusting your search criteria or clear filters.
                </p>
                <div class="mt-6">
                    <button
                        @click="clearFilters"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
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
                        Clients List
                    </h2>
                </div>
                <div class="relative">
                    <!-- Loading Overlay -->
                    <div
                        v-if="loading"
                        class="absolute inset-0 bg-white bg-opacity-50 z-10 flex items-center justify-center"
                    >
                        <div
                            class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"
                        ></div>
                    </div>

                    <DataTable
                        :data="clients"
                        :columns="columns"
                        :filters="[]"
                        :loading="loading"
                        @create="createClient"
                        @view="viewClient"
                        @edit="editClient"
                        @delete="deleteClient"
                    >
                        <template #name="{ item }">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12">
                                    <div
                                        v-if="item.profile_image_url"
                                        class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden ring-2 ring-white shadow-sm"
                                    >
                                        <img
                                            :src="item.profile_image_url"
                                            :alt="`${item.first_name} ${item.last_name}`"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <div
                                        v-else
                                        class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center ring-2 ring-white shadow-sm"
                                    >
                                        <span
                                            class="text-white font-bold text-sm"
                                        >
                                            {{
                                                getInitials(
                                                    item.first_name,
                                                    item.last_name
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="font-semibold text-gray-900">
                                        {{ item.first_name }}
                                        {{ item.last_name }}
                                    </div>
                                    <div
                                        v-if="item.company"
                                        class="text-sm text-gray-500"
                                    >
                                        {{ item.company }}
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template #email="{ item }">
                            <a
                                v-if="item.email"
                                :href="`mailto:${item.email}`"
                                class="text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200"
                            >
                                {{ item.email }}
                            </a>
                            <span v-else class="text-gray-400">N/A</span>
                        </template>

                        <template #phone="{ item }">
                            <div class="flex items-center">
                                <svg
                                    v-if="item.phone"
                                    class="w-4 h-4 text-gray-400 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                    ></path>
                                </svg>
                                <span v-if="item.phone" class="text-gray-900">{{
                                    item.phone
                                }}</span>
                                <span v-else class="text-gray-400">N/A</span>
                            </div>
                        </template>

                        <template #category="{ item }">
                            <span
                                v-if="item.category"
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800"
                            >
                                {{ item.category }}
                            </span>
                            <span v-else class="text-gray-400"
                                >Uncategorized</span
                            >
                        </template>

                        <template #sales_count="{ item }">
                            <div class="flex items-center">
                                <svg
                                    class="w-4 h-4 text-green-500 mr-1"
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
                                <span class="font-semibold text-green-600">{{
                                    item.sales_count || 0
                                }}</span>
                            </div>
                        </template>

                        <template #content_posts_count="{ item }">
                            <div class="flex items-center">
                                <svg
                                    class="w-4 h-4 text-purple-500 mr-1"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    ></path>
                                </svg>
                                <span class="font-semibold text-purple-600">{{
                                    item.content_posts_count || 0
                                }}</span>
                            </div>
                        </template>
                    </DataTable>
                </div>

                <!-- Pagination -->
                <div class="border-t border-gray-200">
                    <Pagination
                        :links="props.clients.links"
                        :from="props.clients.from"
                        :to="props.clients.to"
                        :total="props.clients.total"
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
    clients: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Reactive data
const loading = ref(false);
const filters = ref({
    search: props.filters.search || "",
    category: props.filters.category || "",
    company: props.filters.company || "",
});

// Computed properties
const clients = computed(() => props.clients.data || []);
const hasActiveFilters = computed(() => {
    return Object.values(filters.value).some((value) => value !== "");
});

// Category options for filter dropdown
const categoryOptions = [
    { value: "Consignment Partner", label: "Consignment Partner" },
    { value: "Direct Buyer", label: "Direct Buyer" },
    { value: "Wholesale Client", label: "Wholesale Client" },
    { value: "Retail Customer", label: "Retail Customer" },
    { value: "Corporate Account", label: "Corporate Account" },
    { value: "Auction House", label: "Auction House" },
    { value: "Other", label: "Other" },
];

// Table columns
const columns = [
    { key: "name", label: "Client", sortable: true },
    { key: "email", label: "Email", sortable: true },
    { key: "phone", label: "Phone", sortable: true },
    { key: "company", label: "Company", sortable: true },
    { key: "category", label: "Category", sortable: true },
    { key: "sales_count", label: "Sales", type: "number", sortable: true },
    {
        key: "content_posts_count",
        label: "Content Posts",
        type: "number",
        sortable: true,
    },
];

// Methods
const createClient = () => {
    router.visit("/clients/create");
};

const editClient = (client) => {
    router.visit(`/clients/${client.id}/edit`);
};

const viewClient = (client) => {
    router.visit(`/clients/${client.id}`);
};

const deleteClient = async (client) => {
    if (
        confirm(
            "Are you sure you want to delete this client? This action cannot be undone."
        )
    ) {
        try {
            loading.value = true;
            await router.delete(`/clients/${client.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    // The page will automatically refresh with new data
                },
                onError: (errors) => {
                    console.error("Error deleting client:", errors);
                    if (errors.message?.includes("403")) {
                        alert(
                            "You don't have permission to delete this client."
                        );
                    }
                },
                onFinish: () => {
                    loading.value = false;
                },
            });
        } catch (error) {
            loading.value = false;
            console.error("Error deleting client:", error);
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

    router.get("/clients", params, {
        preserveState: true,
        preserveScroll: true,
        only: ["clients", "filters"],
        onError: (errors) => {
            console.error("Error applying filters:", errors);
            if (errors.message?.includes("403")) {
                alert("You don't have permission to view clients.");
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
        category: "",
        company: "",
    };
    applyFilters();
};

const handlePageChange = (url) => {
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        only: ["clients", "filters"],
    });
};

// Statistics methods
const getTotalSales = () => {
    return clients.value.reduce(
        (total, client) => total + (client.sales_count || 0),
        0
    );
};

const getTotalContentPosts = () => {
    return clients.value.reduce(
        (total, client) => total + (client.content_posts_count || 0),
        0
    );
};

const getNewClientsThisMonth = () => {
    const currentMonth = new Date().getMonth();
    const currentYear = new Date().getFullYear();

    return clients.value.filter((client) => {
        const createdDate = new Date(client.created_at);
        return (
            createdDate.getMonth() === currentMonth &&
            createdDate.getFullYear() === currentYear
        );
    }).length;
};

// Utility functions
const getInitials = (firstName, lastName) => {
    return `${firstName?.charAt(0) || ""}${
        lastName?.charAt(0) || ""
    }`.toUpperCase();
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
