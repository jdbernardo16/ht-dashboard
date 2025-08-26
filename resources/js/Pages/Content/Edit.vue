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
                                <label
                                    for="client_id"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Client *
                                </label>
                                <select
                                    id="client_id"
                                    v-model="form.client_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                <p
                                    v-if="form.errors.client_id"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.client_id }}
                                </p>
                            </div>

                            <!-- Title -->
                            <div>
                                <label
                                    for="title"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Title *
                                </label>
                                <input
                                    type="text"
                                    id="title"
                                    v-model="form.title"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.title"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <!-- Platform -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Platforms *
                                </label>
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
                                <p
                                    v-if="form.errors.platform"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.platform }}
                                </p>
                            </div>

                            <!-- Content Type -->
                            <div>
                                <label
                                    for="content_type"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Content Type *
                                </label>
                                <select
                                    id="content_type"
                                    v-model="form.content_type"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                <p
                                    v-if="form.errors.content_type"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.content_type }}
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label
                                    for="description"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Description
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <p
                                    v-if="form.errors.description"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Content URL -->
                            <div>
                                <label
                                    for="content_url"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Content URL
                                </label>
                                <input
                                    type="url"
                                    id="content_url"
                                    v-model="form.content_url"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.content_url"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.content_url }}
                                </p>
                            </div>

                            <!-- Post Count -->
                            <div>
                                <label
                                    for="post_count"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Post Count
                                </label>
                                <input
                                    type="number"
                                    id="post_count"
                                    v-model="form.post_count"
                                    min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.post_count"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.post_count }}
                                </p>
                            </div>

                            <!-- Scheduled Date -->
                            <div>
                                <label
                                    for="scheduled_date"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Scheduled Date
                                </label>
                                <input
                                    type="date"
                                    id="scheduled_date"
                                    v-model="form.scheduled_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.scheduled_date"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.scheduled_date }}
                                </p>
                            </div>

                            <!-- Published Date -->
                            <div>
                                <label
                                    for="published_date"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Published Date
                                </label>
                                <input
                                    type="date"
                                    id="published_date"
                                    v-model="form.published_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.published_date"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.published_date }}
                                </p>
                            </div>

                            <!-- Status -->
                            <div>
                                <label
                                    for="status"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Status *
                                </label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                                <p
                                    v-if="form.errors.status"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.status }}
                                </p>
                            </div>

                            <!-- Content Category -->
                            <div>
                                <label
                                    for="content_category"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Content Category
                                </label>
                                <input
                                    type="text"
                                    id="content_category"
                                    v-model="form.content_category"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="form.errors.content_category"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.content_category }}
                                </p>
                            </div>

                            <!-- Tags -->
                            <div>
                                <label
                                    for="tags"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Tags (comma separated)
                                </label>
                                <input type="text" id="tags" />
                                <p
                                    v-if="form.errors.tags"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.tags }}
                                </p>
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

                            <!-- Media Upload -->
                            <div>
                                <InputLabel value="Upload Files" class="mb-2" />
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
import { computed } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import FileUpload from "@/Components/UI/FileUpload.vue";

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
    client_id: props.contentPost.client_id || "",
    title: props.contentPost.title || "",
    platform: Array.isArray(props.contentPost.platform)
        ? props.contentPost.platform
        : props.contentPost.platform
        ? [props.contentPost.platform]
        : [],
    content_type: props.contentPost.content_type || "",
    description: props.contentPost.description || "",
    content_url: props.contentPost.content_url || "",
    post_count: props.contentPost.post_count || 1,
    scheduled_date: props.contentPost.scheduled_date || "",
    published_date: props.contentPost.published_date || "",
    status: props.contentPost.status || "draft",
    content_category: props.contentPost.content_category || "",
    tags: props.contentPost.tags ? props.contentPost.tags.join(", ") : "",
    notes: props.contentPost.notes || "",
    media: [],
});

const handleFileError = (error) => {
    form.errors.media = error;
};

const submitForm = () => {
    // Convert tags string to array
    const tagsArray = form.tags
        ? form.tags
              .split(",")
              .map((tag) => tag.trim())
              .filter((tag) => tag)
        : [];

    // Convert File objects to FormData for proper file upload
    const formData = new FormData();

    // Add all form fields
    Object.keys(form.data()).forEach((key) => {
        if (key === "media") {
            // Handle file uploads
            form.media.forEach((file, index) => {
                formData.append(`media[${index}]`, file);
            });
        } else if (Array.isArray(form[key])) {
            // Handle arrays (like platform)
            form[key].forEach((item, index) => {
                formData.append(`${key}[${index}]`, item);
            });
        } else {
            formData.append(key, form[key]);
        }
    });

    // Add tags array
    tagsArray.forEach((tag, index) => {
        formData.append(`tags[${index}]`, tag);
    });

    form.put(`/content/${props.contentPost.id}`, {
        data: formData,
        headers: {
            "Content-Type": "multipart/form-data",
        },
        preserveScroll: true,
        onSuccess: () => {
            router.visit(`/content/${props.contentPost.id}`);
        },
        onError: (errors) => {
            console.error("Error updating content post:", errors);
        },
    });
};

const goBack = () => {
    router.visit(`/content/${props.contentPost.id}`);
};
</script>
