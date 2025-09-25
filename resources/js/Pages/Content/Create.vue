<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Content Post
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Client -->
                            <div>
                                <InputLabel for="client_id" value="Client *" />
                                <select
                                    id="client_id"
                                    v-model="form.client_id"
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
                                                    'opacity-50': loading,
                                                }"
                                                :disabled="loading"
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
                                                :disabled="loading"
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
                                <InputLabel for="notes" value="Notes" />
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{
                                        'border-red-500': form.errors.notes,
                                    }"
                                ></textarea>
                                <InputError
                                    :message="form.errors.notes"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Meta Description -->
                            <div>
                                <InputLabel
                                    for="meta_description"
                                    value="Meta Description"
                                />
                                <textarea
                                    id="meta_description"
                                    v-model="form.meta_description"
                                    rows="2"
                                    maxlength="255"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{
                                        'border-red-500':
                                            form.errors.meta_description,
                                    }"
                                ></textarea>
                                <InputError
                                    :message="form.errors.meta_description"
                                    class="mt-2"
                                />
                                <p class="text-xs text-gray-500 mt-1">
                                    {{
                                        form.meta_description
                                            ? form.meta_description.length
                                            : 0
                                    }}/255 characters
                                </p>
                            </div>

                            <!-- SEO Keywords -->
                            <div>
                                <InputLabel
                                    for="seo_keywords"
                                    value="SEO Keywords"
                                />
                                <TextInput
                                    id="seo_keywords"
                                    v-model="form.seo_keywords"
                                    type="text"
                                    maxlength="255"
                                    class="mt-1 block w-full"
                                    :class="{
                                        'border-red-500':
                                            form.errors.seo_keywords,
                                    }"
                                />
                                <InputError
                                    :message="form.errors.seo_keywords"
                                    class="mt-2"
                                />
                                <p class="text-xs text-gray-500 mt-1">
                                    Separate keywords with commas
                                </p>
                            </div>

                            <!-- Image Upload -->
                            <div>
                                <InputLabel value="Upload Image" class="mb-2" />
                                <BaseFileUploader
                                    v-model="form.image"
                                    label="Main Image"
                                    :acceptTypes="['image']"
                                    :multiple="false"
                                    :maxSize="10"
                                    description="Drag & drop image here or click to browse"
                                    :withPreview="true"
                                    :required="false"
                                    :error="form.errors.image"
                                />
                                <p class="text-xs text-gray-500 mt-2">
                                    Upload a single image file. Supported
                                    formats: JPG, PNG, GIF, WebP (max 10MB)
                                </p>
                            </div>

                            <!-- Media Upload -->
                            <div>
                                <InputLabel
                                    value="Upload Additional Files"
                                    class="mb-2"
                                />
                                <BaseFileUploader
                                    v-model="form.media"
                                    label="Additional Files"
                                    :acceptTypes="[
                                        'image',
                                        'pdf',
                                        'xlsx',
                                        'csv',
                                    ]"
                                    :multiple="true"
                                    :maxSize="10"
                                    description="Drag & drop files here or click to browse"
                                    :withPreview="false"
                                    :required="false"
                                    :error="form.errors.media"
                                />
                                <p class="text-xs text-gray-500 mt-2">
                                    You can upload up to 10 files. Supported
                                    formats: JPG, PNG, GIF, PDF, Excel, CSV (max
                                    10MB each)
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
                                    :disabled="loading"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    {{
                                        loading
                                            ? "Creating..."
                                            : "Create Content Post"
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
import { computed, ref, watch } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import BaseFileUploader from "@/Components/Shared/Fields/BaseFileUploader.vue";

const props = defineProps({
    clients: {
        type: Array,
        required: true,
    },
});

const loading = ref(false);

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
    client_id: "",
    title: "",
    platform: [],
    content_type: "",
    description: "",
    content_url: "",
    image: null, // This will store either File object or FileData object
    post_count: 1,
    scheduled_date: "",
    published_date: "",
    status: "draft",
    content_category: "",
    tags: [],
    notes: "",
    meta_description: "",
    seo_keywords: "",
    media: [],
});

// Watch for changes to the image field and preserve both File object and preview
watch(
    () => form.image,
    (newImage) => {
        if (
            newImage &&
            typeof newImage === "object" &&
            newImage.file instanceof File
        ) {
            // If it's a FileData object with a file property, preserve the entire object
            // to maintain both the file and preview data
            // The form.image already contains the FileData object, so no need to modify it
            console.log(
                "FileData object received with file:",
                newImage.file.name,
                "and preview:",
                newImage.preview ? "available" : "not available"
            );
        }
    },
    { deep: true }
);

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

const submitForm = () => {
    loading.value = true;

    // Create a copy of form data for processing
    const formDataToProcess = { ...form.data() };

    // Debug: Log all form data before processing
    console.log("Original form data:", formDataToProcess);

    // Fix client_id field handling - ensure it's not sending empty strings when null is expected
    if (
        formDataToProcess.client_id === "" ||
        formDataToProcess.client_id === null
    ) {
        formDataToProcess.client_id = null;
        console.log("Client ID set to null");
    } else {
        // Convert to number if it's a string
        formDataToProcess.client_id = parseInt(formDataToProcess.client_id);
        console.log(
            "Client ID converted to number:",
            formDataToProcess.client_id
        );
    }

    // Fix date formatting for published_date field to ensure YYYY-MM-DD format
    if (formDataToProcess.published_date) {
        // If the date is already in YYYY-MM-DD format, keep it as is
        // Otherwise, try to parse and reformat it
        const date = new Date(formDataToProcess.published_date);
        if (!isNaN(date.getTime())) {
            // Format as YYYY-MM-DD
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const day = String(date.getDate()).padStart(2, "0");
            formDataToProcess.published_date = `${year}-${month}-${day}`;
            console.log(
                "Published date formatted:",
                formDataToProcess.published_date
            );
        }
    }

    // Fix date formatting for scheduled_date field to ensure YYYY-MM-DD format
    if (formDataToProcess.scheduled_date) {
        // If the date is already in YYYY-MM-DD format, keep it as is
        // Otherwise, try to parse and reformat it
        const date = new Date(formDataToProcess.scheduled_date);
        if (!isNaN(date.getTime())) {
            // Format as YYYY-MM-DD
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const day = String(date.getDate()).padStart(2, "0");
            formDataToProcess.scheduled_date = `${year}-${month}-${day}`;
            console.log(
                "Scheduled date formatted:",
                formDataToProcess.scheduled_date
            );
        }
    }

    // Fix nullable fields (content_url) to convert empty strings to null
    if (formDataToProcess.content_url === "") {
        formDataToProcess.content_url = null;
        console.log("Content URL set to null");
    }

    // Convert File objects to FormData for proper file upload
    const formData = new FormData();

    // Add all processed form fields
    Object.keys(formDataToProcess).forEach((key) => {
        if (key === "media") {
            // Handle media file uploads (multiple files)
            if (Array.isArray(formDataToProcess.media)) {
                formDataToProcess.media.forEach((fileData, index) => {
                    if (fileData?.file instanceof File) {
                        formData.append(`media[${index}]`, fileData.file);
                    }
                });
            }
        } else if (key === "image") {
            console.log("Processing image field:", formDataToProcess.image);
            // Handle single image file upload - extract File from FileData object
            if (
                formDataToProcess.image &&
                formDataToProcess.image.file instanceof File
            ) {
                formData.append(key, formDataToProcess.image.file);
                console.log(
                    "Appended image file:",
                    formDataToProcess.image.file.name
                );
            } else if (formDataToProcess.image instanceof File) {
                formData.append(key, formDataToProcess.image);
                console.log(
                    "Appended direct File object:",
                    formDataToProcess.image.name
                );
            } else {
                console.log(
                    "Image field is not a valid File object or FileData:",
                    formDataToProcess.image
                );
            }
        } else if (Array.isArray(formDataToProcess[key])) {
            // Handle arrays (like platform and tags) - ensure they're properly formatted as JSON
            if (key === "platform" || key === "tags") {
                formData.append(key, JSON.stringify(formDataToProcess[key]));
            } else {
                formDataToProcess[key].forEach((item, index) => {
                    formData.append(`${key}[${index}]`, item);
                });
            }
        } else {
            // Handle null values properly
            if (formDataToProcess[key] === null) {
                formData.append(key, "");
            } else {
                formData.append(key, formDataToProcess[key]);
            }
        }
    });

    // Debug: Log what's in the formData for key fields
    console.log("FormData client_id:", formData.get("client_id"));
    console.log("FormData published_date:", formData.get("published_date"));
    console.log("FormData scheduled_date:", formData.get("scheduled_date"));
    console.log("FormData content_url:", formData.get("content_url"));
    console.log("FormData image field:", formData.get("image"));
    console.log("Form image field value:", formDataToProcess.image);
    console.log("Form image field type:", typeof formDataToProcess.image);

    // Log the final form data for debugging
    console.log("Final form data being submitted:");
    for (let pair of formData.entries()) {
        console.log(pair[0] + ":", pair[1]);
    }

    // Use FormData for the post request - let browser set Content-Type automatically
    form.post(route("content.web.store"), {
        data: formData,
        // Remove manual Content-Type header - browser sets it automatically with boundary
        onSuccess: () => {
            // The controller now directly renders the Show page with the new content post
            // No additional redirect needed as Inertia handles the page transition
        },
        onError: (error) => {
            console.error("Form submission error:", error);
        },
        onFinish: () => {
            loading.value = false;
        },
    });
};

const goBack = () => {
    router.visit(route("content.web.index"));
};
</script>
