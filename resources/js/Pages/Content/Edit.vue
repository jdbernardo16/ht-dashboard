<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Content Post
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Client -->
                            <div>
                                <InputLabel for="client_id" value="Client *" />
                                <select
                                    id="client_id"
                                    v-model="formattedClientId"
                                    required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{
                                        'border-red-500': form.errors.client_id,
                                    }"
                                >
                                    <option value="">Select a client</option>
                                    <option
                                        v-for="client in clients"
                                        :key="client.id"
                                        :value="client.id"
                                    >
                                        {{ client.name }}
                                    </option>
                                </select>
                                <InputError
                                    :message="form.errors.client_id"
                                    class="mt-2"
                                />
                            </div>

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

                            <!-- Platform -->
                            <div>
                                <InputLabel value="Platforms *" class="mb-2" />
                                <div
                                    class="grid grid-cols-2 md:grid-cols-3 gap-3"
                                >
                                    <div
                                        v-for="platformOption in platformOptions"
                                        :key="platformOption.value"
                                        class="flex items-center"
                                    >
                                        <input
                                            type="checkbox"
                                            :id="`platform-${platformOption.value}`"
                                            :value="platformOption.value"
                                            v-model="form.platform"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        />
                                        <label
                                            :for="`platform-${platformOption.value}`"
                                            class="ml-2 text-sm text-gray-700"
                                        >
                                            {{ platformOption.label }}
                                        </label>
                                    </div>
                                </div>
                                <InputError
                                    :message="form.errors.platform"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Content Type -->
                            <div>
                                <InputLabel
                                    for="content_type"
                                    value="Content Type *"
                                />
                                <select
                                    id="content_type"
                                    v-model="form.content_type"
                                    required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{
                                        'border-red-500':
                                            form.errors.content_type,
                                    }"
                                >
                                    <option value="">
                                        Select content type
                                    </option>
                                    <option value="post">Post</option>
                                    <option value="story">Story</option>
                                    <option value="reel">Reel</option>
                                    <option value="video">Video</option>
                                    <option value="image">Image</option>
                                    <option value="carousel">Carousel</option>
                                    <option value="live">Live</option>
                                    <option value="article">Article</option>
                                </select>
                                <InputError
                                    :message="form.errors.content_type"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Description -->
                            <div>
                                <InputLabel
                                    for="description"
                                    value="Description"
                                />
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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

                            <!-- Content URL -->
                            <div>
                                <InputLabel
                                    for="content_url"
                                    value="Content URL"
                                />
                                <TextInput
                                    id="content_url"
                                    v-model="form.content_url"
                                    type="url"
                                    class="mt-1 block w-full"
                                    :class="{
                                        'border-red-500':
                                            form.errors.content_url,
                                    }"
                                />
                                <InputError
                                    :message="form.errors.content_url"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Post Count -->
                            <div>
                                <InputLabel
                                    for="post_count"
                                    value="Post Count"
                                />
                                <TextInput
                                    id="post_count"
                                    v-model="form.post_count"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full"
                                    :class="{
                                        'border-red-500':
                                            form.errors.post_count,
                                    }"
                                />
                                <InputError
                                    :message="form.errors.post_count"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Scheduled Date -->
                            <div>
                                <InputLabel
                                    for="scheduled_date"
                                    value="Scheduled Date"
                                />
                                <TextInput
                                    id="scheduled_date"
                                    v-model="form.scheduled_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    :class="{
                                        'border-red-500':
                                            form.errors.scheduled_date,
                                    }"
                                />
                                <InputError
                                    :message="form.errors.scheduled_date"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Published Date -->
                            <div>
                                <InputLabel
                                    for="published_date"
                                    value="Published Date"
                                />
                                <TextInput
                                    id="published_date"
                                    v-model="form.published_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    :class="{
                                        'border-red-500':
                                            form.errors.published_date,
                                    }"
                                />
                                <InputError
                                    :message="form.errors.published_date"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Status -->
                            <div>
                                <InputLabel for="status" value="Status *" />
                                <select
                                    id="status"
                                    v-model="form.status"
                                    required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{
                                        'border-red-500': form.errors.status,
                                    }"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                                <InputError
                                    :message="form.errors.status"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Content Category -->
                            <div>
                                <InputLabel
                                    for="content_category"
                                    value="Content Category"
                                />
                                <TextInput
                                    id="content_category"
                                    v-model="form.content_category"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :class="{
                                        'border-red-500':
                                            form.errors.content_category,
                                    }"
                                />
                                <InputError
                                    :message="form.errors.content_category"
                                    class="mt-2"
                                />
                            </div>

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
                                                'bg-gray-50': isTagInputFocused,
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
                                                @keydown="handleTagInputKeydown"
                                                @focus="
                                                    isTagInputFocused = true
                                                "
                                                @blur="
                                                    isTagInputFocused = false
                                                "
                                                @input="updateCurrentTagPreview"
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
                                        <p class="text-xs text-gray-500 mb-1">
                                            Common tags:
                                        </p>
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="(
                                                    suggestion, index
                                                ) in tagSuggestions"
                                                :key="index"
                                                @click="
                                                    addSuggestion(suggestion)
                                                "
                                                class="text-xs px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors cursor-pointer inline-block"
                                                role="button"
                                                tabindex="0"
                                                @keydown.enter="
                                                    addSuggestion(suggestion)
                                                "
                                                @keydown.space.prevent="
                                                    addSuggestion(suggestion)
                                                "
                                            >
                                                {{ suggestion }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <InputError
                                    :message="form.errors.tags"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Notes -->
                            <div>
                                <label
                                    for="notes"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Notes
                                </label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <p
                                    v-if="form.errors.notes"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.notes }}
                                </p>
                            </div>

                            <!-- Image Upload -->
                            <div>
                                <InputLabel value="Upload Image" class="mb-2" />

                                <!-- Display existing image if available -->
                                <div
                                    v-if="props.contentPost.image"
                                    class="mb-4"
                                >
                                    <p class="text-sm text-gray-600 mb-2">
                                        Current Image:
                                    </p>
                                    <img
                                        :src="props.contentPost.image"
                                        alt="Current content image"
                                        class="max-w-full h-auto max-h-48 rounded-lg border"
                                        onerror="this.style.display='none'"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">
                                        This image will be replaced if you
                                        upload a new one.
                                    </p>
                                </div>

                                <FileUpload
                                    v-model="form.image"
                                    :multiple="false"
                                    accept="image/*"
                                    :maxFiles="1"
                                    :maxSize="10 * 1024 * 1024"
                                    title="Drag & drop image here or click to browse"
                                    description="Supports JPG, PNG, GIF, WebP images (max 10MB - will be optimized)"
                                    @error="handleFileError"
                                />
                                <p class="text-xs text-gray-500 mt-2">
                                    Upload a single image file. Supported
                                    formats: JPG, PNG, GIF.
                                </p>
                                <p
                                    v-if="form.errors.image"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.image }}
                                </p>
                            </div>

                            <!-- Media Upload -->
                            <div>
                                <InputLabel
                                    value="Upload Additional Files"
                                    class="mb-2"
                                />

                                <!-- Display existing media files if available -->
                                <div
                                    v-if="
                                        props.contentPost.media &&
                                        props.contentPost.media.length > 0
                                    "
                                    class="mb-4"
                                >
                                    <p class="text-sm text-gray-600 mb-2">
                                        Current Files:
                                    </p>
                                    <div class="space-y-2">
                                        <div
                                            v-for="media in props.contentPost
                                                .media"
                                            :key="media.id"
                                            class="flex items-center justify-between p-2 bg-gray-50 rounded border"
                                        >
                                            <div
                                                class="flex items-center space-x-2"
                                            >
                                                <span
                                                    class="text-sm text-gray-700"
                                                    >{{
                                                        media.original_name
                                                    }}</span
                                                >
                                                <span
                                                    class="text-xs text-gray-500"
                                                    >({{
                                                        formatFileSize(
                                                            media.file_size
                                                        )
                                                    }})</span
                                                >
                                            </div>
                                            <a
                                                :href="media.file_path"
                                                target="_blank"
                                                class="text-indigo-600 hover:text-indigo-800 text-sm"
                                            >
                                                View
                                            </a>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        These files will remain unless you
                                        delete them.
                                    </p>
                                </div>

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
                                <p class="text-xs text-gray-500 mt-2">
                                    You can upload up to 10 files. Supported
                                    formats: JPG, PNG, GIF, PDF, DOC, DOCX, TXT.
                                </p>
                                <p
                                    v-if="form.errors.media"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.media }}
                                </p>
                            </div>

                            <!-- Submit Buttons -->
                            <div
                                class="flex items-center justify-end space-x-3"
                            >
                                <button
                                    type="button"
                                    @click="goBack"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    {{
                                        form.processing
                                            ? "Updating..."
                                            : "Update Content Post"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import FileUpload from "@/Components/UI/FileUpload.vue";

// Helper function to convert ISO datetime to date input format (YYYY-MM-DD)
const formatDateForInput = (isoDate) => {
    if (!isoDate) return "";
    const date = new Date(isoDate);
    return date.toISOString().split("T")[0];
};

// Helper function to format file size
const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const props = defineProps({
    contentPost: {
        type: Object,
        required: true,
    },
    clients: {
        type: Array,
        required: true,
    },
});

const platformOptions = [
    { value: "website", label: "Website" },
    { value: "facebook", label: "Facebook" },
    { value: "instagram", label: "Instagram" },
    { value: "twitter", label: "Twitter" },
    { value: "linkedin", label: "LinkedIn" },
    { value: "tiktok", label: "TikTok" },
    { value: "youtube", label: "YouTube" },
    { value: "pinterest", label: "Pinterest" },
    { value: "email", label: "Email" },
    { value: "other", label: "Other" },
];

const form = useForm({
    client_id:
        props.contentPost.client_id !== undefined
            ? props.contentPost.client_id
            : null,
    title: props.contentPost.title || "",
    platform: props.contentPost.platform
        ? Array.isArray(props.contentPost.platform)
            ? props.contentPost.platform
            : [props.contentPost.platform]
        : [],
    content_type: props.contentPost.content_type || "",
    description: props.contentPost.description || "",
    content_url: props.contentPost.content_url || "",
    image: null,
    post_count: props.contentPost.post_count || 1,
    scheduled_date: formatDateForInput(props.contentPost.scheduled_date),
    published_date: formatDateForInput(props.contentPost.published_date),
    status: props.contentPost.status || "draft",
    content_category: props.contentPost.content_category || "",
    tags: (() => {
        if (Array.isArray(props.contentPost.tags)) {
            return props.contentPost.tags;
        } else if (typeof props.contentPost.tags === "string") {
            try {
                // Try to parse as JSON first (in case it's a JSON string)
                const parsed = JSON.parse(props.contentPost.tags);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                // If JSON parsing fails, treat as comma-separated string
                return props.contentPost.tags
                    .split(",")
                    .map((tag) => tag.trim())
                    .filter((tag) => tag);
            }
        }
        return [];
    })(),
    notes: props.contentPost.notes || "",
    media: [],
});

// Enhanced Tag Handling
const tagInput = ref("");
const tagInputRef = ref(null);
const isTagInputFocused = ref(false);
const currentTagPreview = ref("");
const tagSuggestions = ref([
    "social-media",
    "blog",
    "video",
    "image",
    "campaign",
    "promotional",
    "educational",
    "entertainment",
]);

// Computed properties
const currentTagsArray = computed(() => {
    if (Array.isArray(form.tags)) {
        return form.tags.filter((tag) => tag && tag.trim());
    }
    return [];
});

// Ensure client_id is properly formatted as string for select binding
const formattedClientId = computed({
    get: () => (form.client_id ? String(form.client_id) : ""),
    set: (value) => {
        form.client_id = value ? Number(value) : null;
    },
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

const handleFileError = (error) => {
    form.errors.media = error;
};

const submitForm = () => {
    // Use FormData for proper file upload handling (similar to Create.vue)
    const formData = new FormData();

    // Add all form fields - ensure client_id is always included even if null
    Object.keys(form.data()).forEach((key) => {
        if (key === "media") {
            // Handle media file uploads (multiple files)
            form.media.forEach((file, index) => {
                formData.append(`media[${index}]`, file);
            });
        } else if (key === "image" && form.image instanceof File) {
            // Handle single image file upload
            formData.append(key, form.image);
        } else if (Array.isArray(form[key])) {
            // Handle arrays (like platform)
            form[key].forEach((item, index) => {
                formData.append(`${key}[${index}]`, item);
            });
        } else if (key === "client_id") {
            // Special handling for client_id - only include if not null
            if (
                form.client_id !== null &&
                form.client_id !== undefined &&
                form.client_id !== ""
            ) {
                formData.append(key, form.client_id);
            }
        } else {
            // Handle regular fields
            const value = form[key] === null ? "" : form[key];
            if (value !== undefined) {
                formData.append(key, value);
            }
        }
    });

    // Convert tags array to JSON string for proper handling
    if (Array.isArray(form.tags)) {
        formData.append("tags", JSON.stringify(form.tags));
    }

    // Use FormData for the put request - let browser set Content-Type automatically
    form.put(
        route("content.web.update", props.contentPost.id),
        {
            data: formData,
            // Remove manual Content-Type header - browser sets it automatically with boundary
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                console.log("Content post updated successfully");
                router.visit(route("content.web.show", props.contentPost.id));
            },
            onError: (errors) => {
                console.error("Error updating content post:", errors);
                if (errors.client_id) {
                    console.error("Client ID error:", errors.client_id);
                }
                if (errors.content_type) {
                    console.error("Content Type error:", errors.content_type);
                }
            },
        }
    );
};

const goBack = () => {
    router.visit(route("content.web.show", props.contentPost.id));
};
</script>
