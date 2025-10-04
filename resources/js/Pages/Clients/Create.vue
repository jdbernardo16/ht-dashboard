<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Create New Client
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Add a new client to your database
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
                        Fill in the client's personal and business details
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
                                        class="block text-sm font-medium text-gray-700"
                                        >First Name *</label
                                    >
                                    <input
                                        type="text"
                                        id="first_name"
                                        v-model="form.first_name"
                                        required
                                        placeholder="Enter first name..."
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="errors.first_name"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.first_name }}
                                    </p>
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <label
                                        for="last_name"
                                        class="block text-sm font-medium text-gray-700"
                                        >Last Name *</label
                                    >
                                    <input
                                        type="text"
                                        id="last_name"
                                        v-model="form.last_name"
                                        required
                                        placeholder="Enter last name..."
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="errors.last_name"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.last_name }}
                                    </p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label
                                        for="email"
                                        class="block text-sm font-medium text-gray-700"
                                        >Email *</label
                                    >
                                    <input
                                        type="email"
                                        id="email"
                                        v-model="form.email"
                                        required
                                        placeholder="client@example.com"
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="errors.email"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.email }}
                                    </p>
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label
                                        for="phone"
                                        class="block text-sm font-medium text-gray-700"
                                        >Phone *</label
                                    >
                                    <input
                                        type="tel"
                                        id="phone"
                                        v-model="form.phone"
                                        required
                                        placeholder="(555) 123-4567"
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="errors.phone"
                                        class="mt-1 text-sm text-red-600"
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
                                        class="inline-flex items-center justify-center w-10 h-10 bg-cyan-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-cyan-600"
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
                                        Enter company and category details
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Company -->
                                <div>
                                    <label
                                        for="company"
                                        class="block text-sm font-medium text-gray-700"
                                        >Company *</label
                                    >
                                    <input
                                        type="text"
                                        id="company"
                                        v-model="form.company"
                                        required
                                        placeholder="Enter company name..."
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="errors.company"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.company }}
                                    </p>
                                </div>

                                <!-- Category -->
                                <div>
                                    <label
                                        for="category"
                                        class="block text-sm font-medium text-gray-700"
                                        >Category</label
                                    >
                                    <select
                                        id="category"
                                        v-model="form.category"
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
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
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.category }}
                                    </p>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="mt-6">
                                <label
                                    for="address"
                                    class="block text-sm font-medium text-gray-700"
                                    >Address *</label
                                >
                                <textarea
                                    id="address"
                                    v-model="form.address"
                                    rows="3"
                                    required
                                    placeholder="Enter complete address..."
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                ></textarea>
                                <p
                                    v-if="errors.address"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.address }}
                                </p>
                            </div>
                        </div>

                        <!-- Client Image Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-purple-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-purple-600"
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
                                        Client Photo
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Upload a profile image for the client
                                    </p>
                                </div>
                            </div>

                            <div>
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
                                />
                                <p class="mt-2 text-xs text-gray-500">
                                    Recommended: Square image, minimum
                                    100x100px, maximum 5MB
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
                                class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="loading"
                                class="px-6 py-3 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 transition-all duration-200"
                            >
                                {{ loading ? "Creating..." : "Create Client" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ImageUploader from "@/Components/Forms/ImageUploader.vue";

const loading = ref(false);
const errors = ref({});

const form = useForm({
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
    company: "",
    address: "",
    category: "",
    image: null,
});

const submitForm = () => {
    loading.value = true;
    errors.value = {};

    form.post(route("clients.web.store"), {
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
</script>
