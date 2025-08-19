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
                <SearchFilter
                    v-model="filters"
                    :filters="[
                        'search',
                        'status',
                        'priority',
                        'assigned_to',
                        'date',
                    ]"
                    :status-options="statusOptions"
                    :priority-options="priorityOptions"
                    :user-options="userOptions"
                    @apply="applyFilters"
                    @clear="clearFilters"
                />

                <!-- Data Table -->
                <DataTable
                    :data="tasks"
                    :columns="columns"
                    :filters="tableFilters"
                    @create="createTask"
                    @view="viewTask"
                    @edit="editTask"
                    @delete="deleteTask"
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
                            :class="getDueDateClass(item.due_date, item.status)"
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
import { ref, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import FormModal from "@/Components/FormModal.vue";
import SearchFilter from "@/Components/SearchFilter.vue";

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

// Status options
const statusOptions = [
    { value: "pending", label: "Pending" },
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
    {
        name: "tags",
        label: "Tags",
        type: "text",
        required: false,
        placeholder: "Enter tags separated by commas",
    },
    { name: "notes", label: "Notes", type: "textarea", required: false },
    {
        name: "related_goal_id",
        label: "Related Goal",
        type: "select",
        required: false,
        options: [],
    },
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
    { value: "pending", label: "Pending" },
    { value: "in_progress", label: "In Progress" },
    { value: "completed", label: "Completed" },
    { value: "cancelled", label: "Cancelled" },
];

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

// Use props data instead of fetching
const users = computed(() => props.users);
const goals = computed(() => props.goals);
const tasks = computed(() => props.tasks.data || []);

// Update form fields with props data
const userOptions = computed(() =>
    users.value.map((user) => ({
        value: user.id,
        label: user.full_name || user.name,
    }))
);

const goalOptions = computed(() =>
    goals.value.map((goal) => ({
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
import { watch } from "vue";
watch(() => props.users, updateFormFields, { immediate: true });
watch(() => props.goals, updateFormFields, { immediate: true });
</script>
