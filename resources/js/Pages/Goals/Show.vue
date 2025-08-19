<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Goal Details
                </h2>
                <div class="flex space-x-2">
                    <button
                        @click="editGoal"
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Edit
                    </button>
                    <button
                        @click="deleteGoal"
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
                        <!-- Goal Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Goal Information
                                </h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Title
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ props.goal.title }}
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
                                                props.goal.description ||
                                                "No description provided"
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Target Value
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            ${{
                                                formatCurrency(
                                                    props.goal.target_value
                                                )
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Current Value
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            ${{
                                                formatCurrency(
                                                    props.goal.current_value
                                                )
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Progress
                                        </dt>
                                        <dd class="mt-1">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-full bg-gray-200 rounded-full h-2.5"
                                                >
                                                    <div
                                                        class="bg-blue-600 h-2.5 rounded-full"
                                                        :style="{
                                                            width:
                                                                progressPercentage +
                                                                '%',
                                                        }"
                                                    ></div>
                                                </div>
                                                <span
                                                    class="ml-2 text-sm font-medium text-gray-900"
                                                >
                                                    {{ progressPercentage }}%
                                                </span>
                                            </div>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Quarter
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ props.goal.quarter }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Year
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ props.goal.year }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Additional Details
                                </h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Deadline
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatDate(props.goal.deadline)
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Days Until Deadline
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <span
                                                :class="daysUntilDeadlineClass"
                                            >
                                                {{ daysUntilDeadline }}
                                            </span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Status
                                        </dt>
                                        <dd class="mt-1">
                                            <span
                                                :class="
                                                    getStatusClass(
                                                        props.goal.status
                                                    )
                                                "
                                                class="px-2 py-1 text-xs font-medium rounded-full"
                                            >
                                                {{
                                                    formatStatus(
                                                        props.goal.status
                                                    )
                                                }}
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
                                            {{
                                                props.goal.user?.name ||
                                                "System"
                                            }}
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
                                                formatDateTime(
                                                    props.goal.created_at
                                                )
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
                                                formatDateTime(
                                                    props.goal.updated_at
                                                )
                                            }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Statistics Card -->
                        <div class="mt-8 bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Goal Statistics
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Target Value
                                    </dt>
                                    <dd
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        ${{
                                            formatCurrency(
                                                props.goal.target_value
                                            )
                                        }}
                                    </dd>
                                </div>

                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Current Value
                                    </dt>
                                    <dd
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        ${{
                                            formatCurrency(
                                                props.goal.current_value
                                            )
                                        }}
                                    </dd>
                                </div>

                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Remaining Value
                                    </dt>
                                    <dd
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        ${{ formatCurrency(remainingValue) }}
                                    </dd>
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
import { computed } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    goal: {
        type: Object,
        required: true,
    },
});

const editGoal = () => {
    router.visit(`/goals/${props.goal.id}/edit`);
};

const deleteGoal = () => {
    if (confirm("Are you sure you want to delete this goal?")) {
        router.delete(`/goals/${props.goal.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                router.visit("/goals");
            },
            onError: (errors) => {
                console.error("Error deleting goal:", errors);
                if (errors.message?.includes("403")) {
                    alert("You don't have permission to delete this goal.");
                }
            },
        });
    }
};

const goBack = () => {
    router.visit("/goals");
};

// Utility functions
const formatCurrency = (value) => {
    return parseFloat(value || 0).toFixed(2);
};

const formatDate = (date) => {
    if (!date) return "N/A";
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

const getStatusClass = (status) => {
    const classes = {
        draft: "bg-gray-100 text-gray-800",
        published: "bg-green-100 text-green-800",
        archived: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const progressPercentage = computed(() => {
    if (!props.goal.target_value || props.goal.target_value <= 0) {
        return 0;
    }
    return Math.min(
        100,
        Math.round((props.goal.current_value / props.goal.target_value) * 100)
    );
});

const remainingValue = computed(() => {
    return Math.max(0, props.goal.target_value - props.goal.current_value);
});

const daysUntilDeadline = computed(() => {
    if (!props.goal.deadline) return "No deadline set";

    const deadlineDate = new Date(props.goal.deadline);
    const today = new Date();

    if (deadlineDate < today) {
        return "Overdue";
    }

    const diffTime = deadlineDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    return diffDays === 0
        ? "Due today"
        : `${diffDays} day${diffDays > 1 ? "s" : ""}`;
});

const daysUntilDeadlineClass = computed(() => {
    if (!props.goal.deadline) return "text-gray-600";

    const deadlineDate = new Date(props.goal.deadline);
    const today = new Date();

    if (deadlineDate < today) {
        return "text-red-600 font-semibold";
    }

    const diffTime = deadlineDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays <= 7) {
        return "text-orange-600 font-semibold";
    }

    return "text-green-600";
});
</script>
