<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Task Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filter Bar -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
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
                                        />
                                    </svg>
                                </div>
                                <input
                                    id="search"
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Search tasks..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
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
                            class="text-sm text-indigo-600 hover:text-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
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
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
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
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            aria-label="Clear all filters"
                        >
                            Clear Filters
                        </button>
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            aria-label="Apply filters"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div
                    v-if="loading && tasks.length === 0"
                    class="bg-white rounded-lg shadow-sm p-6"
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
                    class="bg-white rounded-lg shadow-sm p-12 text-center"
                >
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        No tasks yet
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Get started by creating your first task.
                    </p>
                    <div class="mt-6">
                        <button
                            @click="createTask"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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
                                />
                            </svg>
                            New Task
                        </button>
                    </div>
                </div>

                <!-- No Results State -->
                <div
                    v-else-if="tasks.length === 0 && hasActiveFilters"
                    class="bg-white rounded-lg shadow-sm p-12 text-center"
                >
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        No tasks found
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Try adjusting your search criteria or clear filters.
                    </p>
                    <div class="mt-6">
                        <button
                            @click="clearFilters"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            aria-label="Clear filters"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Data Table -->
                <div
                    v-else
                    class="bg-white shadow-sm rounded-lg overflow-hidden"
                >
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
                                        getDueDateClass(
                                            item.due_date,
                                            item.status
                                        )
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
                    <Pagination
                        :links="props.tasks.links"
                        :from="props.tasks.from"
                        :to="props.tasks.to"
                        :total="props.tasks.total"
                        @navigate="handlePageChange"
                        class="border-t border-gray-200"
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
    { name: "due_date", label: "Due Date", type: "date", required: true },
    {
        name: "assigned_to",
        label: "Assigned To",
        type: "select",
        required: false,
        options: [],
    },
    { name: "category", label: "Category", type: "text", required: false },
    {
        name: "estimated_hours",
        label: "Estimated Hours",
        type: "number",
        required: false,
        min: 0,
        step: 0.5,
    },
    {
        name: "actual_hours",
        label: "Actual Hours",
        type: "number",
        required: false,
        min: 0,
        step: 0.5,
    },
];

// Computed properties
const tasks = computed(() => props.tasks.data || []);
const hasActiveFilters = computed(() =>
    Object.values(filters.value).some((value) => value && value !== "")
);

const userOptions = computed(() =>
    props.users.map((user) => ({
        value: user.id,
        label: user.full_name || user.name,
    }))
);

const goalOptions = computed(() =>
    props.goals.map((goal) => ({
        value: goal.id,
        label: goal.title,
    }))
);

// Update form fields dynamically
const updateFormFields = () => {
    const assignedToField = formFields.find((f) => f.name === "assigned_to");
    if (assignedToField) {
        assignedToField.options = userOptions.value;
    }

    const goalField = formFields.find((f) => f.name === "related_goal_id");
    if (goalField) {
        goalField.options = goalOptions.value;
    }
};

// Methods
const fetchTasks = async () => {
    loading.value = true;
    try {
        const params = {};
        Object.keys(filters.value).forEach((key) => {
            if (filters.value[key]) params[key] = filters.value[key];
        });

        router.get("/tasks", params, {
            preserveState: true,
            preserveScroll: true,
            only: ["tasks", "filters"],
            onError: (errors) => {
                console.error("Error fetching tasks:", errors);
            },
        });
    } catch (error) {
        console.error("Error fetching tasks:", error);
    } finally {
        loading.value = false;
    }
};

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
            await router.delete(`/tasks/${task.id}`);
            await fetchTasks();
        } catch (error) {
            console.error("Error deleting task:", error);
        }
    }
};

const handleSubmit = async (formData) => {
    loading.value = true;
    try {
        // Handle tags conversion
        if (formData.tags) {
            formData.tags = formData.tags
                .split(",")
                .map((tag) => tag.trim())
                .filter((tag) => tag);
        }

        // Convert checkbox values to proper boolean type
        if (formData.is_recurring !== undefined) {
            formData.is_recurring = Boolean(formData.is_recurring);
        }

        if (isEdit.value) {
            await router.put(`/tasks/${form.value.id}`, formData);
        } else {
            await router.post("/tasks", formData);
        }
        await fetchTasks();
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            const validationErrors = error.response.data.errors;
            if (formModal.value) {
                formModal.value.setErrors(validationErrors);
            }
        } else {
            console.error("Error saving task:", error);
        }
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

const applyFilters = () => {
    fetchTasks();
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
    fetchTasks();
};

const handlePageChange = (url) => {
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        only: ["tasks", "users", "goals", "filters"],
    });
};

// Search input handler with debouncing
let searchTimeout = null;
const handleSearchInput = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchTasks();
    }, 300);
};

// Filter change handler
const handleFilterChange = () => {
    fetchTasks();
};

// Utility functions
const formatCurrency = (value) => {
    return parseFloat(value).toFixed(2);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1).replace("_", " ");
};

const formatPriority = (priority) => {
    return priority.charAt(0).toUpperCase() + priority.slice(1);
};

const getStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
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
    if (status === "completed" || status === "cancelled") return "";
    const today = new Date();
    const due = new Date(dueDate);
    if (due < today) return "text-red-600 font-medium";
    return "";
};

// Lifecycle
onMounted(() => {
    updateFormFields();
});

// Watch for changes in props
watch(() => props.users, updateFormFields, { immediate: true });
watch(() => props.goals, updateFormFields, { immediate: true });
</script>
