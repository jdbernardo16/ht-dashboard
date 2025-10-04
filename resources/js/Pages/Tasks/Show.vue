<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ task.title }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Task ID: #{{ task.id }} • Created
                        {{ formatDate(task.created_at) }}
                    </p>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
                    <button
                        @click="editTask"
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
                        Edit Task
                    </button>
                    <button
                        @click="deleteTask"
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
            <!-- Status and Priority Bar -->
            <div
                class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-6 mb-8 border border-indigo-100"
            >
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-600"
                                >Status:</span
                            >
                            <span
                                :class="getStatusClass(task.status)"
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                            >
                                {{ formatStatus(task.status) }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-600"
                                >Priority:</span
                            >
                            <span
                                :class="getPriorityClass(task.priority)"
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                            >
                                {{ formatPriority(task.priority) }}
                            </span>
                        </div>
                        <div
                            v-if="task.due_date"
                            class="flex items-center space-x-2"
                        >
                            <svg
                                class="w-4 h-4 text-gray-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                            <span class="text-sm text-gray-600"
                                >Due: {{ formatDate(task.due_date) }}</span
                            >
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="text-sm text-gray-600">Progress:</div>
                        <div class="flex items-center space-x-2">
                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                <div
                                    class="bg-gradient-to-r from-indigo-500 to-purple-600 h-2 rounded-full transition-all duration-300"
                                    :style="`width: ${getProgressPercentage(
                                        task
                                    )}%`"
                                ></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900"
                                >{{ getProgressPercentage(task) }}%</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content Area -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Task Information Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4"
                        >
                            <h2 class="text-xl font-semibold text-white">
                                Task Information
                            </h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-6">
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Description
                                    </dt>
                                    <dd class="text-gray-900 leading-relaxed">
                                        {{
                                            task.description ||
                                            "No description provided"
                                        }}
                                    </dd>
                                </div>

                                <div v-if="task.notes">
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Notes
                                    </dt>
                                    <dd
                                        class="text-gray-900 leading-relaxed bg-gray-50 p-4 rounded-lg"
                                    >
                                        {{ task.notes }}
                                    </dd>
                                </div>

                                <div v-if="task.tags && task.tags.length > 0">
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-3"
                                    >
                                        Tags
                                    </dt>
                                    <dd class="flex flex-wrap gap-2">
                                        <span
                                            v-for="tag in formatTags(task.tags)"
                                            :key="tag"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 border border-indigo-200"
                                        >
                                            <svg
                                                class="w-3 h-3 mr-1"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            {{ tag }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Related Information -->
                    <div
                        v-if="task.related_goal || task.parent_task"
                        class="grid grid-cols-1 md:grid-cols-2 gap-6"
                    >
                        <div
                            v-if="task.related_goal"
                            class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                        >
                            <div
                                class="bg-gradient-to-r from-green-500 to-teal-600 px-6 py-4"
                            >
                                <h3 class="text-lg font-semibold text-white">
                                    Related Goal
                                </h3>
                            </div>
                            <div class="p-6">
                                <h4 class="font-semibold text-gray-900 mb-2">
                                    {{ task.related_goal.title }}
                                </h4>
                                <p class="text-sm text-gray-600 mb-4">
                                    {{
                                        task.related_goal.description ||
                                        "No description"
                                    }}
                                </p>
                                <div class="flex items-center justify-between">
                                    <span
                                        :class="
                                            getStatusClass(
                                                task.related_goal.status
                                            )
                                        "
                                        class="px-3 py-1 text-xs font-medium rounded-full"
                                    >
                                        {{
                                            formatStatus(
                                                task.related_goal.status
                                            )
                                        }}
                                    </span>
                                    <button
                                        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium"
                                    >
                                        View Goal →
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="task.parent_task"
                            class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                        >
                            <div
                                class="bg-gradient-to-r from-yellow-500 to-orange-600 px-6 py-4"
                            >
                                <h3 class="text-lg font-semibold text-white">
                                    Parent Task
                                </h3>
                            </div>
                            <div class="p-6">
                                <h4 class="font-semibold text-gray-900 mb-2">
                                    {{ task.parent_task.title }}
                                </h4>
                                <p class="text-sm text-gray-600 mb-4">
                                    {{
                                        task.parent_task.description ||
                                        "No description"
                                    }}
                                </p>
                                <div class="flex items-center justify-between">
                                    <span
                                        :class="
                                            getStatusClass(
                                                task.parent_task.status
                                            )
                                        "
                                        class="px-3 py-1 text-xs font-medium rounded-full"
                                    >
                                        {{
                                            formatStatus(
                                                task.parent_task.status
                                            )
                                        }}
                                    </span>
                                    <button
                                        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium"
                                    >
                                        View Task →
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Task Details Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6"
                    >
                        <div
                            class="bg-gradient-to-r from-gray-700 to-gray-900 px-6 py-4"
                        >
                            <h3 class="text-lg font-semibold text-white">
                                Task Details
                            </h3>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Assigned To
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{
                                            task.assigned_to_user?.name ||
                                            "Unassigned"
                                        }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Category
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ task.category || "No category" }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Est. Hours
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ task.estimated_hours || 0 }}h
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Actual Hours
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ task.actual_hours || 0 }}h
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Created By
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ task.user?.name || "System" }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Created
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ formatDateTime(task.created_at) }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Updated
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ formatDateTime(task.updated_at) }}
                                    </dd>
                                </div>

                                <div
                                    v-if="task.completed_at"
                                    class="flex justify-between items-center py-3 bg-green-50 rounded-lg px-3"
                                >
                                    <dt
                                        class="text-sm font-medium text-green-700"
                                    >
                                        Completed
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-green-900"
                                    >
                                        {{ formatDateTime(task.completed_at) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Information -->
        <div class="mt-8 border-t pt-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-if="task.related_goal">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Related Goal
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900">
                            {{ task.related_goal.title }}
                        </h4>
                        <p class="text-sm text-gray-600 mt-1">
                            {{
                                task.related_goal.description ||
                                "No description"
                            }}
                        </p>
                        <div class="mt-2">
                            <span
                                :class="
                                    getStatusClass(task.related_goal.status)
                                "
                                class="px-2 py-1 text-xs font-medium rounded-full"
                            >
                                {{ formatStatus(task.related_goal.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="task.parent_task">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Parent Task
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900">
                            {{ task.parent_task.title }}
                        </h4>
                        <p class="text-sm text-gray-600 mt-1">
                            {{
                                task.parent_task.description || "No description"
                            }}
                        </p>
                        <div class="mt-2">
                            <span
                                :class="getStatusClass(task.parent_task.status)"
                                class="px-2 py-1 text-xs font-medium rounded-full"
                            >
                                {{ formatStatus(task.parent_task.status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    task: {
        type: Object,
        required: true,
    },
});

const task = ref({});

const fetchTask = () => {
    task.value = props.task;
};

const editTask = () => {
    router.visit(`/tasks/${props.task.id}/edit`);
};

const deleteTask = () => {
    if (confirm("Are you sure you want to delete this task?")) {
        router.delete(`/tasks/${props.task.id}`, {
            onSuccess: () => {
                router.visit("/tasks");
            },
        });
    }
};

const goBack = () => {
    router.visit("/tasks");
};

// Utility functions
const formatCurrency = (value) => {
    return parseFloat(value || 0).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return "No due date";
    return new Date(date).toLocaleDateString();
};

const formatDateTime = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleString();
};

const formatStatus = (status) => {
    if (!status) return "Unknown";
    return status.charAt(0).toUpperCase() + status.slice(1).replace("_", " ");
};

const formatPriority = (priority) => {
    if (!priority) return "Unknown";
    return priority.charAt(0).toUpperCase() + priority.slice(1);
};

const formatTags = (tags) => {
    if (!tags) return [];
    if (Array.isArray(tags)) return tags;
    if (typeof tags === "string") {
        try {
            // Try to parse as JSON first (in case it's a JSON string)
            const parsed = JSON.parse(tags);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            // If JSON parsing fails, treat as comma-separated string
            return tags
                .split(",")
                .map((tag) => tag.trim())
                .filter((tag) => tag);
        }
    }
    return [];
};

const getStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        not_started: "bg-gray-100 text-gray-800",
        in_progress: "bg-blue-100 text-blue-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getPriorityClass = (priority) => {
    const classes = {
        low: "bg-gray-100 text-gray-800",
        medium: "bg-yellow-100 text-yellow-800",
        high: "bg-orange-100 text-orange-800",
        urgent: "bg-red-100 text-red-800",
    };
    return classes[priority] || "bg-gray-100 text-gray-800";
};

const getProgressPercentage = (task) => {
    if (task.status === "completed") return 100;
    if (task.status === "cancelled" || task.status === "not_started") return 0;

    const estimated = parseFloat(task.estimated_hours) || 0;
    const actual = parseFloat(task.actual_hours) || 0;

    if (estimated === 0) return 0;
    return Math.min(Math.round((actual / estimated) * 100), 100);
};

// Lifecycle
onMounted(() => {
    fetchTask();
});
</script>
