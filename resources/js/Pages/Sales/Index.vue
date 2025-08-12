<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Sales Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filter Bar -->
                <SearchFilter
                    v-model="filters"
                    :filters="['search', 'status', 'date', 'amount']"
                    :status-options="statusOptions"
                    @apply="applyFilters"
                    @clear="clearFilters"
                />

                <!-- Data Table -->
                <DataTable
                    :data="sales"
                    :columns="columns"
                    :filters="tableFilters"
                    @create="createSale"
                    @view="viewSale"
                    @edit="editSale"
                    @delete="deleteSale"
                >
                    <template #amount="{ item }">
                        <span class="font-medium text-green-600">
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

                    <template #sale_date="{ item }">
                        {{ formatDate(item.sale_date) }}
                    </template>

                    <template #client="{ item }">
                        {{ item.client?.name || "N/A" }}
                    </template>
                </DataTable>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <FormModal
            :show="showModal"
            :title="isEdit ? 'Edit Sale' : 'Create New Sale'"
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
const sales = ref([]);
const clients = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEdit = ref(false);
const form = ref({});
const filters = ref({
    search: "",
    status: "",
    date_from: "",
    date_to: "",
    min_amount: "",
    max_amount: "",
});

// Table columns
const columns = [
    { key: "id", label: "ID", sortable: true },
    { key: "product_name", label: "Product", sortable: true },
    { key: "client", label: "Client", sortable: true },
    { key: "amount", label: "Amount", type: "currency", sortable: true },
    { key: "sale_date", label: "Date", type: "date", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "payment_method", label: "Payment", sortable: true },
];

// Status options
const statusOptions = [
    { value: "pending", label: "Pending" },
    { value: "completed", label: "Completed" },
    { value: "cancelled", label: "Cancelled" },
];

// Form fields
const formFields = [
    {
        name: "client_id",
        label: "Client",
        type: "select",
        required: true,
        options: [],
    },
    {
        name: "product_name",
        label: "Product Name",
        type: "text",
        required: true,
    },
    {
        name: "description",
        label: "Description",
        type: "textarea",
        required: false,
    },
    {
        name: "amount",
        label: "Amount",
        type: "number",
        required: true,
        min: 0,
        step: 0.01,
    },
    { name: "sale_date", label: "Sale Date", type: "date", required: true },
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
        ],
    },
    { name: "notes", label: "Notes", type: "textarea", required: false },
];

// Table filters
const tableFilters = [
    { value: "pending", label: "Pending" },
    { value: "completed", label: "Completed" },
    { value: "cancelled", label: "Cancelled" },
];

// Methods
const fetchSales = async () => {
    loading.value = true;
    try {
        const params = new URLSearchParams();
        Object.keys(filters.value).forEach((key) => {
            if (filters.value[key]) params.append(key, filters.value[key]);
        });

        const response = await axios.get(`/api/sales?${params}`);
        sales.value = response.data.data;
    } catch (error) {
        console.error("Error fetching sales:", error);
    } finally {
        loading.value = false;
    }
};

const fetchClients = async () => {
    try {
        const response = await axios.get("/api/users?role=client");
        clients.value = response.data.data;
        // Update form fields with clients
        const clientField = formFields.find((f) => f.name === "client_id");
        if (clientField) {
            clientField.options = clients.value.map((client) => ({
                value: client.id,
                label: client.name,
            }));
        }
    } catch (error) {
        console.error("Error fetching clients:", error);
    }
};

const createSale = () => {
    isEdit.value = false;
    form.value = {
        client_id: "",
        product_name: "",
        description: "",
        amount: 0,
        sale_date: new Date().toISOString().split("T")[0],
        status: "pending",
        payment_method: "cash",
        notes: "",
    };
    showModal.value = true;
};

const editSale = (sale) => {
    isEdit.value = true;
    form.value = { ...sale };
    showModal.value = true;
};

const viewSale = (sale) => {
    router.visit(`/sales/${sale.id}`);
};

const deleteSale = async (sale) => {
    if (confirm("Are you sure you want to delete this sale?")) {
        try {
            await axios.delete(`/api/sales/${sale.id}`);
            await fetchSales();
        } catch (error) {
            console.error("Error deleting sale:", error);
        }
    }
};

const handleSubmit = async (formData) => {
    loading.value = true;
    try {
        if (isEdit.value) {
            await axios.put(`/api/sales/${form.value.id}`, formData);
        } else {
            await axios.post("/api/sales", formData);
        }
        await fetchSales();
        closeModal();
    } catch (error) {
        if (error.response?.status === 422) {
            // Validation errors
            const validationErrors = error.response.data.errors;
            if (formModal.value) {
                formModal.value.setErrors(validationErrors);
            }
        } else {
            console.error("Error saving sale:", error);
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
    fetchSales();
};

const clearFilters = () => {
    filters.value = {
        search: "",
        status: "",
        date_from: "",
        date_to: "",
        min_amount: "",
        max_amount: "",
    };
    fetchSales();
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
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

// Lifecycle
onMounted(() => {
    fetchSales();
    fetchClients();
});
</script>
