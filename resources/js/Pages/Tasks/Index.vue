<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Task Management
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage and track all your tasks in one place
                    </p>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
                    <button
                        @click="createTask"
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
                        New Task
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Task Statistics Cards -->
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
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-blue-600">
                                Total Tasks
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ tasks.length }}
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
                                Completed
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ getCompletedTasksCount() }}
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
                                In Progress
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ getInProgressTasksCount() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-red-50 to-pink-50 rounded-xl p-3 border border-red-100"
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
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-red-600">
                                Overdue
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ getOverdueTasksCount() }}
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
                    class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4"
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
                                Search tasks
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
                                    placeholder="Search tasks..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors duration-200"
                                    @input="handleSearchInput"
                                    aria-describedby="search-description"
                                />
                            </div>
                            <p
                                id="search-description"
                                class="mt-1 text-xs text-gray-500"
                            >
                                Search by title, description, or assignee
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
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
                    </div>

                    <!-- Advanced Filters Toggle -->
                    <div class="mt-4">
                        <button
                            @click="showAdvancedFilters = !showAdvancedFilters"
                            class="text-sm text-indigo-600 hover:text-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors duration-200"
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
                        <!-- Assigned To Filter -->
                        <div>
                            <label
                                for="assigned_to"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Assigned To
                            </label>
                            <select
                                id="assigned_to"
                                v-model="filters.assigned_to"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                                @change="handleFilterChange"
                            >
                                <option value="">All Users</option>
                                <option
                                    v-for="user in userOptions"
                                    :key="user.value"
                                    :value="user.value"
                                >
                                    {{ user.label }}
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
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
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                            aria-label="Clear all filters"
                        >
                            Clear Filters
                        </button>
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-sm"
                            aria-label="Apply filters"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div
                v-if="loading && tasks.length === 0"
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
                v-else-if="tasks.length === 0 && !hasActiveFilters"
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
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                    ></path>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">
                    No tasks yet
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Get started by creating your first task.
                </p>
                <div class="mt-6">
                    <button
                        @click="createTask"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                        aria-label="Create new task"
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
                        New Task
                    </button>
                </div>
            </div>

            <!-- No Results State -->
            <div
                v-else-if="tasks.length === 0 && hasActiveFilters"
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
                    No tasks found
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Try adjusting your search criteria or clear filters.
                </p>
                <div class="mt-6">
                    <button
                        @click="clearFilters"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
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
                    <h2 class="text-xl font-semibold text-white">Tasks List</h2>
                </div>
                <div class="relative">
                    <!-- Loading Overlay -->
                    <div
                        v-if="loading"
                        class="absolute inset-0 bg-white bg-opacity-50 z-10 flex items-center justify-center"
                    >
                        <div
                            class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"
                        ></div>
                    </div>

                    <DataTable
                        :data="tasks"
                        :columns="columns"
                        :filters="tableFilters"
                        @create="createTask"
                        @view="viewTask"
                        @edit="editTask"
                        @delete="deleteTask"
                        aria-label="Tasks list"
                    >
                        <template #title="{ item }">
                            <div class="font-medium text-gray-900">
                                {{ item.title }}
                            </div>
                        </template>

                        <template #priority="{ item }">
                            <span
                                :class="getPriorityClass(item.priority)"
                                class="px-2 py-1 text-xs font-medium rounded-full"
                            >
                                {{ formatPriority(item.priority) }}
                            </span>
                        </template>

                        <template #status="{ item }">
                            <span
                                :class="getStatusClass(item.status)"
                                class="px-2 py-1 text-xs font-medium rounded-full"
                            >
                                {{ formatStatus(item.status) }}
                            </span>
                        </template>

                        <template #due_date="{ item }">
                            <span
                                :class="
                                    getDueDateClass(item.due_date, item.status)
                                "
                                class="text-sm font-medium"
                            >
                                {{ formatDate(item.due_date) }}
                            </span>
                        </template>

                        <template #assigned_to="{ item }">
                            {{
                                item.assigned_to?.full_name ||
                                item.assigned_to?.name ||
                                "Unassigned"
                            }}
                        </template>

                        <template #estimated_hours="{ item }">
                            {{ item.estimated_hours || "-" }} hrs
                        </template>
                    </DataTable>
                </div>

                <!-- Pagination -->
                <div class="border-t border-gray-200">
                    <Pagination
                        :links="props.tasks.links"
                        :from="props.tasks.from"
                        :to="props.tasks.to"
                        :total="props.tasks.total"
                        @navigate="handlePageChange"
                        class="bg-gray-50 px-6 py-3"
                    />
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <FormModal
            :show="showModal"
            :title="isEdit ? 'Edit Task' : 'Create New Task'"
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
import { ref, computed, onMounted, watch } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import FormModal from "@/Components/FormModal.vue";
import SearchFilter from "@/Components/SearchFilter.vue";
import Pagination from "@/Components/Pagination.vue";

// Props from controller
const props = defineProps({
    tasks: {
        type: Object,
        required: true,
    },
    users: {
        type: Array,
        default: () => [],
    },
    goals: {
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
const showModal = ref(false);
const showAdvancedFilters = ref(false);
const isEdit = ref(false);
const form = ref({});
const filters = ref({
    search: props.filters.search || "",
    status: props.filters.status || "",
    priority: props.filters.priority || "",
    assigned_to: props.filters.assigned_to || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
});

// Status options
const statusOptions = [
    { value: "pending", label: "Pending" },
    { value: "not_started", label: "Not Started" },
    { value: "in_progress", label: "In Progress" },
    { value: "completed", label: "Completed" },
    { value: "cancelled", label: "Cancelled" },
];

// Priority options
const priorityOptions = [
    { value: "low", label: "Low" },
    { value: "medium", label: "Medium" },
    { value: "high", label: "High" },
    { value: "urgent", label: "Urgent" },
];

// Table columns
const columns = [
    { key: "id", label: "ID", sortable: true },
    { key: "title", label: "Title", sortable: true },
    { key: "priority", label: "Priority", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "due_date", label: "Due Date", type: "date", sortable: true },
    { key: "assigned_to", label: "Assigned To", sortable: true },
    { key: "estimated_hours", label: "Est. Hours", sortable: true },
];

// Table filters
const tableFilters = [
    { value: "pending", label: "Pending" },
    { value: "not_started", label: "Not Started" },
    { value: "in_progress", label: "In Progress" },
    { value: "completed", label: "Completed" },
    { value: "cancelled", label: "Cancelled" },
];

// Computed properties
const tasks = computed(() => props.tasks.data || []);
const hasActiveFilters = computed(() => {
    return Object.values(filters.value).some((value) => value !== "");
});

// User options for filter
const userOptions = computed(() => {
    return props.users.map((user) => ({
        value: user.id,
        label: user.name || user.full_name,
    }));
});

// Form fields
const formFields = [
    { name: "title", label: "Title", type: "text", required: true },
    {
        name: "description",
        label: "Description",
        type: "textarea",
        required: false,
        rows: 4,
    },
    {
        name: "status",
        label: "Status",
        type: "select",
        required: true,
        options: statusOptions,
    },
    {
        name: "priority",
        label: "Priority",
        type: "select",
        required: true,
        options: priorityOptions,
    },
    {
        name: "assigned_to",
        label: "Assigned To",
        type: "select",
        required: false,
        options: userOptions,
    },
    {
        name: "due_date",
        label: "Due Date",
        type: "date",
        required: false,
    },
    {
        name: "estimated_hours",
        label: "Estimated Hours",
        type: "number",
        required: false,
        min: 0,
        step: 0.5,
    },
    {
        name: "notes",
        label: "Notes",
        type: "textarea",
        required: false,
        rows: 3,
    },
];

// Methods
const createTask = () => {
    router.visit("/tasks/create");
};

const editTask = (task) => {
    router.visit(`/tasks/${task.id}/edit`);
};

const viewTask = (task) => {
    router.visit(`/tasks/${task.id}`);
};

const deleteTask = async (task) => {
    if (confirm("Are you sure you want to delete this task?")) {
        try {
            loading.value = true;
            await router.delete(`/tasks/${task.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    // The page will automatically refresh with new data
                },
                onError: (errors) => {
                    console.error("Error deleting task:", errors);
                    if (errors.message?.includes("403")) {
                        alert("You don't have permission to delete this task.");
                    }
                },
                onFinish: () => {
                    loading.value = false;
                },
            });
        } catch (error) {
            loading.value = false;
            console.error("Error deleting task:", error);
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

    router.get("/tasks", params, {
        preserveState: true,
        preserveScroll: true,
        only: ["tasks", "filters"],
        onError: (errors) => {
            console.error("Error applying filters:", errors);
            if (errors.message?.includes("403")) {
                alert("You don't have permission to view tasks.");
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
        priority: "",
        assigned_to: "",
        date_from: "",
        date_to: "",
    };
    applyFilters();
};

const handlePageChange = (url) => {
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        only: ["tasks", "users", "goals", "filters"],
    });
};

const closeModal = () => {
    showModal.value = false;
    form.value = {};
    if (formModal.value) {
        formModal.value.resetForm();
    }
};

const handleSubmit = (formData) => {
    loading.value = true;
    // Handle form submission logic here
    // This would typically involve an API call or form submission
};

// Statistics methods
const getCompletedTasksCount = () => {
    return tasks.value.filter((task) => task.status === "completed").length;
};

const getInProgressTasksCount = () => {
    return tasks.value.filter((task) => task.status === "in_progress").length;
};

const getOverdueTasksCount = () => {
    const today = new Date();
    return tasks.value.filter((task) => {
        return (
            task.due_date &&
            new Date(task.due_date) < today &&
            task.status !== "completed"
        );
    }).length;
};

// Utility functions
const formatDate = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString();
};

const formatStatus = (status) => {
    if (!status) return "Unknown";
    return status.charAt(0).toUpperCase() + status.slice(1).replace("_", " ");
};

const formatPriority = (priority) => {
    if (!priority) return "Unknown";
    return priority.charAt(0).toUpperCase() + priority.slice(1);
};

const getStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        not_started: "bg-gray-100 text-gray-800",
        in_progress: "bg-blue-100 text-blue-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
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

const getDueDateClass = (dueDate, status) => {
    if (!dueDate || status === "completed") return "text-gray-900";

    const today = new Date();
    const due = new Date(dueDate);
    const diffTime = due - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays < 0) return "text-red-600 font-medium";
    if (diffDays <= 3) return "text-yellow-600 font-medium";
    return "text-gray-900";
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
