<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Expense Management
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filter Bar -->
                <SearchFilter
                    v-model="filters"
                    :filters="[
                        'search',
                        'status',
                        'category',
                        'date',
                        'amount',
                    ]"
                    :status-options="statusOptions"
                    :category-options="categoryOptions"
                    @apply="applyFilters"
                    @clear="clearFilters"
                />

                <!-- Data Table -->
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
                        <span class="font-medium text-red-600">
                            ${{ formatCurrency(item.amount) }}
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

                    <template #expense_date="{ item }">
                        {{ formatDate(item.expense_date) }}
                    </template>

                    <template #category="{ item }">
                        {{ item.category }}
                    </template>
                </DataTable>

                <!-- Pagination -->
                <Pagination
                    :links="expenses.links"
                    :from="expenses.from"
                    :to="expenses.to"
                    :total="expenses.total"
                    @navigate="handlePageChange"
                />
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

// Table columns
const columns = [
    { key: "id", label: "ID", sortable: true },
    { key: "category", label: "Category", sortable: true },
    { key: "amount", label: "Amount", type: "currency", sortable: true },
    { key: "expense_date", label: "Date", type: "date", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "merchant", label: "Merchant", sortable: true },
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

const getStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        paid: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};
</script>
