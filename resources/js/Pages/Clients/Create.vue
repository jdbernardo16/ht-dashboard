<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Client
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submitForm" class="space-y-6">
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    >Email</label
                                >
                                <input
                                    type="email"
                                    id="email"
                                    v-model="form.email"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    >Phone</label
                                >
                                <input
                                    type="tel"
                                    id="phone"
                                    v-model="form.phone"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="errors.phone"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.phone }}
                                </p>
                            </div>

                            <!-- Company -->
                            <div>
                                <label
                                    for="company"
                                    class="block text-sm font-medium text-gray-700"
                                    >Company</label
                                >
                                <input
                                    type="text"
                                    id="company"
                                    v-model="form.company"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="errors.company"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.company }}
                                </p>
                            </div>

                            <!-- Address -->
                            <div>
                                <label
                                    for="address"
                                    class="block text-sm font-medium text-gray-700"
                                    >Address</label
                                >
                                <textarea
                                    id="address"
                                    v-model="form.address"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <p
                                    v-if="errors.address"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.address }}
                                </p>
                            </div>

                            <!-- Submit Buttons -->
                            <div
                                class="flex items-center justify-end space-x-3"
                            >
                                <button
                                    type="button"
                                    @click="goBack"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="loading"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    {{
                                        loading
                                            ? "Creating..."
                                            : "Create Client"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const loading = ref(false);
const errors = ref({});

const form = useForm({
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
    company: "",
    address: "",
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
