<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Task: {{ task.title }}
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

                                <!-- Existing Media Files -->
                                <div
                                    v-if="task.media && task.media.length > 0"
                                    class="mb-6"
                                >
                                    <InputLabel
                                        value="Existing Files"
                                        class="mb-2"
                                    />
                                    <div class="space-y-3">
                                        <div
                                            v-for="media in task.media"
                                            :key="media.id"
                                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border"
                                        >
                                            <div
                                                class="flex items-center space-x-3"
                                            >
                                                <!-- File Icon -->
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="w-10 h-10 flex items-center justify-center rounded bg-gray-200"
                                                    >
                                                        <span
                                                            class="text-xs font-medium text-gray-600"
                                                        >
                                                            {{
                                                                getFileExtension(
                                                                    media.original_name
                                                                )
                                                            }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- File Info -->
                                                <div class="min-w-0 flex-1">
                                                    <p
                                                        class="text-sm font-medium text-gray-900 truncate"
                                                    >
                                                        {{
                                                            media.original_name
                                                        }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-gray-500"
                                                    >
                                                        {{
                                                            formatFileSize(
                                                                media.file_size
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Remove Button -->
                                            <button
                                                type="button"
                                                @click="
                                                    removeExistingMedia(
                                                        media.id
                                                    )
                                                "
                                                class="ml-3 text-red-600 hover:text-red-800"
                                                title="Remove file"
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
                                                        d="M6 18L18 6M6 6l12 12"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- New File Upload -->
                                <div>
                                    <InputLabel
                                        value="Upload New Files"
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
                                        >Updating...</span
                                    >
                                    <span v-else>Update Task</span>
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
    task: {
        type: Object,
        required: true,
    },
    users: {
        type: Array,
        default: () => [],
    },
    goals: {
        type: Array,
        default: () => [],
    },
});

// Helper function to convert ISO datetime to date input format (YYYY-MM-DD)
const formatDateForInput = (isoDate) => {
    if (!isoDate) return "";
    const date = new Date(isoDate);
    return date.toISOString().split("T")[0];
};

// Form setup with pre-populated data
const form = useForm({
    title: props.task.title || "",
    description: props.task.description || "",
    priority: props.task.priority || "",
    status: props.task.status || "",
    due_date: props.task.due_date
        ? formatDateForInput(props.task.due_date)
        : "",
    assigned_to: props.task.assigned_to
        ? props.task.assigned_to.toString()
        : "",
    category: props.task.category || "",
    estimated_hours: props.task.estimated_hours || "",
    actual_hours: props.task.actual_hours || "",
    tags: (() => {
        if (Array.isArray(props.task.tags)) {
            return props.task.tags;
        } else if (typeof props.task.tags === "string") {
            try {
                // Try to parse as JSON first (in case it's a JSON string)
                const parsed = JSON.parse(props.task.tags);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                // If JSON parsing fails, treat as comma-separated string
                return props.task.tags
                    .split(",")
                    .map((tag) => tag.trim())
                    .filter((tag) => tag);
            }
        }
        return [];
    })(),
    notes: props.task.notes || "",
    related_goal_id: props.task.related_goal_id || "",
    is_recurring: Boolean(props.task.is_recurring),
    recurring_frequency: props.task.recurring_frequency || "",
    media: [],
});

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
    console.log("Form data being submitted:", {
        status: form.status,
        priority: form.priority,
        title: form.title,
        tags: form.tags,
    });

    // When using forceFormData, we need to ensure array data is properly handled
    // Convert tags array to JSON string for FormData compatibility
    const formData = new FormData();

    // Add all form fields to FormData
    Object.keys(form.data()).forEach((key) => {
        if (key === "tags" && Array.isArray(form[key])) {
            // Convert tags array to JSON string for FormData
            formData.append(key, JSON.stringify(form[key]));
        } else if (key === "media" && Array.isArray(form[key])) {
            // Handle media files separately
            form[key].forEach((file) => {
                formData.append("media[]", file);
            });
        } else {
            formData.append(key, form[key]);
        }
    });

    form.put(route("tasks.web.update", props.task.id), {
        data: formData,
        preserveScroll: true,
        onSuccess: () => {
            // The backend redirects back to this page with success message
            // No need for additional redirect here
            console.log("Form submitted successfully");
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

// Utility functions for media handling
const getFileExtension = (filename) => {
    const parts = filename.split(".");
    return parts.length > 1
        ? parts.pop().toUpperCase().substring(0, 3)
        : "FILE";
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const removeExistingMedia = (mediaId) => {
    if (!confirm("Are you sure you want to remove this file?")) {
        return;
    }

    router.delete(
        route("tasks.web.media.destroy", {
            task: props.task.id,
            media: mediaId,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                // Remove the media from the local task object
                const index = props.task.media.findIndex(
                    (m) => m.id === mediaId
                );
                if (index !== -1) {
                    props.task.media.splice(index, 1);
                }
            },
            onError: (errors) => {
                console.error("Error removing media:", errors);
                alert("Failed to remove the file. Please try again.");
            },
        }
    );
};
</script>
