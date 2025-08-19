<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Content Post Details
                </h2>

                <div class="flex space-x-2">
                    <button
                        @click="editContentPost"
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Edit
                    </button>

                    <button
                        @click="deleteContentPost"
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
const formatPlatform = (platform) =>
    ({
        facebook: "Facebook",
        instagram: "Instagram",
        twitter: "Twitter",
        linkedin: "LinkedIn",
        tiktok: "TikTok",
        youtube: "YouTube",
        pinterest: "Pinterest",
        other: "Other",
    }[platform] || platform);

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

const daysSinceCreated = computed(() => {
    const created = new Date(props.contentPost.created_at);
    return Math.ceil((Date.now() - created.getTime()) / (1000 * 60 * 60 * 24));
});
</script>
