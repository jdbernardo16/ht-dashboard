<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Content Post
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
                                    v-if="errors.client_id"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.client_id }}
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
                                    v-if="errors.title"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.title }}
                                </p>
                            </div>

                            <!-- Platform -->
                            <div>
                                <label
                                    for="platform"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Platform *
                                </label>
                                <select
                                    id="platform"
                                    v-model="form.platform"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select a platform</option>
                                    <option value="facebook">Facebook</option>
                                    <option value="instagram">Instagram</option>
                                    <option value="twitter">Twitter</option>
                                    <option value="linkedin">LinkedIn</option>
                                    <option value="tiktok">TikTok</option>
                                    <option value="youtube">YouTube</option>
                                    <option value="pinterest">Pinterest</option>
                                    <option value="other">Other</option>
                                </select>
                                <p
                                    v-if="errors.platform"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.platform }}
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
                                    v-if="errors.content_type"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.content_type }}
                                </p>
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
                                    v-if="errors.content_url"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.content_url }}
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
                                    v-if="errors.post_count"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.post_count }}
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
                                    v-if="errors.scheduled_date"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.scheduled_date }}
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
                                    v-if="errors.published_date"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.published_date }}
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
                                    v-if="errors.status"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.status }}
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
                                    v-if="errors.content_category"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.content_category }}
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
                                <input
                                    type="text"
                                    id="tags"
                                    v-model="form.tags"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <p
                                    v-if="errors.tags"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.tags }}
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
                                    v-if="errors.notes"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors.notes }}
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
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";

const props = defineProps({
    clients: {
        type: Array,
        required: true,
    },
});

const loading = ref(false);
const errors = ref({});

const form = useForm({
    client_id: "",
    title: "",
    platform: "",
    content_type: "",
    description: "",
    content_url: "",
    post_count: 1,
    scheduled_date: "",
    published_date: "",
    status: "draft",
    content_category: "",
    tags: "",
    notes: "",
});

const submitForm = () => {
    loading.value = true;
    errors.value = {};

    // Convert tags string to array
    const tagsArray = form.tags
        ? form.tags
              .split(",")
              .map((tag) => tag.trim())
              .filter((tag) => tag)
        : [];

    // Transform the form data
    form.transform((data) => ({
        ...data,
        tags: tagsArray,
    })).post(route("content.store"), {
        onSuccess: () => {
            router.visit(route("content.index"));
        },
        onError: (error) => {
            errors.value = error;
        },
        onFinish: () => {
            loading.value = false;
        },
    });
};

const goBack = () => {
    router.visit(route("content.index"));
};
</script>
