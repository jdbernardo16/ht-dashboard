<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        {{ props.contentPost.title }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Content ID: #{{ props.contentPost.id }} • Created
                        {{ formatDateTime(props.contentPost.created_at) }}
                    </p>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
                    <button
                        @click="editContentPost"
                        class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 shadow-sm"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                            />
                        </svg>
                        Edit Content
                    </button>
                    <button
                        @click="deleteContentPost"
                        class="inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                        Delete
                    </button>
                    <button
                        @click="goBack"
                        class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                        Back
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Status and Platform Bar -->
            <div
                class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 mb-8 border border-purple-100"
            >
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-600"
                                >Status:</span
                            >
                            <span
                                :class="
                                    getStatusClass(props.contentPost.status)
                                "
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                            >
                                {{ formatStatus(props.contentPost.status) }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-600"
                                >Platform:</span
                            >
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800"
                            >
                                {{ formatPlatform(props.contentPost.platform) }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-600"
                                >Type:</span
                            >
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
                            >
                                {{
                                    formatContentType(
                                        props.contentPost.content_type
                                    )
                                }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Engagement Rate</p>
                        <p class="text-2xl font-bold text-purple-900">
                            {{ props.contentPost.engagement_rate || 0 }}%
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content Area -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Content Information Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4"
                        >
                            <h2 class="text-xl font-semibold text-white">
                                Content Information
                            </h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-6">
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Title
                                    </dt>
                                    <dd
                                        class="text-xl font-semibold text-gray-900"
                                    >
                                        {{ props.contentPost.title }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Client
                                    </dt>
                                    <dd class="text-lg text-gray-900">
                                        {{
                                            props.contentPost.client?.first_name
                                        }}
                                        {{
                                            props.contentPost.client?.last_name
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Description
                                    </dt>
                                    <dd
                                        class="text-gray-900 leading-relaxed bg-gray-50 p-4 rounded-lg"
                                    >
                                        {{
                                            props.contentPost.description ||
                                            "No description provided"
                                        }}
                                    </dd>
                                </div>

                                <div v-if="props.contentPost.notes">
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Notes
                                    </dt>
                                    <dd
                                        class="text-gray-900 leading-relaxed bg-blue-50 p-4 rounded-lg border border-blue-200"
                                    >
                                        {{ props.contentPost.notes }}
                                    </dd>
                                </div>

                                <div
                                    v-if="
                                        props.contentPost.tags &&
                                        props.contentPost.tags.length > 0
                                    "
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-3"
                                    >
                                        Tags
                                    </dt>
                                    <dd class="flex flex-wrap gap-2">
                                        <span
                                            v-for="tag in props.contentPost
                                                .tags"
                                            :key="tag"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 border border-purple-200"
                                        >
                                            <svg
                                                class="w-3 h-3 mr-1"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            {{ tag }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- SEO Section -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-green-500 to-teal-600 px-6 py-4"
                        >
                            <h2 class="text-xl font-semibold text-white">
                                SEO Information
                            </h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-6">
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Meta Description
                                    </dt>
                                    <dd
                                        class="text-gray-900 leading-relaxed bg-gray-50 p-4 rounded-lg"
                                    >
                                        {{
                                            props.contentPost
                                                .meta_description ||
                                            "No meta description provided"
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        SEO Keywords
                                    </dt>
                                    <dd
                                        class="text-gray-900 leading-relaxed bg-gray-50 p-4 rounded-lg"
                                    >
                                        {{
                                            props.contentPost.seo_keywords ||
                                            "No SEO keywords provided"
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Content URL
                                    </dt>
                                    <dd class="text-gray-900">
                                        <a
                                            v-if="props.contentPost.content_url"
                                            :href="
                                                props.contentPost.content_url
                                            "
                                            target="_blank"
                                            class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center"
                                        >
                                            <svg
                                                class="w-4 h-4 mr-2"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                                />
                                            </svg>
                                            {{ props.contentPost.content_url }}
                                        </a>
                                        <span v-else class="text-gray-500"
                                            >No URL provided</span
                                        >
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Image Display -->
                    <div
                        v-if="props.contentPost.image_url"
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4"
                        >
                            <h2 class="text-xl font-semibold text-white">
                                Main Image
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="bg-gray-50 rounded-lg p-6 text-center">
                                <img
                                    :src="props.contentPost.image_url"
                                    alt="Content post image"
                                    class="max-w-full h-auto max-h-64 rounded-lg border shadow-sm mx-auto"
                                    onerror="this.style.display='none'"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Media Files Display -->
                    <div
                        v-if="
                            props.contentPost.media &&
                            props.contentPost.media.length > 0
                        "
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-yellow-500 to-orange-600 px-6 py-4"
                        >
                            <h2 class="text-xl font-semibold text-white">
                                Additional Files
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <div
                                    v-for="media in props.contentPost.media"
                                    :key="media.id"
                                    class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <svg
                                                class="w-8 h-8 text-gray-400"
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
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-sm font-medium text-gray-900 truncate"
                                            >
                                                {{ media.original_name }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{
                                                    formatFileSize(
                                                        media.file_size
                                                    )
                                                }}
                                                • {{ media.mime_type }}
                                            </p>
                                        </div>
                                    </div>
                                    <a
                                        :href="media.url"
                                        target="_blank"
                                        class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200"
                                    >
                                        <svg
                                            class="w-4 h-4 mr-1"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                            />
                                        </svg>
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Content Details Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6"
                    >
                        <div
                            class="bg-gradient-to-r from-gray-700 to-gray-900 px-6 py-4"
                        >
                            <h3 class="text-lg font-semibold text-white">
                                Content Details
                            </h3>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Scheduled Date
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{
                                            props.contentPost.scheduled_date ||
                                            "Not scheduled"
                                        }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Published Date
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{
                                            props.contentPost.published_date ||
                                            "Not published"
                                        }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Post Count
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ props.contentPost.post_count || 0 }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Content Category
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{
                                            props.contentPost
                                                .content_category ||
                                            "Not specified"
                                        }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Created By
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{
                                            props.contentPost.user?.name ||
                                            "System"
                                        }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3 border-b border-gray-100"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Created At
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{
                                            formatDateTime(
                                                props.contentPost.created_at
                                            )
                                        }}
                                    </dd>
                                </div>

                                <div
                                    class="flex justify-between items-center py-3"
                                >
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Last Updated
                                    </dt>
                                    <dd
                                        class="text-sm font-medium text-gray-900"
                                    >
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

                    <!-- Content Statistics Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-green-500 to-teal-600 px-6 py-4"
                        >
                            <h3 class="text-lg font-semibold text-white">
                                Content Statistics
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div
                                    class="text-center p-4 bg-green-50 rounded-lg border border-green-200"
                                >
                                    <p
                                        class="text-sm font-medium text-green-600 mb-1"
                                    >
                                        Engagement Rate
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-green-900"
                                    >
                                        {{
                                            props.contentPost.engagement_rate ||
                                            0
                                        }}%
                                    </p>
                                </div>

                                <div
                                    class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200"
                                >
                                    <p
                                        class="text-sm font-medium text-blue-600 mb-1"
                                    >
                                        Total Engagement
                                    </p>
                                    <p class="text-2xl font-bold text-blue-900">
                                        {{
                                            props.contentPost
                                                .total_engagement || 0
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="text-center p-4 bg-purple-50 rounded-lg border border-purple-200"
                                >
                                    <p
                                        class="text-sm font-medium text-purple-600 mb-1"
                                    >
                                        Days Since Created
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-purple-900"
                                    >
                                        {{ daysSinceCreated }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Engagement Metrics -->
                    <div
                        v-if="props.contentPost.engagement_metrics"
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4"
                        >
                            <h3 class="text-lg font-semibold text-white">
                                Engagement Metrics
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    class="text-center p-3 bg-blue-50 rounded-lg border border-blue-200"
                                >
                                    <svg
                                        class="w-5 h-5 text-blue-600 mx-auto mb-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                    </svg>
                                    <p class="text-xs text-blue-600 mb-1">
                                        Views
                                    </p>
                                    <p class="text-lg font-bold text-blue-900">
                                        {{
                                            props.contentPost.engagement_metrics
                                                .views || 0
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="text-center p-3 bg-red-50 rounded-lg border border-red-200"
                                >
                                    <svg
                                        class="w-5 h-5 text-red-600 mx-auto mb-1"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <p class="text-xs text-red-600 mb-1">
                                        Likes
                                    </p>
                                    <p class="text-lg font-bold text-red-900">
                                        {{
                                            props.contentPost.engagement_metrics
                                                .likes || 0
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="text-center p-3 bg-green-50 rounded-lg border border-green-200"
                                >
                                    <svg
                                        class="w-5 h-5 text-green-600 mx-auto mb-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                        />
                                    </svg>
                                    <p class="text-xs text-green-600 mb-1">
                                        Comments
                                    </p>
                                    <p class="text-lg font-bold text-green-900">
                                        {{
                                            props.contentPost.engagement_metrics
                                                .comments || 0
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="text-center p-3 bg-purple-50 rounded-lg border border-purple-200"
                                >
                                    <svg
                                        class="w-5 h-5 text-purple-600 mx-auto mb-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m9.032 4.026a9.001 9.001 0 01-7.432 0m9.032-4.026A9.001 9.001 0 0112 3c-4.474 0-8.268 3.12-9.032 7.326m0 0A9.001 9.001 0 0012 21c4.474 0 8.268-3.12 9.032-7.326"
                                        />
                                    </svg>
                                    <p class="text-xs text-purple-600 mb-1">
                                        Shares
                                    </p>
                                    <p
                                        class="text-lg font-bold text-purple-900"
                                    >
                                        {{
                                            props.contentPost.engagement_metrics
                                                .shares || 0
                                        }}
                                    </p>
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
