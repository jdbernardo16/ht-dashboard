<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Client Details: {{ client.first_name }} {{ client.last_name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Client Information Card -->
                    <div class="lg:col-span-1">
                        <div
                            class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <div class="flex items-center space-x-4 mb-6">
                                    <div class="flex-shrink-0 h-16 w-16">
                                        <div
                                            class="h-16 w-16 rounded-full bg-indigo-600 flex items-center justify-center"
                                        >
                                            <span
                                                class="text-white font-medium text-lg"
                                            >
                                                {{
                                                    getInitials(
                                                        client.first_name,
                                                        client.last_name
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <h3
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            {{ client.first_name }}
                                            {{ client.last_name }}
                                        </h3>
                                        <p
                                            v-if="client.company"
                                            class="text-sm text-gray-500"
                                        >
                                            {{ client.company }}
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div v-if="client.email">
                                        <h4
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Email
                                        </h4>
                                        <a
                                            :href="`mailto:${client.email}`"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            {{ client.email }}
                                        </a>
                                    </div>

                                    <div v-if="client.phone">
                                        <h4
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Phone
                                        </h4>
                                        <p class="text-gray-900">
                                            {{ client.phone }}
                                        </p>
                                    </div>

                                    <div v-if="client.address">
                                        <h4
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Address
                                        </h4>
                                        <p
                                            class="text-gray-900 whitespace-pre-line"
                                        >
                                            {{ client.address }}
                                        </p>
                                    </div>

                                    <div>
                                        <h4
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Total Sales
                                        </h4>
                                        <p class="text-blue-600 font-medium">
                                            {{ client.sales?.length || 0 }}
                                        </p>
                                    </div>

                                    <div>
                                        <h4
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Content Posts
                                        </h4>
                                        <p class="text-green-600 font-medium">
                                            {{
                                                client.content_posts?.length ||
                                                0
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-6 flex space-x-3">
                                    <button
                                        @click="editClient"
                                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Edit Client
                                    </button>
                                    <button
                                        @click="deleteClient"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales and Content Posts -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Recent Sales -->
                        <div
                            class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Recent Sales
                                </h3>

                                <div
                                    v-if="
                                        client.sales && client.sales.length > 0
                                    "
                                >
                                    <div
                                        v-for="sale in client.sales.slice(0, 5)"
                                        :key="sale.id"
                                        class="border-b border-gray-100 py-3 last:border-b-0"
                                    >
                                        <div
                                            class="flex justify-between items-center"
                                        >
                                            <div>
                                                <h4
                                                    class="font-medium text-gray-900"
                                                >
                                                    {{ sale.product_name }}
                                                </h4>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{
                                                        formatDate(
                                                            sale.sale_date
                                                        )
                                                    }}
                                                    •
                                                    <span
                                                        :class="
                                                            getStatusClass(
                                                                sale.status
                                                            )
                                                        "
                                                        class="px-2 py-1 text-xs rounded-full"
                                                    >
                                                        {{
                                                            formatStatus(
                                                                sale.status
                                                            )
                                                        }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p
                                                    class="font-medium text-green-600"
                                                >
                                                    ${{
                                                        formatCurrency(
                                                            sale.amount
                                                        )
                                                    }}
                                                </p>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{ sale.payment_method }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 text-center">
                                        <button
                                            @click="viewAllSales"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                        >
                                            View all sales →
                                        </button>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="text-center py-8 text-gray-500"
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
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                        />
                                    </svg>
                                    <p class="mt-2">
                                        No sales recorded for this client
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Content Posts -->
                        <div
                            class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Recent Content Posts
                                </h3>

                                <div
                                    v-if="
                                        client.content_posts &&
                                        client.content_posts.length > 0
                                    "
                                >
                                    <div
                                        v-for="post in client.content_posts.slice(
                                            0,
                                            5
                                        )"
                                        :key="post.id"
                                        class="border-b border-gray-100 py-3 last:border-b-0"
                                    >
                                        <div>
                                            <h4
                                                class="font-medium text-gray-900"
                                            >
                                                {{ post.title }}
                                            </h4>
                                            <p class="text-sm text-gray-500">
                                                {{
                                                    formatDate(post.created_at)
                                                }}
                                                •
                                                <span
                                                    :class="
                                                        getPostStatusClass(
                                                            post.status
                                                        )
                                                    "
                                                    class="px-2 py-1 text-xs rounded-full"
                                                >
                                                    {{
                                                        formatStatus(
                                                            post.status
                                                        )
                                                    }}
                                                </span>
                                            </p>
                                            <p
                                                class="text-sm text-gray-600 mt-1 line-clamp-2"
                                            >
                                                {{ post.description }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-4 text-center">
                                        <button
                                            @click="viewAllContent"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                        >
                                            View all content posts →
                                        </button>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="text-center py-8 text-gray-500"
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
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                    <p class="mt-2">
                                        No content posts for this client
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    client: {
        type: Object,
        required: true,
    },
});

const loading = ref(false);

const editClient = () => {
    router.visit(`/clients/${props.client.id}/edit`);
};

const deleteClient = async () => {
    if (
        confirm(
            "Are you sure you want to delete this client? This action cannot be undone."
        )
    ) {
        try {
            loading.value = true;
            await router.delete(`/clients/${props.client.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    router.visit("/clients");
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

const viewAllSales = () => {
    router.visit("/sales", {
        data: { client_id: props.client.id },
    });
};

const viewAllContent = () => {
    router.visit("/content", {
        data: { client_id: props.client.id },
    });
};

// Utility functions
const getInitials = (firstName, lastName) => {
    return `${firstName?.charAt(0) || ""}${
        lastName?.charAt(0) || ""
    }`.toUpperCase();
};

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
        draft: "bg-gray-100 text-gray-800",
        published: "bg-blue-100 text-blue-800",
        scheduled: "bg-purple-100 text-purple-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getPostStatusClass = (status) => {
    const classes = {
        draft: "bg-gray-100 text-gray-800",
        published: "bg-green-100 text-green-800",
        scheduled: "bg-blue-100 text-blue-800",
        archived: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
