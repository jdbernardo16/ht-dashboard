<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Goals Management
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Set, track, and achieve your business goals
                    </p>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
                    <button
                        @click="createGoal"
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
                        New Goal
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Goals Statistics Cards -->
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
            >
                <div
                    class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100"
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
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-blue-600">
                                Total Goals
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ goals.length }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100"
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
                                Completed
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ getCompletedGoalsCount() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-6 border border-yellow-100"
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
                                        d="M13 10V3L4 14h7v7l9-11h-7z"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-yellow-600">
                                In Progress
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ getInProgressGoalsCount() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-100"
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
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-purple-600">
                                Total Target
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                ${{ getTotalTargetValue() }}
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
                    class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4"
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
                                Search goals
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
                                    placeholder="Search by title, description, or category..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                                    @input="handleSearchInput"
                                    aria-describedby="search-description"
                                />
                            </div>
                            <p
                                id="search-description"
                                class="mt-1 text-xs text-gray-500"
                            >
                                Search by title, description, category, or notes
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
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

                        <!-- Type Filter -->
                        <div>
                            <label
                                for="type"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Type
                            </label>
                            <select
                                id="type"
                                v-model="filters.type"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
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
                            class="text-sm text-blue-600 hover:text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
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
                        <!-- Priority Filter -->
                        <div>
                            <label
                                for="priority"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Priority
                            </label>
                            <select
                                id="priority"
                                v-model="filters.priority"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                @change="handleFilterChange"
                            >
                                <option value="">All Priorities</option>
                                <option
                                    v-for="priority in priorityOptions"
                                    :key="priority.value"
                                    :value="priority.value"
                                >
                                    {{ priority.label }}
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
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
                v-if="loading && goals.length === 0"
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
                v-else-if="goals.length === 0 && !hasActiveFilters"
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
                        d="M13 10V3L4 14h7v7l9-11h-7z"
                    ></path>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">
                    No goals yet
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Get started by creating your first goal.
                </p>
                <div class="mt-6">
                    <button
                        @click="createGoal"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                        aria-label="Create new goal"
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
                        New Goal
                    </button>
                </div>
            </div>

            <!-- No Results State -->
            <div
                v-else-if="goals.length === 0 && hasActiveFilters"
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
                    No goals found
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
                    <h2 class="text-xl font-semibold text-white">Goals List</h2>
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
                        :data="goals"
                        :columns="columns"
                        :filters="tableFilters"
                        @create="createGoal"
                        @view="viewGoal"
                        @edit="editGoal"
                        @delete="deleteGoal"
                    >
                        <template #title="{ item }">
                            <div class="font-semibold text-gray-900 max-w-xs">
                                {{ item.title }}
                            </div>
                        </template>

                        <template #type="{ item }">
                            <span
                                :class="getTypeClass(item.type)"
                                class="px-2 py-1 text-xs font-medium rounded-full"
                            >
                                {{ formatType(item.type) }}
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

                        <template #priority="{ item }">
                            <span
                                :class="getPriorityClass(item.priority)"
                                class="px-2 py-1 text-xs font-medium rounded-full"
                            >
                                {{ formatPriority(item.priority) }}
                            </span>
                        </template>

                        <template #target_value="{ item }">
                            <span class="font-semibold text-blue-600 text-lg">
                                ${{ formatCurrency(item.target_value) }}
                            </span>
                        </template>

                        <template #progress="{ item }">
                            <div class="w-full max-w-xs">
                                <div
                                    class="flex items-center justify-between mb-1"
                                >
                                    <span class="text-xs text-gray-600"
                                        >Progress</span
                                    >
                                    <span
                                        class="text-xs font-medium text-gray-900"
                                    >
                                        {{
                                            Math.round(
                                                (item.progress /
                                                    item.target_value) *
                                                    100
                                            )
                                        }}%
                                    </span>
                                </div>
                                <div
                                    class="w-full bg-gray-200 rounded-full h-2"
                                >
                                    <div
                                        class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 rounded-full transition-all duration-300"
                                        :style="{
                                            width: `${Math.min(
                                                (item.progress /
                                                    item.target_value) *
                                                    100,
                                                100
                                            )}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </template>

                        <template #target_date="{ item }">
                            <div class="text-sm text-gray-900">
                                {{ formatDate(item.target_date) }}
                            </div>
                        </template>
                    </DataTable>
                </div>

                <!-- Pagination -->
                <div class="border-t border-gray-200">
                    <Pagination
                        :links="goalsPagination.links"
                        :from="goalsPagination.from"
                        :to="goalsPagination.to"
                        :total="goalsPagination.total"
                        @navigate="handlePageChange"
                        class="bg-gray-50 px-6 py-3"
                    />
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <FormModal
            :show="showModal"
            :title="isEdit ? 'Edit Goal' : 'Create New Goal'"
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

// Reactive data
const goals = ref([]);
const goalsPagination = ref({});
const loading = ref(false);
const showAdvancedFilters = ref(false);
const showModal = ref(false);
const isEdit = ref(false);
const form = ref({});
const filters = ref({
    search: "",
    status: "",
    type: "",
    priority: "",
    date_from: "",
    date_to: "",
});

// Computed properties
const hasActiveFilters = computed(() => {
    return Object.values(filters.value).some((value) => value !== "");
});

// Table columns
const columns = [
    { key: "id", label: "ID", sortable: true },
    { key: "title", label: "Title", sortable: true },
    { key: "type", label: "Type", sortable: true },
    { key: "target_value", label: "Target", type: "currency", sortable: true },
    { key: "progress", label: "Progress", sortable: true },
    { key: "priority", label: "Priority", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "target_date", label: "Target Date", type: "date", sortable: true },
];

// Status options
const statusOptions = [
    { value: "not_started", label: "Not Started" },
    { value: "in_progress", label: "In Progress" },
    { value: "completed", label: "Completed" },
    { value: "failed", label: "Failed" },
];

// Type options
const typeOptions = [
    { value: "sales", label: "Sales" },
    { value: "revenue", label: "Revenue" },
    { value: "expense", label: "Expense" },
    { value: "task", label: "Task" },
    { value: "content", label: "Content" },
    { value: "other", label: "Other" },
];

// Priority options
const priorityOptions = [
    { value: "low", label: "Low" },
    { value: "medium", label: "Medium" },
    { value: "high", label: "High" },
    { value: "urgent", label: "Urgent" },
];

// Form fields
const formFields = [
    { name: "title", label: "Title", type: "text", required: true },
    {
        name: "description",
        label: "Description",
        type: "textarea",
        required: false,
    },
    {
        name: "type",
        label: "Type",
        type: "select",
        required: true,
        options: typeOptions,
    },
    {
        name: "target_value",
        label: "Target Value",
        type: "number",
        required: true,
        min: 0,
        step: 0.01,
    },
    {
        name: "current_value",
        label: "Current Value",
        type: "number",
        required: false,
        min: 0,
        step: 0.01,
    },
    {
        name: "budget",
        label: "Budget ($)",
        type: "number",
        required: false,
        min: 0,
        step: 0.01,
    },
    {
        name: "labor_hours",
        label: "Labor Hours",
        type: "number",
        required: false,
        min: 0,
        step: 0.01,
    },
    { name: "target_date", label: "Target Date", type: "date", required: true },
    {
        name: "priority",
        label: "Priority",
        type: "select",
        required: true,
        options: priorityOptions,
    },
    {
        name: "status",
        label: "Status",
        type: "select",
        required: true,
        options: statusOptions,
    },
    { name: "category", label: "Category", type: "text", required: false },
    { name: "notes", label: "Notes", type: "textarea", required: false },
    {
        name: "is_recurring",
        label: "Recurring",
        type: "checkbox",
        required: false,
    },
    {
        name: "recurring_frequency",
        label: "Frequency",
        type: "select",
        required: false,
        options: [
            { value: "daily", label: "Daily" },
            { value: "weekly", label: "Weekly" },
            { value: "monthly", label: "Monthly" },
            { value: "yearly", label: "Yearly" },
        ],
    },
];

// Table filters
const tableFilters = [
    { value: "not_started", label: "Not Started" },
    { value: "in_progress", label: "In Progress" },
    { value: "completed", label: "Completed" },
    { value: "failed", label: "Failed" },
];

// Methods
const fetchGoals = () => {
    loading.value = true;
    router.get("/goals", filters.value, {
        preserveState: true,
        onSuccess: (page) => {
            goalsPagination.value = page.props.goals;
            goals.value = page.props.goals.data || page.props.goals;
            loading.value = false;
        },
        onError: (errors) => {
            console.error("Error fetching goals:", errors);
            loading.value = false;
        },
    });
};

const createGoal = () => {
    router.visit("/goals/create");
};

const editGoal = (goal) => {
    router.visit(`/goals/${goal.id}/edit`);
};

const viewGoal = (goal) => {
    router.visit(`/goals/${goal.id}`);
};

const deleteGoal = (goal) => {
    if (confirm("Are you sure you want to delete this goal?")) {
        router.delete(`/goals/${goal.id}`, {
            onSuccess: () => fetchGoals(),
            onError: (errors) => console.error("Error deleting goal:", errors),
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
    fetchGoals();
};

const handleSubmit = (formData) => {
    loading.value = true;
    if (isEdit.value) {
        router.put(`/goals/${form.value.id}`, formData, {
            onSuccess: () => {
                fetchGoals();
                closeModal();
            },
            onError: (errors) => {
                if (formModal.value) {
                    formModal.value.setErrors(errors);
                }
                loading.value = false;
            },
        });
    } else {
        router.post("/goals", formData, {
            onSuccess: () => {
                fetchGoals();
                closeModal();
            },
            onError: (errors) => {
                if (formModal.value) {
                    formModal.value.setErrors(errors);
                }
                loading.value = false;
            },
        });
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
        type: "",
        priority: "",
        date_from: "",
        date_to: "",
    };
    fetchGoals();
};

const handlePageChange = (url) => {
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        only: ["goals", "filters"],
    });
};

// Statistics methods
const getCompletedGoalsCount = () => {
    return goals.value.filter((goal) => goal.status === "completed").length;
};

const getInProgressGoalsCount = () => {
    return goals.value.filter((goal) => goal.status === "in_progress").length;
};

const getTotalTargetValue = () => {
    return goals.value
        .reduce((total, goal) => total + parseFloat(goal.target_value || 0), 0)
        .toFixed(2);
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

const formatType = (type) => {
    return type.charAt(0).toUpperCase() + type.slice(1).replace("_", " ");
};

const formatPriority = (priority) => {
    return priority.charAt(0).toUpperCase() + priority.slice(1);
};

const getStatusClass = (status) => {
    const classes = {
        not_started: "bg-gray-100 text-gray-800",
        in_progress: "bg-blue-100 text-blue-800",
        completed: "bg-green-100 text-green-800",
        failed: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getTypeClass = (type) => {
    const classes = {
        sales: "bg-purple-100 text-purple-800",
        revenue: "bg-green-100 text-green-800",
        expense: "bg-red-100 text-red-800",
        task: "bg-blue-100 text-blue-800",
        content: "bg-orange-100 text-orange-800",
        other: "bg-gray-100 text-gray-800",
    };
    return classes[type] || "bg-gray-100 text-gray-800";
};

const getPriorityClass = (priority) => {
    const classes = {
        low: "bg-gray-100 text-gray-800",
        medium: "bg-yellow-100 text-yellow-800",
        high: "bg-orange-100 text-orange-800",
        urgent: "bg-red-100 text-red-800",
    };
    return classes[priority] || "bg-gray-100 text-gray-800";
};

// Lifecycle
onMounted(() => {
    fetchGoals();
});
</script>
