<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Create New Expense
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Record and track business expenses
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
                        Back to Expenses
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
                    class="bg-gradient-to-r from-red-500 to-orange-600 px-6 py-4"
                >
                    <h2 class="text-xl font-semibold text-white">
                        Expense Information
                    </h2>
                    <p class="text-red-100 text-sm mt-1">
                        Fill in the expense details and upload receipts
                    </p>
                </div>

                <div class="p-6 text-gray-900">
                    <form @submit.prevent="submitForm" class="space-y-8">
                        <!-- Basic Information Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-red-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-red-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Basic Information
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Enter the core expense details
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Category -->
                                <div>
                                    <label
                                        for="category"
                                        class="block text-sm font-medium text-gray-700"
                                        >Category *</label
                                    >
                                    <select
                                        id="category"
                                        v-model="form.category"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    >
                                        <option value="">
                                            Select a category
                                        </option>
                                        <option value="Labor">Labor</option>
                                        <option value="Software">
                                            Software
                                        </option>
                                        <option value="Table">Table</option>
                                        <option value="Advertising">
                                            Advertising
                                        </option>
                                        <option value="Office Supplies">
                                            Office Supplies
                                        </option>
                                        <option value="Travel">Travel</option>
                                        <option value="Utilities">
                                            Utilities
                                        </option>
                                        <option value="Marketing">
                                            Marketing
                                        </option>
                                        <option value="Inventory">
                                            Inventory
                                        </option>
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
                                        >Title *</label
                                    >
                                    <input
                                        type="text"
                                        id="title"
                                        v-model="form.title"
                                        required
                                        placeholder="Enter expense title..."
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="form.errors.title"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ form.errors.title }}
                                    </p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mt-6">
                                <label
                                    for="description"
                                    class="block text-sm font-medium text-gray-700"
                                    >Description *</label
                                >
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    required
                                    rows="4"
                                    placeholder="Describe the expense..."
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                ></textarea>
                                <p
                                    v-if="form.errors.description"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Financial Details Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-green-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-green-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Financial Details
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Set amount and payment information
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Amount -->
                                <div>
                                    <label
                                        for="amount"
                                        class="block text-sm font-medium text-gray-700"
                                        >Amount *</label
                                    >
                                    <div
                                        class="mt-1 relative rounded-md shadow-sm"
                                    >
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <span
                                                class="text-gray-500 sm:text-sm"
                                                >$</span
                                            >
                                        </div>
                                        <input
                                            type="number"
                                            id="amount"
                                            v-model="form.amount"
                                            required
                                            min="0"
                                            step="0.01"
                                            placeholder="0.00"
                                            class="mt-1 block w-full pl-7 pr-3 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.amount"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ form.errors.amount }}
                                    </p>
                                </div>

                                <!-- Tax Amount -->
                                <div>
                                    <label
                                        for="tax_amount"
                                        class="block text-sm font-medium text-gray-700"
                                        >Tax Amount</label
                                    >
                                    <div
                                        class="mt-1 relative rounded-md shadow-sm"
                                    >
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <span
                                                class="text-gray-500 sm:text-sm"
                                                >$</span
                                            >
                                        </div>
                                        <input
                                            type="number"
                                            id="tax_amount"
                                            v-model="form.tax_amount"
                                            min="0"
                                            step="0.01"
                                            placeholder="0.00"
                                            class="mt-1 block w-full pl-7 pr-3 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.tax_amount"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ form.errors.tax_amount }}
                                    </p>
                                </div>

                                <!-- Expense Date -->
                                <div>
                                    <label
                                        for="expense_date"
                                        class="block text-sm font-medium text-gray-700"
                                        >Expense Date *</label
                                    >
                                    <input
                                        type="date"
                                        id="expense_date"
                                        v-model="form.expense_date"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
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
                                        >Status *</label
                                    >
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    >
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid</option>
                                        <option value="cancelled">
                                            Cancelled
                                        </option>
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
                                        >Payment Method *</label
                                    >
                                    <select
                                        id="payment_method"
                                        v-model="form.payment_method"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
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
                            </div>
                        </div>

                        <!-- Additional Information Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-orange-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-orange-600"
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
                                        Additional Information
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Add merchant details and notes
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Merchant -->
                                <div>
                                    <label
                                        for="merchant"
                                        class="block text-sm font-medium text-gray-700"
                                        >Merchant</label
                                    >
                                    <input
                                        type="text"
                                        id="merchant"
                                        v-model="form.merchant"
                                        placeholder="Enter merchant name..."
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
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
                                        >Receipt Number</label
                                    >
                                    <input
                                        type="text"
                                        id="receipt_number"
                                        v-model="form.receipt_number"
                                        placeholder="Enter receipt number..."
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    />
                                    <p
                                        v-if="form.errors.receipt_number"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ form.errors.receipt_number }}
                                    </p>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mt-6">
                                <label
                                    for="notes"
                                    class="block text-sm font-medium text-gray-700"
                                    >Notes</label
                                >
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    placeholder="Add any additional notes..."
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                ></textarea>
                                <p
                                    v-if="form.errors.notes"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.notes }}
                                </p>
                            </div>
                        </div>

                        <!-- Receipt Upload Section -->
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
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Receipt Upload
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Upload receipt images or PDFs
                                    </p>
                                </div>
                            </div>

                            <div>
                                <ImageUploader
                                    v-model="form.receipts"
                                    :multiple="true"
                                    accept="image/*,.pdf"
                                    :max-size="10 * 1024 * 1024"
                                    label="Upload receipts"
                                    description="Drag & drop receipt images or PDFs here, or click to browse"
                                    :error="form.errors.receipts"
                                />
                                <p class="mt-2 text-xs text-gray-500">
                                    Supported formats: JPG, PNG, WebP, PDF (Max
                                    10MB each)
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
                                :disabled="form.processing"
                                class="px-6 py-3 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 transition-all duration-200"
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
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ImageUploader from "@/Components/Forms/ImageUploader.vue";

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
    receipts: [],
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
