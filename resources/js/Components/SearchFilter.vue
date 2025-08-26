<template>
    <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search Input -->
            <div>
                <label
                    for="search"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Search
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
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="search"
                        :value="modelValue.search"
                        @input="updateFilter('search', $event.target.value)"
                        placeholder="Search..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    />
                </div>
            </div>

            <!-- Status Filter -->
            <div v-if="filters.includes('status')">
                <label
                    for="status"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Status
                </label>
                <select
                    id="status"
                    :value="modelValue.status"
                    @change="updateFilter('status', $event.target.value)"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">All Status</option>
                    <option
                        v-for="status in statusOptions"
                        :key="status.value"
                        :value="status.value"
                    >
                        {{ status.label }}
                    </option>
                </select>
            </div>

            <!-- Date Range -->
            <div v-if="filters.includes('date')">
                <label
                    for="date_from"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Date From
                </label>
                <input
                    type="date"
                    id="date_from"
                    :value="modelValue.date_from"
                    @change="updateFilter('date_from', $event.target.value)"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                />
            </div>

            <div v-if="filters.includes('date')">
                <label
                    for="date_to"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Date To
                </label>
                <input
                    type="date"
                    id="date_to"
                    :value="modelValue.date_to"
                    @change="updateFilter('date_to', $event.target.value)"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                />
            </div>

            <!-- Priority Filter -->
            <div v-if="filters.includes('priority')">
                <label
                    for="priority"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Priority
                </label>
                <select
                    id="priority"
                    :value="modelValue.priority"
                    @change="updateFilter('priority', $event.target.value)"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">All Priorities</option>
                    <option
                        v-for="priority in priorityOptions"
                        :key="priority.value"
                        :value="priority.value"
                    >
                        {{ priority.label }}
                    </option>
                </select>
            </div>

            <!-- Type Filter -->
            <div v-if="filters.includes('type')">
                <label
                    for="type"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Type
                </label>
                <select
                    id="type"
                    :value="modelValue.type"
                    @change="updateFilter('type', $event.target.value)"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">All Types</option>
                    <option
                        v-for="type in typeOptions"
                        :key="type.value"
                        :value="type.value"
                    >
                        {{ type.label }}
                    </option>
                </select>
            </div>

            <!-- Category Filter -->
            <div v-if="filters.includes('category')">
                <label
                    for="category"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Category
                </label>
                <select
                    id="category"
                    :value="modelValue.category"
                    @change="updateFilter('category', $event.target.value)"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">All Categories</option>
                    <option
                        v-for="category in categoryOptions"
                        :key="category.value"
                        :value="category.value"
                    >
                        {{ category.label }}
                    </option>
                </select>
            </div>

            <!-- Amount Range -->
            <div v-if="filters.includes('amount')">
                <label
                    for="min_amount"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Min Amount
                </label>
                <input
                    type="number"
                    id="min_amount"
                    :value="modelValue.min_amount"
                    @input="updateFilter('min_amount', $event.target.value)"
                    placeholder="0"
                    min="0"
                    step="0.01"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                />
            </div>

            <div v-if="filters.includes('amount')">
                <label
                    for="max_amount"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Max Amount
                </label>
                <input
                    type="number"
                    id="max_amount"
                    :value="modelValue.max_amount"
                    @input="updateFilter('max_amount', $event.target.value)"
                    placeholder="999999"
                    min="0"
                    step="0.01"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                />
            </div>

            <!-- Assigned To Filter -->
            <div v-if="filters.includes('assigned_to')">
                <label
                    for="assigned_to"
                    class="block text-sm font-medium text-gray-700 mb-1"
                >
                    Assigned To
                </label>
                <select
                    id="assigned_to"
                    :value="modelValue.assigned_to"
                    @change="updateFilter('assigned_to', $event.target.value)"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">All Users</option>
                    <option
                        v-for="user in userOptions"
                        :key="user.value"
                        :value="user.value"
                    >
                        {{ user.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4 flex justify-end space-x-3">
            <button
                @click="clearFilters"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                Clear Filters
            </button>
            <button
                @click="applyFilters"
                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                Apply Filters
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({
            search: "",
            status: "",
            date_from: "",
            date_to: "",
            priority: "",
            type: "",
            category: "",
            min_amount: "",
            max_amount: "",
            assigned_to: "",
        }),
    },
    filters: {
        type: Array,
        default: () => ["search", "status", "date"],
    },
    statusOptions: {
        type: Array,
        default: () => [
            { value: "pending", label: "Pending" },
            { value: "not_started", label: "Not Started" },
            { value: "in_progress", label: "In Progress" },
            { value: "completed", label: "Completed" },
            { value: "cancelled", label: "Cancelled" },
        ],
    },
    priorityOptions: {
        type: Array,
        default: () => [
            { value: "low", label: "Low" },
            { value: "medium", label: "Medium" },
            { value: "high", label: "High" },
            { value: "urgent", label: "Urgent" },
        ],
    },
    typeOptions: {
        type: Array,
        default: () => [],
    },
    categoryOptions: {
        type: Array,
        default: () => [],
    },
    userOptions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["update:modelValue", "apply", "clear"]);

const updateFilter = (key, value) => {
    emit("update:modelValue", {
        ...props.modelValue,
        [key]: value,
    });
};

const clearFilters = () => {
    const cleared = {};
    Object.keys(props.modelValue).forEach((key) => {
        cleared[key] = "";
    });
    emit("update:modelValue", cleared);
    emit("clear");
};

const applyFilters = () => {
    emit("apply", props.modelValue);
};
</script>
