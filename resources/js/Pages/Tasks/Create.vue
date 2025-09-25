<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Task
            </h2>
        </template>

        <div>
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
                                            <option value="not_started">
                                                Not Started
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
                                    <div class="mt-1">
                                        <!-- Tag Input Container -->
                                        <div class="relative">
                                            <div
                                                class="flex flex-wrap items-center gap-2 p-2 border border-gray-300 rounded-md shadow-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 min-h-12 transition-colors"
                                                :class="{
                                                    'border-red-500':
                                                        form.errors.tags,
                                                    'bg-gray-50':
                                                        isTagInputFocused,
                                                }"
                                                @click="focusTagInput"
                                            >
                                                <!-- Existing Tags -->
                                                <span
                                                    v-for="(
                                                        tag, index
                                                    ) in currentTagsArray"
                                                    :key="index"
                                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 border border-indigo-200 transition-all hover:bg-indigo-200 hover:scale-105"
                                                >
                                                    {{ tag.trim() }}
                                                    <button
                                                        type="button"
                                                        @click.stop="
                                                            removeTag(index)
                                                        "
                                                        class="text-indigo-600 hover:text-indigo-800 focus:outline-none transition-colors"
                                                        title="Remove tag"
                                                    >
                                                        <svg
                                                            class="h-4 w-4"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M6 18L18 6M6 6l12 12"
                                                            ></path>
                                                        </svg>
                                                    </button>
                                                </span>

                                                <!-- Tag Input Field -->
                                                <input
                                                    ref="tagInputRef"
                                                    type="text"
                                                    v-model="tagInput"
                                                    @keydown="
                                                        handleTagInputKeydown
                                                    "
                                                    @focus="
                                                        isTagInputFocused = true
                                                    "
                                                    @blur="
                                                        isTagInputFocused = false
                                                    "
                                                    @input="
                                                        updateCurrentTagPreview
                                                    "
                                                    placeholder="Type to add tags..."
                                                    class="flex-1 min-w-0 bg-transparent border-none outline-none py-1 px-2 text-sm placeholder-gray-400"
                                                    :class="{
                                                        'opacity-50':
                                                            form.processing,
                                                    }"
                                                    :disabled="form.processing"
                                                />

                                                <!-- Add Tag Button -->
                                                <button
                                                    v-if="tagInput.trim()"
                                                    type="button"
                                                    @click.prevent.stop="
                                                        addTagFromButton
                                                    "
                                                    class="text-indigo-600 hover:text-indigo-800 focus:outline-none transition-colors p-1 rounded"
                                                    title="Add tag"
                                                    :disabled="form.processing"
                                                >
                                                    <svg
                                                        class="h-5 w-5"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                                        ></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Current Tag Preview -->
                                            <div
                                                v-if="currentTagPreview"
                                                class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-md shadow-lg z-10 p-2"
                                            >
                                                <div
                                                    class="text-sm text-gray-600 flex items-center justify-between"
                                                >
                                                    <span
                                                        >Press Enter or Comma to
                                                        add:</span
                                                    >
                                                    <span
                                                        class="font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded"
                                                    >
                                                        {{ currentTagPreview }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tag Suggestions (if any) -->
                                        <div
                                            v-if="tagSuggestions.length > 0"
                                            class="mt-1 p-2 bg-gray-50 border border-gray-200 rounded-md"
                                        >
                                            <p
                                                class="text-xs text-gray-500 mb-1"
                                            >
                                                Common tags:
                                            </p>
                                            <div class="flex flex-wrap gap-1">
                                                <span
                                                    v-for="(
                                                        suggestion, index
                                                    ) in tagSuggestions"
                                                    :key="index"
                                                    @click="
                                                        addSuggestion(
                                                            suggestion
                                                        )
                                                    "
                                                    class="text-xs px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors cursor-pointer inline-block"
                                                    role="button"
                                                    tabindex="0"
                                                    @keydown.enter="
                                                        addSuggestion(
                                                            suggestion
                                                        )
                                                    "
                                                    @keydown.space.prevent="
                                                        addSuggestion(
                                                            suggestion
                                                        )
                                                    "
                                                >
                                                    {{ suggestion }}
                                                </span>
                                            </div>
                                        </div>

                                        <p class="mt-1 text-xs text-gray-500">
                                            Press Enter or Comma to add tags.
                                        </p>
                                    </div>
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

                            <!-- File Attachments -->
                            <div class="border-b border-gray-200 pb-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    File Attachments
                                </h3>

                                <div>
                                    <InputLabel
                                        value="Upload Files"
                                        class="mb-2"
                                    />
                                    <FileUpload
                                        v-model="form.media"
                                        :multiple="true"
                                        accept="image/*,.pdf,.doc,.docx,.txt"
                                        :maxFiles="10"
                                        :maxSize="10 * 1024 * 1024"
                                        title="Drag & drop files here or click to browse"
                                        description="Supports images, PDFs, and documents (max 10MB each)"
                                        @error="handleFileError"
                                    />
                                    <InputError
                                        :message="form.errors.media"
                                        class="mt-2"
                                    />
                                    <p class="text-xs text-gray-500 mt-2">
                                        You can upload up to 10 files. Supported
                                        formats: JPG, PNG, GIF, PDF, DOC, DOCX,
                                        TXT.
                                    </p>
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
import { computed, ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import FileUpload from "@/Components/UI/FileUpload.vue";

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
    tags: [],
    notes: "",
    parent_task_id: null,
    related_goal_id: "",
    is_recurring: false,
    recurring_frequency: "",
    media: [],
});

// Computed properties
const users = computed(() => props.users);
const goals = computed(() => props.goals);

// Enhanced Tag Handling
const tagInput = ref("");
const tagInputRef = ref(null);
const isTagInputFocused = ref(false);
const currentTagPreview = ref("");
const tagSuggestions = ref([
    "urgent",
    "important",
    "follow-up",
    "review",
    "meeting",
    "documentation",
]);

// Computed properties
const currentTagsArray = computed(() => {
    if (Array.isArray(form.tags)) {
        return form.tags.filter((tag) => tag && tag.trim());
    }
    return [];
});

// Methods
const focusTagInput = () => {
    if (tagInputRef.value) {
        tagInputRef.value.focus();
    }
};

const updateCurrentTagPreview = () => {
    const tagText = tagInput.value.trim();
    currentTagPreview.value = tagText;
};

const handleTagInputKeydown = (event) => {
    const tagText = tagInput.value.trim();

    if (event.key === "Enter" || event.key === "," || event.keyCode === 188) {
        event.preventDefault();
        if (tagText) {
            addTag(tagText);
        }
    } else if (
        event.key === "Backspace" &&
        !tagText &&
        currentTagsArray.value.length > 0
    ) {
        // Remove last tag when backspace is pressed on empty input
        event.preventDefault();
        removeTag(currentTagsArray.value.length - 1);
    }
};

const addTagFromButton = (event) => {
    // Explicitly prevent any form submission
    event?.preventDefault?.();
    event?.stopPropagation?.();
    const tagText = tagInput.value.trim();
    if (tagText) {
        addTag(tagText);
    }
};

const addTag = (tagText) => {
    if (!tagText) return;

    const trimmedTag = tagText.trim();
    if (!trimmedTag) return;

    // Add new tag if it doesn't already exist
    if (!form.tags.includes(trimmedTag)) {
        form.tags.push(trimmedTag);
    }

    // Clear input and reset preview
    tagInput.value = "";
    currentTagPreview.value = "";
};

const removeTag = (index) => {
    // Remove tag at index
    if (index >= 0 && index < form.tags.length) {
        form.tags.splice(index, 1);
    }
};

const addSuggestion = (suggestion) => {
    addTag(suggestion);
};

const handleFileError = (errorMessage) => {
    // You could show a toast notification here
    console.error("File upload error:", errorMessage);
};

const submitForm = () => {
    // Tags are already in array format, no need to process them
    form.post(route("tasks.web.store"), {
        forceFormData: true, // This ensures files are handled properly
        onSuccess: () => {
            router.visit(route("tasks.web.index"));
        },
        onError: (errors) => {
            console.error("Form errors:", errors);
        },
    });
};

const goBack = () => {
    router.visit(route("tasks.web.index"));
};
</script>
