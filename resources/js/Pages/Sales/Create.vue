<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Sale
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Client -->
                            <div>
                                <label
                                    for="client_id"
                                    class="block text-sm font-medium text-gray-700"
                                    >Client *</label
                                >
                                <Autocomplete
                                    id="client_id"
                                    v-model="form.client_id"
                                    placeholder="Search for a client by name or email..."
                                    class="mt-1"
                                    @select="handleClientSelect"
                                    @create="handleClientCreate"
                                />
                                <p
                                    v-if="errors.client_id"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.client_id }}
                                </p>
                            </div>

                            <!-- Product Name -->
                            <div>
                                <label
                                    for="product_name"
                                    class="block text-sm font-medium text-gray-700"
                                    >Product Name *</label
                                >
                                <input
                                    type="text"
                                    id="product_name"
                                    v-model="form.product_name"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="errors.product_name"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.product_name }}
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label
                                    for="description"
                                    class="block text-sm font-medium text-gray-700"
                                    >Description</label
                                >
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <p
                                    v-if="errors.description"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.description }}
                                </p>
                            </div>

                            <!-- Amount -->
                            <div>
                                <label
                                    for="amount"
                                    class="block text-sm font-medium text-gray-700"
                                    >Amount *</label
                                >
                                <input
                                    type="number"
                                    id="amount"
                                    v-model="form.amount"
                                    required
                                    min="0"
                                    step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="errors.amount"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.amount }}
                                </p>
                            </div>

                            <!-- Sale Date -->
                            <div>
                                <label
                                    for="sale_date"
                                    class="block text-sm font-medium text-gray-700"
                                    >Sale Date *</label
                                >
                                <input
                                    type="date"
                                    id="sale_date"
                                    v-model="form.sale_date"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="errors.sale_date"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.sale_date }}
                                </p>
                            </div>

                            <!-- Status -->
                            <div>
                                <label
                                    for="status"
                                    class="block text-sm font-medium text-gray-700"
                                    >Status *</label
                                >
                                <select
                                    id="status"
                                    v-model="form.status"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <p
                                    v-if="errors.status"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.status }}
                                </p>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label
                                    for="payment_method"
                                    class="block text-sm font-medium text-gray-700"
                                    >Payment Method *</label
                                >
                                <select
                                    id="payment_method"
                                    v-model="form.payment_method"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="online">Online</option>
                                </select>
                                <p
                                    v-if="errors.payment_method"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.payment_method }}
                                </p>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label
                                    for="notes"
                                    class="block text-sm font-medium text-gray-700"
                                    >Notes</label
                                >
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <p
                                    v-if="errors.notes"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.notes }}
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
                                        loading ? "Creating..." : "Create Sale"
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
import Autocomplete from "@/Components/UI/Autocomplete.vue";

const props = defineProps({
    clients: {
        type: Array,
        required: true,
    },
});

const loading = ref(false);
const errors = ref({});

const form = useForm({
    client_id: "",
    product_name: "",
    description: "",
    amount: 0,
    sale_date: new Date().toISOString().split("T")[0],
    status: "pending",
    payment_method: "cash",
    notes: "",
});

const submitForm = () => {
    loading.value = true;
    errors.value = {};

    form.post(route("sales.web.store"), {
        onSuccess: () => {
            router.visit(route("sales.web.index"));
        },
        onError: (error) => {
            errors.value = error;
        },
        onFinish: () => {
            loading.value = false;
        },
    });
};

const handleClientSelect = (client) => {
    console.log("Client selected:", client);
    // The client_id is already set by v-model
};

const handleClientCreate = (client) => {
    console.log("Client created:", client);
    // The client_id is already set by v-model
};

const goBack = () => {
    router.visit(route("sales.web.index"));
};
</script>
