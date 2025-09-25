<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Client Management
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filter Bar -->
                <SearchFilter
                    v-model="filters"
                    :filters="['search']"
                    @apply="applyFilters"
                    @clear="clearFilters"
                />

                <!-- Data Table -->
                <DataTable
                    :data="clients"
                    :columns="columns"
                    :filters="[]"
                    :loading="loading"
                    @create="createClient"
                    @view="viewClient"
                    @edit="editClient"
                    @delete="deleteClient"
                >
                    <template #name="{ item }">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div
                                    class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center"
                                >
                                    <span
                                        class="text-white font-medium text-sm"
                                    >
                                        {{
                                            getInitials(
                                                item.first_name,
                                                item.last_name
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="font-medium text-gray-900">
                                    {{ item.first_name }} {{ item.last_name }}
                                </div>
                                <div
                                    v-if="item.company"
                                    class="text-sm text-gray-500"
                                >
                                    {{ item.company }}
                                </div>
                            </div>
                        </div>
                    </template>

                    <template #email="{ item }">
                        <a
                            v-if="item.email"
                            :href="`mailto:${item.email}`"
                            class="text-indigo-600 hover:text-indigo-900"
                        >
                            {{ item.email }}
                        </a>
                        <span v-else class="text-gray-400">N/A</span>
                    </template>

                    <template #phone="{ item }">
                        <span v-if="item.phone">{{ item.phone }}</span>
                        <span v-else class="text-gray-400">N/A</span>
                    </template>

                    <template #sales_count="{ item }">
                        <span class="font-medium text-blue-600">
                            {{ item.sales_count || 0 }}
                        </span>
                    </template>

                    <template #content_posts_count="{ item }">
                        <span class="font-medium text-green-600">
                            {{ item.content_posts_count || 0 }}
                        </span>
                    </template>
                </DataTable>

                <!-- Pagination -->
                <Pagination
                    :links="props.clients.links"
                    :from="props.clients.from"
                    :to="props.clients.to"
                    :total="props.clients.total"
                    @navigate="handlePageChange"
                />
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
import Pagination from "@/Components/Pagination.vue";

// Props from server
const props = defineProps({
    clients: {
        type: Object,
        required: true,
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
});

// Computed properties
const clients = computed(() => props.clients.data || []);

// Table columns
const columns = [
    { key: "name", label: "Client", sortable: true },
    { key: "email", label: "Email", sortable: true },
    { key: "phone", label: "Phone", sortable: true },
    { key: "company", label: "Company", sortable: true },
    { key: "sales_count", label: "Sales", type: "number", sortable: true },
    {
        key: "content_posts_count",
        label: "Content Posts",
        type: "number",
        sortable: true,
    },
];

// Methods
const createClient = () => {
    router.visit("/clients/create");
};

const editClient = (client) => {
    router.visit(`/clients/${client.id}/edit`);
};

const viewClient = (client) => {
    router.visit(`/clients/${client.id}`);
};

const deleteClient = async (client) => {
    if (
        confirm(
            "Are you sure you want to delete this client? This action cannot be undone."
        )
    ) {
        try {
            loading.value = true;
            await router.delete(`/clients/${client.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    // The page will automatically refresh with new data
                },
                onError: (errors) => {
                    console.error("Error deleting client:", errors);
                    if (errors.message?.includes("403")) {
                        alert(
                            "You don't have permission to delete this client."
                        );
                    }
                },
                onFinish: () => {
                    loading.value = false;
                },
            });
        } catch (error) {
            loading.value = false;
            console.error("Error deleting client:", error);
        }
    }
};

const applyFilters = () => {
    loading.value = true;
    const params = {};
    Object.keys(filters.value).forEach((key) => {
        if (filters.value[key]) params[key] = filters.value[key];
    });

    router.get("/clients", params, {
        preserveState: true,
        preserveScroll: true,
        only: ["clients", "filters"],
        onError: (errors) => {
            console.error("Error applying filters:", errors);
            if (errors.message?.includes("403")) {
                alert("You don't have permission to view clients.");
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
    };
    applyFilters();
};

const handlePageChange = (url) => {
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        only: ["clients", "filters"],
    });
};

// Utility functions
const getInitials = (firstName, lastName) => {
    return `${firstName?.charAt(0) || ""}${
        lastName?.charAt(0) || ""
    }`.toUpperCase();
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
