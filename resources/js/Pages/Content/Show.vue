<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Content Post Details
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Content Post Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left column -->
                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Content Post Information
                                </h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Title
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ props.contentPost.title }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Client
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                props.contentPost.client
                                                    ?.name || "N/A"
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Platform
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatPlatform(
                                                    props.contentPost.platform
                                                )
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Content Type
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatContentType(
                                                    props.contentPost
                                                        .content_type
                                                )
                                            }}
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
                                                        props.contentPost.status
                                                    )
                                                "
                                                class="px-2 py-1 text-xs font-medium rounded-full"
                                            >
                                                {{
                                                    formatStatus(
                                                        props.contentPost.status
                                                    )
                                                }}
                                            </span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Scheduled Date
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                props.contentPost
                                                    .scheduled_date ||
                                                "Not scheduled"
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Published Date
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                props.contentPost
                                                    .published_date ||
                                                "Not published"
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Content URL
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <a
                                                v-if="
                                                    props.contentPost
                                                        .content_url
                                                "
                                                :href="
                                                    props.contentPost
                                                        .content_url
                                                "
                                                target="_blank"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                {{
                                                    props.contentPost
                                                        .content_url
                                                }}
                                            </a>
                                            <span v-else>No URL provided</span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Post Count
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                props.contentPost.post_count ||
                                                0
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Content Category
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                props.contentPost
                                                    .content_category ||
                                                "Not specified"
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Tags
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <span
                                                v-for="tag in props.contentPost
                                                    .tags"
                                                :key="tag"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-1"
                                            >
                                                {{ tag }}
                                            </span>
                                            <span
                                                v-if="
                                                    !props.contentPost.tags
                                                        ?.length
                                                "
                                            >
                                                No tags
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Right column -->
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
                                            Description
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                props.contentPost.description ||
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
                                                props.contentPost.notes ||
                                                "No notes provided"
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Meta Description
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                props.contentPost
                                                    .meta_description ||
                                                "No meta description provided"
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            SEO Keywords
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                props.contentPost
                                                    .seo_keywords ||
                                                "No SEO keywords provided"
                                            }}
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
                                                props.contentPost.user?.name ||
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
                                                    props.contentPost.created_at
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
                                                    props.contentPost.updated_at
                                                )
                                            }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Image Display -->
                        <div v-if="props.contentPost.image" class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Main Image
                            </h3>
                            <div class="bg-gray-50 rounded-lg p-6">
                                <img
                                    :src="props.contentPost.image"
                                    alt="Content post image"
                                    class="max-w-full h-auto max-h-64 rounded-lg border shadow-sm"
                                    onerror="this.style.display='none'"
                                />
                            </div>
                        </div>

                        <!-- Media Files Display -->
                        <div
                            v-if="
                                props.contentPost.media &&
                                props.contentPost.media.length > 0
                            "
                            class="mt-8"
                        >
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Additional Files
                            </h3>
                            <div class="bg-gray-50 rounded-lg p-6">
                                <div class="space-y-3">
                                    <div
                                        v-for="media in props.contentPost.media"
                                        :key="media.id"
                                        class="flex items-center justify-between p-3 bg-white rounded border shadow-sm"
                                    >
                                        <div
                                            class="flex items-center space-x-3"
                                        >
                                            <svg
                                                class="h-5 w-5 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                ></path>
                                            </svg>
                                            <div>
                                                <p
                                                    class="text-sm font-medium text-gray-900"
                                                >
                                                    {{ media.original_name }}
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
                                        <a
                                            :href="media.file_path"
                                            target="_blank"
                                            class="text-indigo-600 hover:text-indigo-800 text-sm font-medium"
                                        >
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Card -->
                        <div class="mt-8 bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Content Post Statistics
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Engagement Rate
                                    </dt>
                                    <dd
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        {{
                                            props.contentPost.engagement_rate ||
                                            0
                                        }}%
                                    </dd>
                                </div>

                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Total Engagement
                                    </dt>
                                    <dd
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        {{
                                            props.contentPost
                                                .total_engagement || 0
                                        }}
                                    </dd>
                                </div>

                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Days Since Created
                                    </dt>
                                    <dd
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        {{ daysSinceCreated }}
                                    </dd>
                                </div>

                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Post Status
                                    </dt>
                                    <dd class="mt-1">
                                        <span
                                            :class="
                                                getStatusClass(
                                                    props.contentPost.status
                                                )
                                            "
                                            class="px-2 py-1 text-xs font-medium rounded-full"
                                        >
                                            {{
                                                formatStatus(
                                                    props.contentPost.status
                                                )
                                            }}
                                        </span>
                                    </dd>
                                </div>
                            </div>
                        </div>

                        <!-- Engagement Metrics -->
                        <div
                            v-if="props.contentPost.engagement_metrics"
                            class="mt-8 bg-gray-50 rounded-lg p-6"
                        >
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Engagement Metrics
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Views
                                    </dt>
                                    <dd
                                        class="mt-1 text-xl font-semibold text-gray-900"
                                    >
                                        {{
                                            props.contentPost.engagement_metrics
                                                .views || 0
                                        }}
                                    </dd>
                                </div>

                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Likes
                                    </dt>
                                    <dd
                                        class="mt-1 text-xl font-semibold text-gray-900"
                                    >
                                        {{
                                            props.contentPost.engagement_metrics
                                                .likes || 0
                                        }}
                                    </dd>
                                </div>

                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Comments
                                    </dt>
                                    <dd
                                        class="mt-1 text-xl font-semibold text-gray-900"
                                    >
                                        {{
                                            props.contentPost.engagement_metrics
                                                .comments || 0
                                        }}
                                    </dd>
                                </div>

                                <div class="bg-white rounded-lg p-4 shadow">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Shares
                                    </dt>
                                    <dd
                                        class="mt-1 text-xl font-semibold text-gray-900"
                                    >
                                        {{
                                            props.contentPost.engagement_metrics
                                                .shares || 0
                                        }}
                                    </dd>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div
                            class="mt-8 flex items-center justify-end space-x-3"
                        >
                            <button
                                @click="goBack"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Back
                            </button>
                            <button
                                @click="editContentPost"
                                class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 border border-transparent rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteContentPost"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            >
                                Delete
                            </button>
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
    contentPost: {
        type: Object,
        required: true,
    },
});

const editContentPost = () => {
    router.visit(`/content/${props.contentPost.id}/edit`);
};

const deleteContentPost = () => {
    if (confirm("Are you sure you want to delete this content post?")) {
        router.delete(`/content/${props.contentPost.id}`, {
            preserveScroll: true,
            onSuccess: () => router.visit("/content"),
            onError: (errors) => {
                console.error("Error deleting content post:", errors);
                if (errors.message?.includes("403")) {
                    alert(
                        "You don't have permission to delete this content post."
                    );
                }
            },
        });
    }
};

const goBack = () => router.visit("/content");

/* ---- Utility Functions ---- */
const formatPlatform = (platform) => {
    if (Array.isArray(platform)) {
        return platform
            .map(
                (p) =>
                    ({
                        facebook: "Facebook",
                        instagram: "Instagram",
                        twitter: "Twitter",
                        linkedin: "LinkedIn",
                        tiktok: "TikTok",
                        youtube: "YouTube",
                        pinterest: "Pinterest",
                        other: "Other",
                    }[p] || p)
            )
            .join(", ");
    }

    return (
        {
            facebook: "Facebook",
            instagram: "Instagram",
            twitter: "Twitter",
            linkedin: "LinkedIn",
            tiktok: "TikTok",
            youtube: "YouTube",
            pinterest: "Pinterest",
            other: "Other",
        }[platform] || platform
    );
};

const formatContentType = (type) =>
    ({
        post: "Post",
        story: "Story",
        reel: "Reel",
        video: "Video",
        image: "Image",
        carousel: "Carousel",
        live: "Live",
        article: "Article",
    }[type] || type);

const formatStatus = (status) =>
    !status
        ? "Unknown"
        : status.charAt(0).toUpperCase() + status.slice(1).replace("_", " ");

const getStatusClass = (status) =>
    ({
        draft: "bg-gray-100 text-gray-800",
        scheduled: "bg-blue-100 text-blue-800",
        published: "bg-green-100 text-green-800",
        archived: "bg-red-100 text-red-800",
    }[status] || "bg-gray-100 text-gray-800");

const formatDateTime = (date) => new Date(date).toLocaleString();

// Helper function to format file size
const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const daysSinceCreated = computed(() => {
    const created = new Date(props.contentPost.created_at);
    return Math.ceil((Date.now() - created.getTime()) / (1000 * 60 * 60 * 24));
});
</script>
