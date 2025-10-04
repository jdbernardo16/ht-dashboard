<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Edit Expense
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Update the expense details and receipt information
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
                        Update the expense details and upload receipts
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
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Category *
                                    </label>
                                    <select
                                        id="category"
                                        v-model="form.category"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 px-3 py-2 transition-colors"
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
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.category }}
                                    </p>
                                </div>

                                <!-- Title -->
                                <div>
                                    <label
                                        for="title"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Title *
                                    </label>
                                    <input
                                        type="text"
                                        id="title"
                                        v-model="form.title"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 px-3 py-2 transition-colors"
                                    />
                                    <p
                                        v-if="form.errors.title"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.title }}
                                    </p>
                                </div>

                                <!-- Amount -->
                                <div>
                                    <label
                                        for="amount"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Amount *
                                    </label>
                                    <div class="relative">
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
                                            class="mt-1 block w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 transition-colors"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.amount"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.amount }}
                                    </p>
                                </div>

                                <!-- Expense Date -->
                                <div>
                                    <label
                                        for="expense_date"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Expense Date *
                                    </label>
                                    <input
                                        type="date"
                                        id="expense_date"
                                        v-model="form.expense_date"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 px-3 py-2 transition-colors"
                                    />
                                    <p
                                        v-if="form.errors.expense_date"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.expense_date }}
                                    </p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mt-6">
                                <label
                                    for="description"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Description *
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    required
                                    rows="4"
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 px-3 py-2 transition-colors"
                                    placeholder="Describe what this expense was for..."
                                ></textarea>
                                <p
                                    v-if="form.errors.description"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Payment Details Section -->
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
                                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Payment Details
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Configure payment method and status
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Status -->
                                <div>
                                    <label
                                        for="status"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Status *
                                    </label>
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 px-3 py-2 transition-colors"
                                    >
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid</option>
                                        <option value="cancelled">
                                            Cancelled
                                        </option>
                                    </select>
                                    <p
                                        v-if="form.errors.status"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.status }}
                                    </p>
                                </div>

                                <!-- Payment Method -->
                                <div>
                                    <label
                                        for="payment_method"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Payment Method *
                                    </label>
                                    <select
                                        id="payment_method"
                                        v-model="form.payment_method"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 px-3 py-2 transition-colors"
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
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.payment_method }}
                                    </p>
                                </div>

                                <!-- Merchant -->
                                <div>
                                    <label
                                        for="merchant"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Merchant
                                    </label>
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
                                            id="merchant"
                                            v-model="form.merchant"
                                            class="mt-1 block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 transition-colors"
                                            placeholder="Where was this purchased?"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.merchant"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.merchant }}
                                    </p>
                                </div>

                                <!-- Receipt Number -->
                                <div>
                                    <label
                                        for="receipt_number"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Receipt Number
                                    </label>
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
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                />
                                            </svg>
                                        </div>
                                        <input
                                            type="text"
                                            id="receipt_number"
                                            v-model="form.receipt_number"
                                            class="mt-1 block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 transition-colors"
                                            placeholder="Receipt or invoice number"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.receipt_number"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.receipt_number }}
                                    </p>
                                </div>
                            </div>

                            <!-- Tax Amount -->
                            <div class="mt-6">
                                <label
                                    for="tax_amount"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Tax Amount
                                </label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                    >
                                        <span class="text-gray-500 sm:text-sm"
                                            >$</span
                                        >
                                    </div>
                                    <input
                                        type="number"
                                        id="tax_amount"
                                        v-model="form.tax_amount"
                                        min="0"
                                        step="0.01"
                                        class="mt-1 block w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 transition-colors"
                                        placeholder="0.00"
                                    />
                                </div>
                                <p
                                    v-if="form.errors.tax_amount"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ form.errors.tax_amount }}
                                </p>
                            </div>
                        </div>

                        <!-- Receipts Section -->
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
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Receipts & Documentation
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Manage expense receipts and supporting
                                        documents
                                    </p>
                                </div>
                            </div>

                            <!-- Existing Receipts -->
                            <div
                                v-if="
                                    props.expense.media &&
                                    props.expense.media.length > 0
                                "
                                class="mb-6"
                            >
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-3"
                                >
                                    Existing Receipts
                                </label>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                                >
                                    <div
                                        v-for="media in props.expense.media"
                                        :key="media.id"
                                        class="bg-white rounded-lg border border-gray-200 p-4 hover:border-red-300 transition-colors"
                                    >
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <img
                                                    v-if="
                                                        media.mime_type.startsWith(
                                                            'image/'
                                                        )
                                                    "
                                                    :src="media.url"
                                                    :alt="media.original_name"
                                                    class="h-16 w-16 object-cover rounded-lg border border-gray-200"
                                                />
                                                <div
                                                    v-else
                                                    class="h-16 w-16 bg-red-50 rounded-lg border border-red-200 flex items-center justify-center"
                                                >
                                                    <svg
                                                        class="w-8 h-8 text-red-600"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p
                                                    class="text-sm font-medium text-gray-900 truncate"
                                                >
                                                    {{ media.original_name }}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-500"
                                                >
                                                    {{ media.formatted_size }}
                                                </p>
                                                <div
                                                    class="mt-2 flex space-x-2"
                                                >
                                                    <a
                                                        :href="media.url"
                                                        target="_blank"
                                                        class="text-red-600 hover:text-red-800 text-xs font-medium inline-flex items-center"
                                                    >
                                                        <svg
                                                            class="w-3 h-3 mr-1"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                                            />
                                                        </svg>
                                                        View
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Receipt Upload -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-3"
                                >
                                    Upload Additional Receipts
                                </label>
                                <div
                                    class="bg-white rounded-lg border border-gray-200 p-4"
                                >
                                    <ImageUploader
                                        v-model="form.receipts"
                                        :multiple="true"
                                        accept="image/*,.pdf"
                                        :max-size="10 * 1024 * 1024"
                                        label="Upload additional receipts"
                                        description="Drag & drop receipt images or PDFs here, or click to browse"
                                        :error="form.errors.receipts"
                                    />
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    Supported formats: JPG, PNG, WebP, PDF (Max
                                    10MB each). You can upload multiple files.
                                </p>
                            </div>
                        </div>

                        <!-- Additional Information Section -->
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
                                        Additional Information
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Add any extra notes or details about
                                        this expense
                                    </p>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label
                                    for="notes"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Notes
                                </label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 px-3 py-2 transition-colors"
                                    placeholder="Add any additional notes or details about this expense..."
                                ></textarea>
                                <p
                                    v-if="form.errors.notes"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ form.errors.notes }}
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
                                :disabled="form.processing"
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
                                :disabled="form.processing"
                                class="inline-flex items-center justify-center px-6 py-3 bg-red-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50"
                            >
                                <svg
                                    v-if="!form.processing"
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
                                    v-if="form.processing"
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
                                {{
                                    form.processing
                                        ? "Updating..."
                                        : "Update Expense"
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

const props = defineProps({
    expense: {
        type: Object,
        required: true,
    },
});

// Helper function to convert ISO datetime to date input format (YYYY-MM-DD)
const formatDateForInput = (isoDate) => {
    if (!isoDate) return "";
    const date = new Date(isoDate);
    return date.toISOString().split("T")[0];
};

const form = useForm({
    title: props.expense.title || "",
    description: props.expense.description || "",
    amount: props.expense.amount || 0,
    expense_date: formatDateForInput(props.expense.expense_date),
    category: props.expense.category || "",
    status: props.expense.status || "pending",
    payment_method: props.expense.payment_method || "cash",
    merchant: props.expense.merchant || "",
    receipt_number: props.expense.receipt_number || "",
    tax_amount: props.expense.tax_amount || 0,
    notes: props.expense.notes || "",
    receipts: [],
});

const submitForm = () => {
    form.put(route("expenses.web.update", props.expense.id), {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};

const goBack = () => {
    window.history.back();
};
</script>
