<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Edit Client: {{ client.first_name }}
                        {{ client.last_name }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Update the client's personal and business information
                    </p>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
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
                        Back to Clients
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Form Container -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
            >
                <!-- Form Header -->
                <div
                    class="bg-gradient-to-r from-blue-500 to-cyan-600 px-6 py-4"
                >
                    <h2 class="text-xl font-semibold text-white">
                        Client Information
                    </h2>
                    <p class="text-blue-100 text-sm mt-1">
                        Update the client's personal and business details
                    </p>
                </div>

                <div class="p-6 text-gray-900">
                    <form @submit.prevent="submitForm" class="space-y-8">
                        <!-- Personal Information Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-blue-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Personal Information
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Enter the client's personal details
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- First Name -->
                                <div>
                                    <label
                                        for="first_name"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >First Name *</label
                                    >
                                    <input
                                        type="text"
                                        id="first_name"
                                        v-model="form.first_name"
                                        required
                                        class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 transition-colors"
                                    />
                                    <p
                                        v-if="errors.first_name"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ errors.first_name }}
                                    </p>
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <label
                                        for="last_name"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Last Name *</label
                                    >
                                    <input
                                        type="text"
                                        id="last_name"
                                        v-model="form.last_name"
                                        required
                                        class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 transition-colors"
                                    />
                                    <p
                                        v-if="errors.last_name"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ errors.last_name }}
                                    </p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label
                                        for="email"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Email *</label
                                    >
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
                                                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
                                                />
                                            </svg>
                                        </div>
                                        <input
                                            type="email"
                                            id="email"
                                            v-model="form.email"
                                            required
                                            class="mt-1 block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                        />
                                    </div>
                                    <p
                                        v-if="errors.email"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ errors.email }}
                                    </p>
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label
                                        for="phone"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Phone *</label
                                    >
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
                                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                                />
                                            </svg>
                                        </div>
                                        <input
                                            type="tel"
                                            id="phone"
                                            v-model="form.phone"
                                            required
                                            class="mt-1 block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                        />
                                    </div>
                                    <p
                                        v-if="errors.phone"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ errors.phone }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Business Information Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-blue-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Business Information
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Enter company and business details
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <!-- Company -->
                                <div>
                                    <label
                                        for="company"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Company *</label
                                    >
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
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                                />
                                            </svg>
                                        </div>
                                        <input
                                            type="text"
                                            id="company"
                                            v-model="form.company"
                                            required
                                            class="mt-1 block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                        />
                                    </div>
                                    <p
                                        v-if="errors.company"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ errors.company }}
                                    </p>
                                </div>

                                <!-- Category -->
                                <div>
                                    <label
                                        for="category"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Category</label
                                    >
                                    <select
                                        id="category"
                                        v-model="form.category"
                                        class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 transition-colors"
                                    >
                                        <option value="">
                                            Select a category
                                        </option>
                                        <option value="Consignment Partner">
                                            Consignment Partner
                                        </option>
                                        <option value="Direct Buyer">
                                            Direct Buyer
                                        </option>
                                        <option value="Wholesale Client">
                                            Wholesale Client
                                        </option>
                                        <option value="Retail Customer">
                                            Retail Customer
                                        </option>
                                        <option value="Corporate Account">
                                            Corporate Account
                                        </option>
                                        <option value="Auction House">
                                            Auction House
                                        </option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <p
                                        v-if="errors.category"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ errors.category }}
                                    </p>
                                </div>

                                <!-- Address -->
                                <div>
                                    <label
                                        for="address"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Address *</label
                                    >
                                    <div class="relative">
                                        <div
                                            class="absolute top-3 left-3 flex items-start pointer-events-none"
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
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                            </svg>
                                        </div>
                                        <textarea
                                            id="address"
                                            v-model="form.address"
                                            rows="3"
                                            required
                                            class="mt-1 block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                                        ></textarea>
                                    </div>
                                    <p
                                        v-if="errors.address"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ errors.address }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Client Image Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-blue-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Client Image
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Upload a profile photo for the client
                                    </p>
                                </div>
                            </div>

                            <div
                                class="bg-white rounded-lg border border-gray-200 p-6"
                            >
                                <ImageUploader
                                    v-model="form.image"
                                    label="Upload client photo"
                                    description="Click or drag image here to upload"
                                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                    :max-size="5 * 1024 * 1024"
                                    :max-width="2000"
                                    :max-height="2000"
                                    :min-width="100"
                                    :min-height="100"
                                    :error="errors.image"
                                    :preview-url="client.profile_image_url"
                                />
                                <p class="mt-2 text-xs text-gray-500">
                                    Supported formats: JPEG, PNG, GIF, WebP.
                                    Maximum size: 5MB. Recommended dimensions:
                                    400x400px.
                                </p>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div
                            class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200"
                        >
                            <button
                                type="button"
                                @click="goBack"
                                class="inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
                                :disabled="loading"
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
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="loading"
                                class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50"
                            >
                                <svg
                                    v-if="!loading"
                                    class="w-4 h-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                <svg
                                    v-if="loading"
                                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                {{ loading ? "Updating..." : "Update Client" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ImageUploader from "@/Components/Forms/ImageUploader.vue";

const props = defineProps({
    client: {
        type: Object,
        required: true,
    },
});

const loading = ref(false);
const errors = ref({});

const form = useForm({
    first_name: props.client.first_name || "",
    last_name: props.client.last_name || "",
    email: props.client.email || "",
    phone: props.client.phone || "",
    company: props.client.company || "",
    address: props.client.address || "",
    category: props.client.category || "",
    image: null,
});

const submitForm = () => {
    loading.value = true;
    errors.value = {};

    form.put(route("clients.web.update", props.client.id), {
        onSuccess: () => {
            router.visit(route("clients.web.index"));
        },
        onError: (error) => {
            errors.value = error;
        },
        onFinish: () => {
            loading.value = false;
        },
    });
};

const goBack = () => {
    router.visit(route("clients.web.index"));
};

// Initialize form with client data
onMounted(() => {
    form.first_name = props.client.first_name || "";
    form.last_name = props.client.last_name || "";
    form.email = props.client.email || "";
    form.phone = props.client.phone || "";
    form.company = props.client.company || "";
    form.address = props.client.address || "";
    form.category = props.client.category || "";
});
</script>
