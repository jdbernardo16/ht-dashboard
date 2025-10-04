<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div
                            v-if="client.profile_image_url"
                            class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden ring-4 ring-white shadow-lg"
                        >
                            <img
                                :src="client.profile_image_url"
                                :alt="`${client.first_name} ${client.last_name}`"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div
                            v-else
                            class="h-20 w-20 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center ring-4 ring-white shadow-lg"
                        >
                            <span class="text-white font-bold text-2xl">
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
                        <h1
                            class="text-2xl font-bold text-gray-900 leading-tight"
                        >
                            {{ client.first_name }} {{ client.last_name }}
                        </h1>
                        <p
                            v-if="client.company"
                            class="text-lg text-gray-600 mt-1"
                        >
                            {{ client.company }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            Client ID: #{{ client.id }} • Member since
                            {{ formatDate(client.created_at) }}
                        </p>
                    </div>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
                    <button
                        @click="editClient"
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
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                            />
                        </svg>
                        Edit Client
                    </button>
                    <button
                        @click="deleteClient"
                        class="inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm"
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
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                        Delete
                    </button>
                    <button
                        @click="goBack"
                        class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
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
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                        Back
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Client Statistics Bar -->
            <div
                class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-8 border border-blue-100"
            >
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div
                            class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-3"
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
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                />
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ client.sales?.length || 0 }}
                        </p>
                        <p class="text-sm text-gray-600">Total Sales</p>
                    </div>
                    <div class="text-center">
                        <div
                            class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-3"
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
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ client.content_posts?.length || 0 }}
                        </p>
                        <p class="text-sm text-gray-600">Content Posts</p>
                    </div>
                    <div class="text-center">
                        <div
                            class="inline-flex items-center justify-center w-12 h-12 bg-purple-100 rounded-full mb-3"
                        >
                            <svg
                                class="w-6 h-6 text-purple-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ client.category || "Uncategorized" }}
                        </p>
                        <p class="text-sm text-gray-600">Category</p>
                    </div>
                    <div class="text-center">
                        <div
                            class="inline-flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-full mb-3"
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
                                />
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ getDaysAsClient() }}
                        </p>
                        <p class="text-sm text-gray-600">Days as Client</p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Client Information Card -->
                <div class="lg:col-span-1">
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4"
                        >
                            <h2 class="text-xl font-semibold text-white">
                                Contact Information
                            </h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-6">
                                <div
                                    v-if="client.email"
                                    class="flex items-start space-x-3"
                                >
                                    <svg
                                        class="w-5 h-5 text-gray-400 mt-0.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                        />
                                    </svg>
                                    <div class="flex-1">
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Email
                                        </dt>
                                        <dd class="mt-1">
                                            <a
                                                :href="`mailto:${client.email}`"
                                                class="text-indigo-600 hover:text-indigo-800 font-medium"
                                            >
                                                {{ client.email }}
                                            </a>
                                        </dd>
                                    </div>
                                </div>

                                <div
                                    v-if="client.phone"
                                    class="flex items-start space-x-3"
                                >
                                    <svg
                                        class="w-5 h-5 text-gray-400 mt-0.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                        />
                                    </svg>
                                    <div class="flex-1">
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Phone
                                        </dt>
                                        <dd
                                            class="mt-1 text-gray-900 font-medium"
                                        >
                                            {{ client.phone }}
                                        </dd>
                                    </div>
                                </div>

                                <div
                                    v-if="client.address"
                                    class="flex items-start space-x-3"
                                >
                                    <svg
                                        class="w-5 h-5 text-gray-400 mt-0.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                    <div class="flex-1">
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Address
                                        </dt>
                                        <dd
                                            class="mt-1 text-gray-900 whitespace-pre-line bg-gray-50 p-3 rounded-lg"
                                        >
                                            {{ client.address }}
                                        </dd>
                                    </div>
                                </div>

                                <div
                                    v-if="client.category"
                                    class="flex items-start space-x-3"
                                >
                                    <svg
                                        class="w-5 h-5 text-gray-400 mt-0.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                        />
                                    </svg>
                                    <div class="flex-1">
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Category
                                        </dt>
                                        <dd class="mt-1">
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800"
                                            >
                                                {{ client.category }}
                                            </span>
                                        </dd>
                                    </div>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Sales and Content Posts -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Recent Sales -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-green-500 to-teal-600 px-6 py-4"
                        >
                            <h3 class="text-xl font-semibold text-white">
                                Recent Sales
                            </h3>
                        </div>
                        <div class="p-6">
                            <div
                                v-if="client.sales && client.sales.length > 0"
                                class="space-y-4"
                            >
                                <div
                                    v-for="sale in client.sales.slice(0, 5)"
                                    :key="sale.id"
                                    class="bg-gray-50 rounded-lg p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200"
                                >
                                    <div
                                        class="flex justify-between items-start"
                                    >
                                        <div class="flex-1">
                                            <h4
                                                class="font-semibold text-gray-900 text-lg"
                                            >
                                                {{ sale.product_name }}
                                            </h4>
                                            <div
                                                class="flex items-center mt-2 space-x-3"
                                            >
                                                <span
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{
                                                        formatDate(
                                                            sale.sale_date
                                                        )
                                                    }}
                                                </span>
                                                <span
                                                    :class="
                                                        getStatusClass(
                                                            sale.status
                                                        )
                                                    "
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                >
                                                    {{
                                                        formatStatus(
                                                            sale.status
                                                        )
                                                    }}
                                                </span>
                                                <span
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{
                                                        formatPaymentMethod(
                                                            sale.payment_method
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-right ml-4">
                                            <p
                                                class="text-xl font-bold text-green-600"
                                            >
                                                ${{
                                                    formatCurrency(sale.amount)
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-6 text-center">
                                    <button
                                        @click="viewAllSales"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200"
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
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                            />
                                        </svg>
                                        View All Sales
                                    </button>
                                </div>
                            </div>
                            <div v-else class="text-center py-12 text-gray-500">
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
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                                <p class="text-lg font-medium text-gray-900">
                                    No sales recorded
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    This client doesn't have any sales yet.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Content Posts -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4"
                        >
                            <h3 class="text-xl font-semibold text-white">
                                Recent Content Posts
                            </h3>
                        </div>
                        <div class="p-6">
                            <div
                                v-if="
                                    client.content_posts &&
                                    client.content_posts.length > 0
                                "
                                class="space-y-4"
                            >
                                <div
                                    v-for="post in client.content_posts.slice(
                                        0,
                                        5
                                    )"
                                    :key="post.id"
                                    class="bg-gray-50 rounded-lg p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200"
                                >
                                    <div>
                                        <h4
                                            class="font-semibold text-gray-900 text-lg"
                                        >
                                            {{ post.title }}
                                        </h4>
                                        <div
                                            class="flex items-center mt-2 space-x-3"
                                        >
                                            <span class="text-sm text-gray-500">
                                                {{
                                                    formatDate(post.created_at)
                                                }}
                                            </span>
                                            <span
                                                :class="
                                                    getPostStatusClass(
                                                        post.status
                                                    )
                                                "
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            >
                                                {{ formatStatus(post.status) }}
                                            </span>
                                        </div>
                                        <p
                                            class="text-sm text-gray-600 mt-2 line-clamp-2"
                                        >
                                            {{ post.description }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-6 text-center">
                                    <button
                                        @click="viewAllContent"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200"
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
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                            />
                                        </svg>
                                        View All Content
                                    </button>
                                </div>
                            </div>
                            <div v-else class="text-center py-12 text-gray-500">
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
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    />
                                </svg>
                                <p class="text-lg font-medium text-gray-900">
                                    No content posts
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    This client doesn't have any content posts
                                    yet.
                                </p>
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

const goBack = () => {
    router.visit("/clients");
};

const getDaysAsClient = () => {
    if (!props.client.created_at) return 0;
    const created = new Date(props.client.created_at);
    const today = new Date();
    const diffTime = Math.abs(today - created);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

const formatPaymentMethod = (method) => {
    const methods = {
        cash: "Cash",
        card: "Card",
        online: "Online",
        bank_transfer: "Bank Transfer",
    };
    return methods[method] || method;
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
