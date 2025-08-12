<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Goals Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filter Bar -->
                <SearchFilter
                    v-model="filters"
                    :filters="['search', 'status', 'type', 'priority', 'date']"
                    :status-options="statusOptions"
                    :type-options="typeOptions"
                    :priority-options="priorityOptions"
                    @apply="applyFilters"
                    @clear="clearFilters"
                />

                <!-- Data Table -->
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
                        <div class="font-medium text-gray-900">
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
                            class="px-2 py-1 text-xs font-medium rounded-full"
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
                        <span class="font-medium text-blue-600"
                            >${{ formatCurrency(item.target_value) }}</span
                        >
                    </template>

                    <template #progress="{ item }">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div
                                class="bg-blue-600 h-2 rounded-full"
                                :style="{
                                    width: `${Math.min(
                                        (item.progress / item.target_value) *
                                            100,
                                        100
                                    )}%`,
                                }"
                            ></div>
                        </div>
                        <span class="text-xs text-gray-600"
                            >{{
                                Math.round(
                                    (item.progress / item.target_value) * 100
                                )
                            }}%</span
                        >
                    </template>

                    <template #target_date="{ item }">
                        {{ formatDate(item.target_date) }}
                    </template>
                </DataTable>
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

// Reactive data
const goals = ref([]);
const loading = ref(false);
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
            goals.value = page.props.goals;
            loading.value = false;
        },
        onError: (errors) => {
            console.error("Error fetching goals:", errors);
            loading.value = false;
        },
    });
};

const createGoal = () => {
    isEdit.value = false;
    form.value = {
        title: "",
        description: "",
        type: "other",
        target_value: 0,
        current_value: 0,
        target_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)
            .toISOString()
            .split("T")[0],
        priority: "medium",
        status: "not_started",
        category: "",
        notes: "",
        is_recurring: false,
        recurring_frequency: "",
    };
    showModal.value = true;
};

const editGoal = (goal) => {
    isEdit.value = true;
    form.value = { ...goal };
    showModal.value = true;
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

const applyFilters = () => {
    fetchGoals();
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
