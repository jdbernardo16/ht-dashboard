<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Task Details
                </h2>
                <div class="flex space-x-2">
                    <button
                        @click="editTask"
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Edit
                    </button>
                    <button
                        @click="deleteTask"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Delete
                    </button>
                    <button
                        @click="goBack"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Back
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Task Information -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Main Task Info -->
                            <div class="lg:col-span-2">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Task Information
                                </h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Title
                                        </dt>
                                        <dd
                                            class="mt-1 text-lg font-semibold text-gray-900"
                                        >
                                            {{ task.title }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Description
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                task.description ||
                                                "No description provided"
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Notes
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                task.notes ||
                                                "No notes provided"
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Tags
                                        </dt>
                                        <dd class="mt-1">
                                            <div class="flex flex-wrap gap-2">
                                                <span
                                                    v-for="tag in formatTags(
                                                        task.tags
                                                    )"
                                                    :key="tag"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                                >
                                                    {{ tag }}
                                                </span>
                                                <span
                                                    v-if="
                                                        !task.tags ||
                                                        task.tags.length === 0
                                                    "
                                                    class="text-sm text-gray-500"
                                                >
                                                    No tags
                                                </span>
                                            </div>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Task Details & Metadata -->
                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Task Details
                                </h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Status
                                        </dt>
                                        <dd class="mt-1">
                                            <span
                                                :class="
                                                    getStatusClass(task.status)
                                                "
                                                class="px-2 py-1 text-xs font-medium rounded-full"
                                            >
                                                {{ formatStatus(task.status) }}
                                            </span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Priority
                                        </dt>
                                        <dd class="mt-1">
                                            <span
                                                :class="
                                                    getPriorityClass(
                                                        task.priority
                                                    )
                                                "
                                                class="px-2 py-1 text-xs font-medium rounded-full"
                                            >
                                                {{
                                                    formatPriority(
                                                        task.priority
                                                    )
                                                }}
                                            </span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Due Date
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ formatDate(task.due_date) }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Assigned To
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                task.assigned_to_user?.name ||
                                                "Unassigned"
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Category
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ task.category || "No category" }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Estimated Hours
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                task.estimated_hours || 0
                                            }}
                                            hours
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Actual Hours
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ task.actual_hours || 0 }} hours
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Progress
                                        </dt>
                                        <dd class="mt-1">
                                            <div
                                                class="w-full bg-gray-200 rounded-full h-2"
                                            >
                                                <div
                                                    class="bg-blue-600 h-2 rounded-full"
                                                    :style="`width: ${getProgressPercentage(
                                                        task
                                                    )}%`"
                                                ></div>
                                            </div>
                                            <span class="text-xs text-gray-600">
                                                {{
                                                    getProgressPercentage(task)
                                                }}% complete
                                            </span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Created By
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ task.user?.name || "System" }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Created At
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatDateTime(task.created_at)
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Last Updated
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatDateTime(task.updated_at)
                                            }}
                                        </dd>
                                    </div>

                                    <div v-if="task.completed_at">
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Completed At
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatDateTime(
                                                    task.completed_at
                                                )
                                            }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Related Information -->
                        <div class="mt-8 border-t pt-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div v-if="task.related_goal">
                                    <h3
                                        class="text-lg font-medium text-gray-900 mb-4"
                                    >
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
                                                    getStatusClass(
                                                        task.related_goal.status
                                                    )
                                                "
                                                class="px-2 py-1 text-xs font-medium rounded-full"
                                            >
                                                {{
                                                    formatStatus(
                                                        task.related_goal.status
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="task.parent_task">
                                    <h3
                                        class="text-lg font-medium text-gray-900 mb-4"
                                    >
                                        Parent Task
                                    </h3>
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <h4 class="font-medium text-gray-900">
                                            {{ task.parent_task.title }}
                                        </h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{
                                                task.parent_task.description ||
                                                "No description"
                                            }}
                                        </p>
                                        <div class="mt-2">
                                            <span
                                                :class="
                                                    getStatusClass(
                                                        task.parent_task.status
                                                    )
                                                "
                                                class="px-2 py-1 text-xs font-medium rounded-full"
                                            >
                                                {{
                                                    formatStatus(
                                                        task.parent_task.status
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
        return tags
            .split(",")
            .map((tag) => tag.trim())
            .filter((tag) => tag);
    }
    return [];
};

const getStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
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
    if (task.status === "cancelled") return 0;

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
