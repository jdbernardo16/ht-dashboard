<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Task
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Form Container -->
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="border-b border-gray-200 pb-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Basic Information
                                </h3>

                                <!-- Title -->
                                <div>
                                    <InputLabel for="title" value="Title *" />
                                    <TextInput
                                        id="title"
                                        v-model="form.title"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                        autofocus
                                        :class="{
                                            'border-red-500': form.errors.title,
                                        }"
                                    />
                                    <InputError
                                        :message="form.errors.title"
                                        class="mt-2"
                                    />
                                </div>

                                <!-- Description -->
                                <div class="mt-4">
                                    <InputLabel
                                        for="description"
                                        value="Description"
                                    />
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        rows="4"
                                        :class="{
                                            'border-red-500':
                                                form.errors.description,
                                        }"
                                    ></textarea>
                                    <InputError
                                        :message="form.errors.description"
                                        class="mt-2"
                                    />
                                </div>
                            </div>

                            <!-- Task Details -->
                            <div class="border-b border-gray-200 pb-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Task Details
                                </h3>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <!-- Priority -->
                                    <div>
                                        <InputLabel
                                            for="priority"
                                            value="Priority *"
                                        />
                                        <select
                                            id="priority"
                                            v-model="form.priority"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                            :class="{
                                                'border-red-500':
                                                    form.errors.priority,
                                            }"
                                        >
                                            <option value="">
                                                Select Priority
                                            </option>
                                            <option value="low">Low</option>
                                            <option value="medium">
                                                Medium
                                            </option>
                                            <option value="high">High</option>
                                            <option value="urgent">
                                                Urgent
                                            </option>
                                        </select>
                                        <InputError
                                            :message="form.errors.priority"
                                            class="mt-2"
                                        />
                                    </div>

                                    <!-- Status -->
                                    <div>
                                        <InputLabel
                                            for="status"
                                            value="Status *"
                                        />
                                        <select
                                            id="status"
                                            v-model="form.status"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                            :class="{
                                                'border-red-500':
                                                    form.errors.status,
                                            }"
                                        >
                                            <option value="">
                                                Select Status
                                            </option>
                                            <option value="pending">
                                                Pending
                                            </option>
                                            <option value="in_progress">
                                                In Progress
                                            </option>
                                            <option value="completed">
                                                Completed
                                            </option>
                                            <option value="cancelled">
                                                Cancelled
                                            </option>
                                        </select>
                                        <InputError
                                            :message="form.errors.status"
                                            class="mt-2"
                                        />
                                    </div>

                                    <!-- Due Date -->
                                    <div>
                                        <InputLabel
                                            for="due_date"
                                            value="Due Date *"
                                        />
                                        <TextInput
                                            id="due_date"
                                            v-model="form.due_date"
                                            type="date"
                                            class="mt-1 block w-full"
                                            required
                                            :class="{
                                                'border-red-500':
                                                    form.errors.due_date,
                                            }"
                                        />
                                        <InputError
                                            :message="form.errors.due_date"
                                            class="mt-2"
                                        />
                                    </div>

                                    <!-- Category -->
                                    <div>
                                        <InputLabel
                                            for="category"
                                            value="Category"
                                        />
                                        <TextInput
                                            id="category"
                                            v-model="form.category"
                                            type="text"
                                            class="mt-1 block w-full"
                                            :class="{
                                                'border-red-500':
                                                    form.errors.category,
                                            }"
                                        />
                                        <InputError
                                            :message="form.errors.category"
                                            class="mt-2"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Assignment -->
                            <div class="border-b border-gray-200 pb-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Assignment
                                </h3>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <!-- Assigned To -->
                                    <div>
                                        <InputLabel
                                            for="assigned_to"
                                            value="Assigned To"
                                        />
                                        <select
                                            id="assigned_to"
                                            v-model="form.assigned_to"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{
                                                'border-red-500':
                                                    form.errors.assigned_to,
                                            }"
                                        >
                                            <option value="">Unassigned</option>
                                            <option
                                                v-for="user in users"
                                                :key="user.id"
                                                :value="user.id"
                                            >
                                                {{ user.name }}
                                            </option>
                                        </select>
                                        <InputError
                                            :message="form.errors.assigned_to"
                                            class="mt-2"
                                        />
                                    </div>

                                    <!-- Related Goal -->
                                    <div>
                                        <InputLabel
                                            for="related_goal_id"
                                            value="Related Goal"
                                        />
                                        <select
                                            id="related_goal_id"
                                            v-model="form.related_goal_id"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{
                                                'border-red-500':
                                                    form.errors.related_goal_id,
                                            }"
                                        >
                                            <option value="">No Goal</option>
                                            <option
                                                v-for="goal in goals"
                                                :key="goal.id"
                                                :value="goal.id"
                                            >
                                                {{ goal.title }}
                                            </option>
                                        </select>
                                        <InputError
                                            :message="
                                                form.errors.related_goal_id
                                            "
                                            class="mt-2"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Time Tracking -->
                            <div class="border-b border-gray-200 pb-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Time Tracking
                                </h3>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <!-- Estimated Hours -->
                                    <div>
                                        <InputLabel
                                            for="estimated_hours"
                                            value="Estimated Hours"
                                        />
                                        <TextInput
                                            id="estimated_hours"
                                            v-model="form.estimated_hours"
                                            type="number"
                                            step="0.5"
                                            min="0"
                                            class="mt-1 block w-full"
                                            :class="{
                                                'border-red-500':
                                                    form.errors.estimated_hours,
                                            }"
                                        />
                                        <InputError
                                            :message="
                                                form.errors.estimated_hours
                                            "
                                            class="mt-2"
                                        />
                                    </div>

                                    <!-- Actual Hours -->
                                    <div>
                                        <InputLabel
                                            for="actual_hours"
                                            value="Actual Hours"
                                        />
                                        <TextInput
                                            id="actual_hours"
                                            v-model="form.actual_hours"
                                            type="number"
                                            step="0.5"
                                            min="0"
                                            class="mt-1 block w-full"
                                            :class="{
                                                'border-red-500':
                                                    form.errors.actual_hours,
                                            }"
                                        />
                                        <InputError
                                            :message="form.errors.actual_hours"
                                            class="mt-2"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Details -->
                            <div class="border-b border-gray-200 pb-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Additional Details
                                </h3>

                                <!-- Tags -->
                                <div>
                                    <InputLabel for="tags" value="Tags" />
                                    <TextInput
                                        id="tags"
                                        v-model="form.tags"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="Enter tags separated by commas"
                                        :class="{
                                            'border-red-500': form.errors.tags,
                                        }"
                                    />
                                    <InputError
                                        :message="form.errors.tags"
                                        class="mt-2"
                                    />
                                </div>

                                <!-- Notes -->
                                <div class="mt-4">
                                    <InputLabel for="notes" value="Notes" />
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        rows="3"
                                        :class="{
                                            'border-red-500': form.errors.notes,
                                        }"
                                    ></textarea>
                                    <InputError
                                        :message="form.errors.notes"
                                        class="mt-2"
                                    />
                                </div>
                            </div>

                            <!-- Recurring Task -->
                            <div class="border-b border-gray-200 pb-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Recurring Task
                                </h3>

                                <div class="space-y-4">
                                    <!-- Is Recurring -->
                                    <div class="flex items-center">
                                        <input
                                            id="is_recurring"
                                            v-model="form.is_recurring"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <InputLabel
                                            for="is_recurring"
                                            value="This is a recurring task"
                                            class="ml-2"
                                        />
                                    </div>

                                    <!-- Recurring Frequency -->
                                    <div v-if="form.is_recurring">
                                        <InputLabel
                                            for="recurring_frequency"
                                            value="Frequency"
                                        />
                                        <select
                                            id="recurring_frequency"
                                            v-model="form.recurring_frequency"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{
                                                'border-red-500':
                                                    form.errors
                                                        .recurring_frequency,
                                            }"
                                        >
                                            <option value="">
                                                Select Frequency
                                            </option>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">
                                                Weekly
                                            </option>
                                            <option value="monthly">
                                                Monthly
                                            </option>
                                            <option value="yearly">
                                                Yearly
                                            </option>
                                        </select>
                                        <InputError
                                            :message="
                                                form.errors.recurring_frequency
                                            "
                                            class="mt-2"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div
                                class="flex items-center justify-end space-x-3 pt-6"
                            >
                                <SecondaryButton
                                    type="button"
                                    @click="goBack"
                                    :disabled="form.processing"
                                >
                                    Cancel
                                </SecondaryButton>
                                <PrimaryButton
                                    type="submit"
                                    :disabled="form.processing"
                                    :class="{ 'opacity-50': form.processing }"
                                >
                                    <span v-if="form.processing"
                                        >Creating...</span
                                    >
                                    <span v-else>Create Task</span>
                                </PrimaryButton>
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
import { router } from "@inertiajs/vue3";
import { computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

// Props
const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    goals: {
        type: Array,
        default: () => [],
    },
});

// Form setup
const form = useForm({
    title: "",
    description: "",
    priority: "medium",
    status: "pending",
    due_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000)
        .toISOString()
        .split("T")[0],
    assigned_to: "",
    category: "",
    estimated_hours: null,
    actual_hours: null,
    tags: "",
    notes: "",
    parent_task_id: null,
    related_goal_id: "",
    is_recurring: false,
    recurring_frequency: "",
});

// Computed properties
const users = computed(() => props.users);
const goals = computed(() => props.goals);

// Methods
const submitForm = () => {
    // Process tags
    const processedForm = {
        ...form.data(),
        tags: form.tags
            ? form.tags
                  .split(",")
                  .map((tag) => tag.trim())
                  .filter((tag) => tag)
            : [],
    };

    form.post(route("tasks.store"), {
        data: processedForm,
        onSuccess: () => {
            router.visit(route("tasks.index"));
        },
        onError: (errors) => {
            console.error("Form errors:", errors);
        },
    });
};

const goBack = () => {
    router.visit(route("tasks.index"));
};
</script>
