<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Content Management
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Plan, create, and manage your content across all
                        platforms
                    </p>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
                    <button
                        @click="createPost"
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
                        New Content
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Content Statistics Cards -->
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
            >
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
                                Total Posts
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ posts.length }}
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
                                Published
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ getPublishedPostsCount() }}
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
                                Scheduled
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ getScheduledPostsCount() }}
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
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-yellow-600">
                                Drafts
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ getDraftPostsCount() }}
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
                    class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4"
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
                                Search content
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
                                    placeholder="Search by title, description, or tags..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm transition-colors duration-200"
                                    @input="handleSearchInput"
                                    aria-describedby="search-description"
                                />
                            </div>
                            <p
                                id="search-description"
                                class="mt-1 text-xs text-gray-500"
                            >
                                Search by title, description, tags, or content
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200"
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

                        <!-- Content Type Filter -->
                        <div>
                            <label
                                for="content_type"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Type
                            </label>
                            <select
                                id="content_type"
                                v-model="filters.content_type"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200"
                                @change="handleFilterChange"
                            >
                                <option value="">All Types</option>
                                <option
                                    v-for="type in typeOptions"
                                    :key="type.value"
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Advanced Filters Toggle -->
                    <div class="mt-4">
                        <button
                            @click="showAdvancedFilters = !showAdvancedFilters"
                            class="text-sm text-purple-600 hover:text-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-colors duration-200"
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
                        <!-- Platform Filter -->
                        <div>
                            <label
                                for="platform"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Platform
                            </label>
                            <select
                                id="platform"
                                v-model="filters.platform"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200"
                                @change="handleFilterChange"
                            >
                                <option value="">All Platforms</option>
                                <option
                                    v-for="platform in platformOptions"
                                    :key="platform.value"
                                    :value="platform.value"
                                >
                                    {{ platform.label }}
                                </option>
                            </select>
                        </div>

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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200"
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200"
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
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                            aria-label="Clear all filters"
                        >
                            Clear Filters
                        </button>
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 shadow-sm"
                            aria-label="Apply filters"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div
                v-if="loading && posts.length === 0"
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
                v-else-if="posts.length === 0 && !hasActiveFilters"
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
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                    ></path>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">
                    No content yet
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Get started by creating your first content post.
                </p>
                <div class="mt-6">
                    <button
                        @click="createPost"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200"
                        aria-label="Create new content"
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
                        New Content
                    </button>
                </div>
            </div>

            <!-- No Results State -->
            <div
                v-else-if="posts.length === 0 && hasActiveFilters"
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
                    No content found
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Try adjusting your search criteria or clear filters.
                </p>
                <div class="mt-6">
                    <button
                        @click="clearFilters"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200"
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
                        Content List
                    </h2>
                </div>
                <div class="relative">
                    <!-- Loading Overlay -->
                    <div
                        v-if="loading"
                        class="absolute inset-0 bg-white bg-opacity-50 z-10 flex items-center justify-center"
                    >
                        <div
                            class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"
                        ></div>
                    </div>

                    <DataTable
                        :data="posts"
                        :columns="columns"
                        :filters="tableFilters"
                        @create="createPost"
                        @view="viewPost"
                        @edit="editPost"
                        @delete="deletePost"
                    >
                        <template #image="{ item }">
                            <div class="flex justify-center">
                                <div
                                    v-if="item.image_url"
                                    class="w-16 h-16 rounded-lg overflow-hidden border border-gray-200 shadow-sm"
                                >
                                    <img
                                        :src="item.image_url"
                                        :alt="item.title"
                                        class="w-full h-full object-cover"
                                        @error="handleImageError"
                                    />
                                </div>
                                <div
                                    v-else
                                    class="w-16 h-16 rounded-lg bg-gradient-to-br from-purple-100 to-pink-100 border border-gray-200 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-8 h-8 text-purple-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        ></path>
                                    </svg>
                                </div>
                            </div>
                        </template>

                        <template #title="{ item }">
                            <div
                                class="font-semibold text-gray-900 max-w-xs truncate"
                            >
                                {{ item.title }}
                            </div>
                        </template>

                        <template #platform="{ item }">
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="platform in Array.isArray(
                                        item.platform
                                    )
                                        ? item.platform
                                        : [item.platform]"
                                    :key="platform"
                                    class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full"
                                >
                                    {{ formatPlatform(platform) }}
                                </span>
                            </div>
                        </template>

                        <template #content_type="{ item }">
                            <span
                                :class="getTypeClass(item.content_type)"
                                class="px-2 py-1 text-xs font-medium rounded-full"
                            >
                                {{ formatType(item.content_type) }}
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

                        <template #scheduled_date="{ item }">
                            <div class="text-sm text-gray-900">
                                {{
                                    item.scheduled_date
                                        ? formatDate(item.scheduled_date)
                                        : "Not scheduled"
                                }}
                            </div>
                        </template>

                        <template #user="{ item }">
                            <div class="flex items-center">
                                <div
                                    class="w-6 h-6 bg-gray-200 rounded-full mr-2 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-3 h-3 text-gray-500"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        ></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-900">{{
                                    item.user?.name || "System"
                                }}</span>
                            </div>
                        </template>
                    </DataTable>
                </div>

                <!-- Pagination -->
                <div class="border-t border-gray-200">
                    <Pagination
                        :links="contentPosts.links"
                        :from="contentPosts.from"
                        :to="contentPosts.to"
                        :total="contentPosts.total"
                        @navigate="handlePageChange"
                        class="bg-gray-50 px-6 py-3"
                    />
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <FormModal
            :show="showModal"
            :title="isEdit ? 'Edit Content' : 'Create New Content'"
            :fields="formFields"
            :initial-data="form"
            :loading="loading"
            @close="closeModal"
            @submit="handleSubmit"
            ref="formModal"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import FormModal from "@/Components/FormModal.vue";
import SearchFilter from "@/Components/SearchFilter.vue";
import Pagination from "@/Components/Pagination.vue";
import axios from "axios";
import { createApiHeaders } from "@/Utils/utils";

// Props from controller
const props = defineProps({
    contentPosts: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Reactive data
const categories = ref([]);
const loading = ref(false);
const showAdvancedFilters = ref(false);
const showModal = ref(false);
const isEdit = ref(false);
const form = ref({});
const filters = ref({
    search: props.filters.search || "",
    status: props.filters.status || "",
    content_type: props.filters.content_type || "",
    platform: props.filters.platform || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
});

// Computed properties
const posts = computed(() => props.contentPosts.data || []);
const hasActiveFilters = computed(() => {
    return Object.values(filters.value).some((value) => value !== "");
});

// Table columns
const columns = [
    { key: "id", label: "ID", sortable: true },
    { key: "image", label: "Image", sortable: false },
    { key: "title", label: "Title", sortable: true },
    { key: "platform", label: "Platforms", sortable: true },
    { key: "content_type", label: "Type", sortable: true },
    { key: "content_category", label: "Category", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "scheduled_date", label: "Scheduled", type: "date", sortable: true },
    { key: "user", label: "Author", sortable: true },
];

// Status options
const statusOptions = [
    { value: "draft", label: "Draft" },
    { value: "published", label: "Published" },
    { value: "scheduled", label: "Scheduled" },
];

// Type options
const typeOptions = [
    { value: "blog", label: "Blog" },
    { value: "social_media", label: "Social Media" },
    { value: "email", label: "Email" },
    { value: "newsletter", label: "Newsletter" },
];

// Platform options
const platformOptions = [
    { value: "facebook", label: "Facebook" },
    { value: "instagram", label: "Instagram" },
    { value: "twitter", label: "Twitter" },
    { value: "linkedin", label: "LinkedIn" },
    { value: "youtube", label: "YouTube" },
    { value: "tiktok", label: "TikTok" },
    { value: "website", label: "Website" },
    { value: "other", label: "Other" },
];

// Form fields
const formFields = [
    { name: "title", label: "Title", type: "text", required: true },
    {
        name: "content",
        label: "Content",
        type: "textarea",
        required: true,
        rows: 5,
    },
    {
        name: "type",
        label: "Type",
        type: "select",
        required: true,
        options: typeOptions,
    },
    {
        name: "status",
        label: "Status",
        type: "select",
        required: true,
        options: statusOptions,
    },
    {
        name: "category_id",
        label: "Category",
        type: "select",
        required: false,
        options: [],
    },
    {
        name: "scheduled_at",
        label: "Scheduled Date",
        type: "datetime-local",
        required: false,
    },
    {
        name: "tags",
        label: "Tags",
        type: "text",
        required: false,
        placeholder: "Enter tags separated by commas",
    },
    {
        name: "meta_description",
        label: "Meta Description",
        type: "textarea",
        required: false,
        rows: 2,
    },
    {
        name: "seo_keywords",
        label: "SEO Keywords",
        type: "text",
        required: false,
        placeholder: "Enter keywords separated by commas",
    },
    {
        name: "image",
        label: "Upload Image",
        type: "file",
        required: false,
        accept: "image/*",
    },
];

// Table filters
const tableFilters = [
    { value: "draft", label: "Draft" },
    { value: "published", label: "Published" },
    { value: "scheduled", label: "Scheduled" },
];

// Methods
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
    const params = {};
    Object.keys(filters.value).forEach((key) => {
        if (filters.value[key]) params[key] = filters.value[key];
    });

    router.get("/content", params, {
        preserveState: true,
        preserveScroll: true,
        only: ["contentPosts", "filters"],
        onError: (errors) => {
            console.error("Error fetching content:", errors);
        },
    });
};

const fetchCategories = async () => {
    try {
        const response = await axios.get("/api/categories", {
            headers: createApiHeaders(),
            withCredentials: true,
        });
        categories.value = response.data.data;
        // Update form fields with categories
        const categoryField = formFields.find((f) => f.name === "category_id");
        if (categoryField) {
            categoryField.options = categories.value.map((category) => ({
                value: category.id,
                label: category.name,
            }));
        }
    } catch (error) {
        console.error("Error fetching categories:", error);
    }
};

const createPost = () => {
    router.visit("/content/create");
};

const editPost = (post) => {
    router.visit(`/content/${post.id}/edit`);
};

const viewPost = (post) => {
    router.visit(`/content/${post.id}`);
};

const deletePost = async (post) => {
    if (confirm("Are you sure you want to delete this content post?")) {
        try {
            await router.delete(`/content/${post.id}`);
            router.reload({ only: ["contentPosts"] });
        } catch (error) {
            console.error("Error deleting content:", error);
        }
    }
};

const handleSubmit = async (formData) => {
    loading.value = true;

    try {
        // Create FormData for file uploads
        const submitData = new FormData();

        // Add all form fields to FormData
        Object.keys(formData).forEach((key) => {
            if (formData[key] instanceof File) {
                // Handle file uploads
                submitData.append(key, formData[key]);
            } else if (Array.isArray(formData[key])) {
                // Handle arrays
                formData[key].forEach((item, index) => {
                    submitData.append(`${key}[${index}]`, item);
                });
            } else {
                // Handle regular fields
                submitData.append(key, formData[key] || "");
            }
        });

        const url = isEdit.value ? `/content/${form.value.id}` : "/content";
        const method = isEdit.value ? "put" : "post";

        await router[method](url, submitData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                router.reload({ only: ["contentPosts"] });
            },
            onError: (errors) => {
                if (formModal.value) {
                    formModal.value.setErrors(errors);
                }
                console.error("Error saving content:", errors);
            },
        });
    } catch (error) {
        console.error("Error submitting form:", error);
    } finally {
        loading.value = false;
    }
};

const closeModal = () => {
    showModal.value = false;
    form.value = {};
    if (formModal.value) {
        formModal.value.resetForm();
    }
};

const clearFilters = () => {
    filters.value = {
        search: "",
        status: "",
        content_type: "",
        platform: "",
        date_from: "",
        date_to: "",
    };
    applyFilters();
};

const handlePageChange = (url) => {
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        only: ["contentPosts", "filters"],
    });
};

// Statistics methods
const getPublishedPostsCount = () => {
    return posts.value.filter((post) => post.status === "published").length;
};

const getScheduledPostsCount = () => {
    return posts.value.filter((post) => post.status === "scheduled").length;
};

const getDraftPostsCount = () => {
    return posts.value.filter((post) => post.status === "draft").length;
};

// Utility functions
const formatDate = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString();
};

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1).replace("_", " ");
};

const formatPlatform = (platform) => {
    const platformMap = {
        website: "Website",
        facebook: "Facebook",
        instagram: "Instagram",
        twitter: "Twitter",
        linkedin: "LinkedIn",
        tiktok: "TikTok",
        youtube: "YouTube",
        pinterest: "Pinterest",
        email: "Email",
        other: "Other",
    };
    return (
        platformMap[platform] ||
        platform.charAt(0).toUpperCase() + platform.slice(1)
    );
};

const formatType = (type) => {
    return type.charAt(0).toUpperCase() + type.slice(1).replace("_", " ");
};

const getStatusClass = (status) => {
    const classes = {
        draft: "bg-gray-100 text-gray-800",
        published: "bg-green-100 text-green-800",
        scheduled: "bg-blue-100 text-blue-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getTypeClass = (type) => {
    const classes = {
        blog: "bg-purple-100 text-purple-800",
        social_media: "bg-blue-100 text-blue-800",
        email: "bg-green-100 text-green-800",
        newsletter: "bg-orange-100 text-orange-800",
    };
    return classes[type] || "bg-gray-100 text-gray-800";
};

const handleImageError = (event) => {
    event.target.style.display = "none";
    event.target.parentElement.innerHTML = `
        <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-purple-100 to-pink-100 border border-gray-200 flex items-center justify-center">
            <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
        </div>
    `;
};

// Lifecycle
onMounted(() => {
    fetchCategories();
});
</script>
