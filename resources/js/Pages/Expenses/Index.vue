<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Expense Management
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
                    :data="expenses"
                    :columns="columns"
                    :filters="tableFilters"
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
                        {{ item.category?.name || "N/A" }}
                    </template>
                </DataTable>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <FormModal
            :show="showModal"
            :title="isEdit ? 'Edit Expense' : 'Create New Expense'"
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
import axios from "axios";

// Reactive data
const expenses = ref([]);
const categories = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEdit = ref(false);
const form = ref({});
const filters = ref({
    search: "",
    status: "",
    category: "",
    date_from: "",
    date_to: "",
    min_amount: "",
    max_amount: "",
});

// Table columns
const columns = [
    { key: "id", label: "ID", sortable: true },
    { key: "description", label: "Description", sortable: true },
    { key: "category", label: "Category", sortable: true },
    { key: "amount", label: "Amount", type: "currency", sortable: true },
    { key: "expense_date", label: "Date", type: "date", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "merchant", label: "Merchant", sortable: true },
];

// Status options
const statusOptions = [
    { value: "pending", label: "Pending" },
    { value: "approved", label: "Approved" },
    { value: "rejected", label: "Rejected" },
];

// Form fields
const formFields = [
    {
        name: "category_id",
        label: "Category",
        type: "select",
        required: true,
        options: [],
    },
    { name: "description", label: "Description", type: "text", required: true },
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
    { value: "approved", label: "Approved" },
    { value: "rejected", label: "Rejected" },
];

// Methods
const fetchExpenses = async () => {
    loading.value = true;
    try {
        const params = new URLSearchParams();
        Object.keys(filters.value).forEach((key) => {
            if (filters.value[key]) params.append(key, filters.value[key]);
        });

        const response = await axios.get(`/api/expenses?${params}`);
        expenses.value = response.data.data;
    } catch (error) {
        console.error("Error fetching expenses:", error);
    } finally {
        loading.value = false;
    }
};

const fetchCategories = async () => {
    try {
        const response = await axios.get("/api/categories?type=expense");
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

const createExpense = () => {
    isEdit.value = false;
    form.value = {
        category_id: "",
        description: "",
        amount: 0,
        expense_date: new Date().toISOString().split("T")[0],
        merchant: "",
        status: "pending",
        payment_method: "cash",
        receipt_number: "",
        tax_amount: 0,
        notes: "",
    };
    showModal.value = true;
};

const editExpense = (expense) => {
    isEdit.value = true;
    form.value = { ...expense };
    showModal.value = true;
};

const viewExpense = (expense) => {
    router.visit(`/expenses/${expense.id}`);
};

const deleteExpense = async (expense) => {
    if (confirm("Are you sure you want to delete this expense?")) {
        try {
            await axios.delete(`/api/expenses/${expense.id}`);
            await fetchExpenses();
        } catch (error) {
            console.error("Error deleting expense:", error);
        }
    }
};

const handleSubmit = async (formData) => {
    loading.value = true;
    try {
        if (isEdit.value) {
            await axios.put(`/api/expenses/${form.value.id}`, formData);
        } else {
            await axios.post("/api/expenses", formData);
        }
        await fetchExpenses();
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            const validationErrors = error.response.data.errors;
            if (formModal.value) {
                formModal.value.setErrors(validationErrors);
            }
        } else {
            console.error("Error saving expense:", error);
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
    fetchExpenses();
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
    fetchExpenses();
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

const getStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        approved: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

// Lifecycle
onMounted(() => {
    fetchExpenses();
    fetchCategories();
});
</script>
