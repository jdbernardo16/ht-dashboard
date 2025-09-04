<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Expense
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Category -->
                            <div>
                                <label
                                    for="category"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Category *
                                </label>
                                <select
                                    id="category"
                                    v-model="form.category"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select a category</option>
                                    <option value="Labor">Labor</option>
                                    <option value="Software">Software</option>
                                    <option value="Table">Table</option>
                                    <option value="Advertising">
                                        Advertising
                                    </option>
                                    <option value="Office Supplies">
                                        Office Supplies
                                    </option>
                                    <option value="Travel">Travel</option>
                                    <option value="Utilities">Utilities</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Inventory">Inventory</option>
                                </select>
                                <p
                                    v-if="form.errors.category"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.category }}
                                </p>
                            </div>

                            <!-- Title -->
                            <div>
                                <label
                                    for="title"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Title *
                                </label>
                                <input
                                    type="text"
                                    id="title"
                                    v-model="form.title"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.title"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label
                                    for="description"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Description *
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    required
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <p
                                    v-if="form.errors.description"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Amount -->
                            <div>
                                <label
                                    for="amount"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Amount *
                                </label>
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
                                    v-if="form.errors.amount"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.amount }}
                                </p>
                            </div>

                            <!-- Expense Date -->
                            <div>
                                <label
                                    for="expense_date"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Expense Date *
                                </label>
                                <input
                                    type="date"
                                    id="expense_date"
                                    v-model="form.expense_date"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.expense_date"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.expense_date }}
                                </p>
                            </div>

                            <!-- Status -->
                            <div>
                                <label
                                    for="status"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Status *
                                </label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <p
                                    v-if="form.errors.status"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.status }}
                                </p>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label
                                    for="payment_method"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Payment Method *
                                </label>
                                <select
                                    id="payment_method"
                                    v-model="form.payment_method"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="online">Online</option>
                                    <option value="bank_transfer">
                                        Bank Transfer
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.payment_method"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.payment_method }}
                                </p>
                            </div>

                            <!-- Merchant -->
                            <div>
                                <label
                                    for="merchant"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Merchant
                                </label>
                                <input
                                    type="text"
                                    id="merchant"
                                    v-model="form.merchant"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.merchant"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.merchant }}
                                </p>
                            </div>

                            <!-- Receipt Number -->
                            <div>
                                <label
                                    for="receipt_number"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Receipt Number
                                </label>
                                <input
                                    type="text"
                                    id="receipt_number"
                                    v-model="form.receipt_number"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.receipt_number"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.receipt_number }}
                                </p>
                            </div>

                            <!-- Tax Amount -->
                            <div>
                                <label
                                    for="tax_amount"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Tax Amount
                                </label>
                                <input
                                    type="number"
                                    id="tax_amount"
                                    v-model="form.tax_amount"
                                    min="0"
                                    step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.tax_amount"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.tax_amount }}
                                </p>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label
                                    for="notes"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Notes
                                </label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <p
                                    v-if="form.errors.notes"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.notes }}
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
                                    :disabled="form.processing"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    {{
                                        form.processing
                                            ? "Creating..."
                                            : "Create Expense"
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
import { useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const form = useForm({
    title: "",
    description: "",
    amount: 0,
    expense_date: new Date().toISOString().split("T")[0],
    category: "",
    status: "pending",
    payment_method: "cash",
    merchant: "",
    receipt_number: "",
    tax_amount: 0,
    notes: "",
});

const submitForm = () => {
    form.post(route("expenses.web.store"), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const goBack = () => {
    window.history.back();
};
</script>
