<template>
    <DashboardModule title="Quarterly Goals" :loading="loading" :error="error">
        <div class="space-y-6">
            <!-- Overall Progress -->
            <div class="bg-white rounded-lg p-6 border">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Overall Progress
                    </h3>
                    <span class="text-2xl font-bold text-blue-600"
                        >{{ overallProgress }}%</span
                    >
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div
                        class="bg-blue-500 h-3 rounded-full transition-all duration-300"
                        :style="{ width: `${overallProgress}%` }"
                    ></div>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    {{ completedGoals }} of {{ totalGoals }} goals completed
                </p>
            </div>

            <!-- Goals List -->
            <div class="space-y-4">
                <div
                    v-for="goal in goals"
                    :key="goal.id"
                    class="bg-white rounded-lg p-4 border"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex-1">
                            <h4 class="text-md font-semibold text-gray-900">
                                {{ goal.title }}
                            </h4>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ goal.description }}
                            </p>
                        </div>
                        <div class="ml-4">
                            <span
                                :class="{
                                    'bg-green-100 text-green-800':
                                        goal.status === 'completed',
                                    'bg-yellow-100 text-yellow-800':
                                        goal.status === 'in_progress',
                                    'bg-gray-100 text-gray-800':
                                        goal.status === 'not_started',
                                }"
                                class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium"
                            >
                                {{ goal.statusText }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Progress</span>
                            <span class="font-medium text-gray-900"
                                >{{ goal.progress }}%</span
                            >
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div
                                class="h-2 rounded-full transition-all duration-300"
                                :class="{
                                    'bg-green-500': goal.status === 'completed',
                                    'bg-blue-500':
                                        goal.status === 'in_progress',
                                    'bg-gray-400':
                                        goal.status === 'not_started',
                                }"
                                :style="{ width: `${goal.progress}%` }"
                            ></div>
                        </div>
                        <div
                            class="flex items-center justify-between text-xs text-gray-500"
                        >
                            <span
                                >{{ goal.current }} / {{ goal.target }}
                                {{ goal.unit }}</span
                            >
                            <span>{{ goal.deadline }}</span>
                        </div>
                    </div>

                    <!-- Milestones -->
                    <div
                        v-if="goal.milestones && goal.milestones.length"
                        class="mt-4 pt-4 border-t"
                    >
                        <h5 class="text-sm font-medium text-gray-900 mb-2">
                            Milestones
                        </h5>
                        <div class="space-y-2">
                            <div
                                v-for="milestone in goal.milestones"
                                :key="milestone.id"
                                class="flex items-center"
                            >
                                <div
                                    :class="
                                        milestone.completed
                                            ? 'bg-green-500'
                                            : 'bg-gray-300'
                                    "
                                    class="w-4 h-4 rounded-full mr-3 flex-shrink-0"
                                ></div>
                                <span
                                    :class="
                                        milestone.completed
                                            ? 'text-gray-900 line-through'
                                            : 'text-gray-600'
                                    "
                                    class="text-sm"
                                >
                                    {{ milestone.title }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Goal Categories -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                    v-for="category in goalCategories"
                    :key="category.name"
                    class="bg-white rounded-lg p-4 border"
                >
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-medium text-gray-900">
                            {{ category.name }}
                        </h4>
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
                            :style="{ backgroundColor: category.color }"
                        >
                            {{ category.count }}
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1">
                        {{ category.progress }}%
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div
                            class="h-2 rounded-full"
                            :style="{
                                width: `${category.progress}%`,
                                backgroundColor: category.color,
                            }"
                        ></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ category.completed }} /
                        {{ category.total }} completed
                    </p>
                </div>
            </div>
        </div>
    </DashboardModule>
</template>

<script setup>
import { computed } from "vue";
import DashboardModule from "./DashboardModule.vue";

const props = defineProps({
    goals: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
});

const goals = computed(
    () =>
        props.goals || [
            {
                id: 1,
                title: "Increase Monthly Revenue",
                description: "Achieve $50,000 in monthly recurring revenue",
                target: 50000,
                current: 42000,
                unit: "USD",
                progress: 84,
                status: "in_progress",
                statusText: "In Progress",
                deadline: "Mar 31, 2024",
                milestones: [
                    {
                        id: 1,
                        title: "Launch new pricing tier",
                        completed: true,
                    },
                    {
                        id: 2,
                        title: "Onboard 50 new customers",
                        completed: true,
                    },
                    { id: 3, title: "Reduce churn by 5%", completed: false },
                ],
            },
            {
                id: 2,
                title: "Launch New Product Feature",
                description: "Release version 2.0 with enhanced capabilities",
                target: 100,
                current: 100,
                unit: "%",
                progress: 100,
                status: "completed",
                statusText: "Completed",
                deadline: "Feb 15, 2024",
                milestones: [
                    { id: 1, title: "Complete development", completed: true },
                    { id: 2, title: "Beta testing phase", completed: true },
                    { id: 3, title: "Production deployment", completed: true },
                ],
            },
            {
                id: 3,
                title: "Customer Satisfaction",
                description: "Achieve 95% customer satisfaction score",
                target: 95,
                current: 87,
                unit: "%",
                progress: 92,
                status: "in_progress",
                statusText: "In Progress",
                deadline: "Mar 31, 2024",
                milestones: [
                    {
                        id: 1,
                        title: "Implement feedback system",
                        completed: true,
                    },
                    { id: 2, title: "Address top 10 issues", completed: false },
                    {
                        id: 3,
                        title: "Conduct satisfaction survey",
                        completed: false,
                    },
                ],
            },
        ]
);

const goalCategories = computed(() => [
    {
        name: "Revenue",
        count: 2,
        total: 2,
        completed: 1,
        progress: 84,
        color: "#3B82F6",
    },
    {
        name: "Product",
        count: 3,
        total: 3,
        completed: 3,
        progress: 100,
        color: "#10B981",
    },
    {
        name: "Customer",
        count: 2,
        total: 2,
        completed: 1,
        progress: 92,
        color: "#F59E0B",
    },
]);

const overallProgress = computed(() => {
    if (!goals.value.length) return 0;
    const totalProgress = goals.value.reduce(
        (sum, goal) => sum + goal.progress,
        0
    );
    return Math.round(totalProgress / goals.value.length);
});

const completedGoals = computed(() => {
    return goals.value.filter((goal) => goal.status === "completed").length;
});

const totalGoals = computed(() => {
    return goals.value.length;
});

const formatNumber = (value) => {
    return new Intl.NumberFormat("en-US").format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
    });
};
</script>
