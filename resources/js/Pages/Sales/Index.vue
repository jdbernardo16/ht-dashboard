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
                    :loading="loading"
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

                <!-- Pagination -->
                <div class="mt-4">
                    <Pagination :links="paginationLinks" />
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

// Props from server
const props = defineProps({
    sales: {
        type: Object,
        required: true,
    },
    clients: {
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
const filters = ref({
    search: props.filters.search || "",
    status: props.filters.status || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
    min_amount: props.filters.min_amount || "",
    max_amount: props.filters.max_amount || "",
});

// Computed properties
const sales = computed(() => props.sales.data || []);
const paginationLinks = computed(() => props.sales.links || []);

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

// Table filters
const tableFilters = [
    { value: "pending", label: "Pending" },
    { value: "completed", label: "Completed" },
    { value: "cancelled", label: "Cancelled" },
];

// Methods
const createSale = () => {
    router.visit("/sales/create");
};

const editSale = (sale) => {
    router.visit(`/sales/${sale.id}/edit`);
};

const viewSale = (sale) => {
    router.visit(`/sales/${sale.id}`);
};

const deleteSale = async (sale) => {
    if (confirm("Are you sure you want to delete this sale?")) {
        try {
            loading.value = true;
            await router.delete(`/sales/${sale.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    // The page will automatically refresh with new data
                },
                onError: (errors) => {
                    console.error("Error deleting sale:", errors);
                    if (errors.message?.includes("403")) {
                        alert("You don't have permission to delete this sale.");
                    }
                },
                onFinish: () => {
                    loading.value = false;
                },
            });
        } catch (error) {
            loading.value = false;
            console.error("Error deleting sale:", error);
        }
    }
};

const applyFilters = () => {
    loading.value = true;
    const params = {};
    Object.keys(filters.value).forEach((key) => {
        if (filters.value[key]) params[key] = filters.value[key];
    });

    router.get("/sales", params, {
        preserveState: true,
        preserveScroll: true,
        only: ["sales", "filters"],
        onError: (errors) => {
            console.error("Error applying filters:", errors);
            if (errors.message?.includes("403")) {
                alert("You don't have permission to view sales.");
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
        date_from: "",
        date_to: "",
        min_amount: "",
        max_amount: "",
    };
    applyFilters();
};

// Utility functions
const formatCurrency = (value) => {
    return parseFloat(value).toFixed(2);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const formatStatus = (status) => {
    if (!status) return "";
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
