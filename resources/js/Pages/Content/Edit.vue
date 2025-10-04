<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Edit Content Post
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Update the content details and scheduling information
                    </p>
                </div>

                <!-- Action buttons -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-2 sm:space-y-0 mt-4 lg:mt-0"
                >
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
                        Back to Content
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Form Container -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
            >
                <!-- Form Header -->
                <div
                    class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4"
                >
                    <h2 class="text-xl font-semibold text-white">
                        Content Details
                    </h2>
                    <p class="text-purple-100 text-sm mt-1">
                        Update the content information and scheduling details
                    </p>
                </div>

                <div class="p-6 text-gray-900">
                    <form @submit.prevent="submitForm" class="space-y-8">
                        <!-- Basic Information Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-purple-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-purple-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Basic Information
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Enter the core content details
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <!-- Client -->
                                <div>
                                    <InputLabel
                                        for="client_id"
                                        value="Client *"
                                    />
                                    <select
                                        id="client_id"
                                        v-model="form.client_id"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 px-3 py-2 transition-colors"
                                        :class="{
                                            'border-red-500':
                                                form.errors.client_id,
                                        }"
                                    >
                                        <option value="">
                                            Select a client
                                        </option>
                                        <option
                                            v-for="client in clients"
                                            :key="client.id"
                                            :value="client.id"
                                        >
                                            {{ client.first_name }}
                                            {{ client.last_name }}
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
                                    <InputLabel
                                        value="Platforms *"
                                        class="mb-3"
                                    />
                                    <div
                                        class="grid grid-cols-2 md:grid-cols-3 gap-4 bg-white p-4 rounded-lg border border-gray-200"
                                    >
                                        <div
                                            v-for="platformOption in platformOptions"
                                            :key="platformOption.value"
                                            class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-purple-300 transition-colors"
                                            :class="{
                                                'bg-purple-50 border-purple-300':
                                                    form.platform.includes(
                                                        platformOption.value
                                                    ),
                                            }"
                                        >
                                            <input
                                                type="checkbox"
                                                :id="`platform-${platformOption.value}`"
                                                :value="platformOption.value"
                                                v-model="form.platform"
                                                class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                                            />
                                            <label
                                                :for="`platform-${platformOption.value}`"
                                                class="ml-3 text-sm font-medium text-gray-700 cursor-pointer"
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
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 px-3 py-2 transition-colors"
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
                                        <option value="carousel">
                                            Carousel
                                        </option>
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
                                        value="Description *"
                                    />
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        required
                                        rows="4"
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 px-3 py-2 transition-colors"
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
                                        value="Content URL *"
                                    />
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
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
                                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                                                />
                                            </svg>
                                        </div>
                                        <TextInput
                                            id="content_url"
                                            v-model="form.content_url"
                                            type="url"
                                            required
                                            class="mt-1 block w-full pl-10"
                                            :class="{
                                                'border-red-500':
                                                    form.errors.content_url,
                                            }"
                                        />
                                    </div>
                                    <InputError
                                        :message="form.errors.content_url"
                                        class="mt-2"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Scheduling Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-purple-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-purple-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Scheduling
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Set dates and publishing status
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Scheduled Date -->
                                <div>
                                    <InputLabel
                                        for="scheduled_date"
                                        value="Scheduled Date *"
                                    />
                                    <TextInput
                                        id="scheduled_date"
                                        v-model="form.scheduled_date"
                                        type="date"
                                        required
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
                                        value="Published Date *"
                                    />
                                    <TextInput
                                        id="published_date"
                                        v-model="form.published_date"
                                        type="date"
                                        required
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
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 px-3 py-2 transition-colors"
                                        :class="{
                                            'border-red-500':
                                                form.errors.status,
                                        }"
                                    >
                                        <option value="draft">Draft</option>
                                        <option value="scheduled">
                                            Scheduled
                                        </option>
                                        <option value="published">
                                            Published
                                        </option>
                                        <option value="archived">
                                            Archived
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.status"
                                        class="mt-2"
                                    />
                                </div>
                            </div>

                            <!-- Post Count -->
                            <div class="mt-6">
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
                        </div>

                        <!-- Content Classification Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-purple-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-purple-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Content Classification
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Categorize and tag your content
                                    </p>
                                </div>
                            </div>

                            <!-- Content Category -->
                            <div class="mb-6">
                                <InputLabel
                                    for="content_category"
                                    value="Content Category"
                                />
                                <select
                                    id="content_category"
                                    v-model="form.content_category"
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 px-3 py-2 transition-colors"
                                    :class="{
                                        'border-red-500':
                                            form.errors.content_category,
                                    }"
                                >
                                    <option value="">Select Category</option>
                                    <option value="Social Media Post">
                                        Social Media Post
                                    </option>
                                    <option value="Blog Article">
                                        Blog Article
                                    </option>
                                    <option value="Email Newsletter">
                                        Email Newsletter
                                    </option>
                                    <option value="Product Description">
                                        Product Description
                                    </option>
                                    <option value="Marketing Campaign">
                                        Marketing Campaign
                                    </option>
                                    <option value="Press Release">
                                        Press Release
                                    </option>
                                    <option value="Video Content">
                                        Video Content
                                    </option>
                                    <option value="Other">Other</option>
                                </select>
                                <InputError
                                    :message="form.errors.content_category"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Tags -->
                            <div class="mb-6">
                                <InputLabel for="tags" value="Tags" />
                                <div class="mt-1">
                                    <!-- Tag Input Container -->
                                    <div class="relative">
                                        <div
                                            class="flex flex-wrap items-center gap-2 p-3 border border-gray-300 rounded-lg shadow-sm focus-within:border-purple-500 focus-within:ring-2 focus-within:ring-purple-500 min-h-12 transition-colors bg-white"
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
                                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 border border-purple-200 transition-all hover:bg-purple-200 hover:scale-105"
                                            >
                                                {{ tag.trim() }}
                                                <button
                                                    type="button"
                                                    @click.stop="
                                                        removeTag(index)
                                                    "
                                                    class="text-purple-600 hover:text-purple-800 focus:outline-none transition-colors"
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
                                                class="text-purple-600 hover:text-purple-800 focus:outline-none transition-colors p-1 rounded"
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
                                            class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10 p-2"
                                        >
                                            <div
                                                class="text-sm text-gray-600 flex items-center justify-between"
                                            >
                                                <span
                                                    >Press Enter or Comma to
                                                    add:</span
                                                >
                                                <span
                                                    class="font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded"
                                                >
                                                    {{ currentTagPreview }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tag Suggestions (if any) -->
                                    <div
                                        v-if="tagSuggestions.length > 0"
                                        class="mt-2 p-3 bg-gray-50 border border-gray-200 rounded-lg"
                                    >
                                        <p class="text-xs text-gray-500 mb-2">
                                            Common tags:
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            <span
                                                v-for="(
                                                    suggestion, index
                                                ) in tagSuggestions"
                                                :key="index"
                                                @click="
                                                    addSuggestion(suggestion)
                                                "
                                                class="text-xs px-3 py-1 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 transition-colors cursor-pointer inline-block"
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
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 px-3 py-2 transition-colors"
                                    :class="{
                                        'border-red-500': form.errors.notes,
                                    }"
                                    placeholder="Add any additional notes or internal comments..."
                                ></textarea>
                                <InputError
                                    :message="form.errors.notes"
                                    class="mt-2"
                                />
                            </div>
                        </div>

                        <!-- SEO Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-purple-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-purple-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        SEO Optimization
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Improve search engine visibility
                                    </p>
                                </div>
                            </div>

                            <!-- Meta Description -->
                            <div class="mb-6">
                                <InputLabel
                                    for="meta_description"
                                    value="Meta Description"
                                />
                                <textarea
                                    id="meta_description"
                                    v-model="form.meta_description"
                                    rows="2"
                                    maxlength="255"
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 px-3 py-2 transition-colors"
                                    :class="{
                                        'border-red-500':
                                            form.errors.meta_description,
                                    }"
                                    placeholder="Brief description for search engines..."
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
                                    placeholder="keyword1, keyword2, keyword3..."
                                />
                                <InputError
                                    :message="form.errors.seo_keywords"
                                    class="mt-2"
                                />
                                <p class="text-xs text-gray-500 mt-1">
                                    Separate keywords with commas
                                </p>
                            </div>
                        </div>

                        <!-- Media Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-purple-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-purple-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Media Files
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Upload images and additional files
                                    </p>
                                </div>
                            </div>

                            <!-- Image Upload -->
                            <div class="mb-6">
                                <InputLabel
                                    value="Main Image"
                                    class="mb-3 text-sm font-medium text-gray-700"
                                />

                                <!-- Display existing image if available -->
                                <div
                                    v-if="props.contentPost.image_url"
                                    class="mb-4"
                                >
                                    <p class="text-sm text-gray-600 mb-2">
                                        Current Image:
                                    </p>
                                    <div class="relative inline-block">
                                        <img
                                            :src="props.contentPost.image_url"
                                            alt="Current content image"
                                            class="max-w-full h-auto max-h-48 rounded-lg border border-gray-200"
                                            onerror="this.style.display='none'"
                                        />
                                        <div
                                            class="absolute top-2 right-2 bg-purple-600 text-white text-xs px-2 py-1 rounded"
                                        >
                                            Current
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">
                                        This image will be replaced if you
                                        upload a new one.
                                    </p>
                                </div>

                                <div
                                    class="bg-white rounded-lg border border-gray-200 p-4"
                                >
                                    <SimpleFileUploader
                                        v-model="form.image"
                                        label="Upload Main Image"
                                        accept="image/*"
                                        :multiple="false"
                                        :max-size="10 * 1024 * 1024"
                                        description="Drag & drop image here or click to browse"
                                        :error="form.errors.image"
                                    />
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    Upload a single image file. Supported
                                    formats: JPG, PNG, GIF, WebP. Maximum size:
                                    10MB.
                                </p>
                                <InputError
                                    :message="form.errors.image"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Media Upload -->
                            <div>
                                <InputLabel
                                    value="Additional Files"
                                    class="mb-3 text-sm font-medium text-gray-700"
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
                                            class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200"
                                        >
                                            <div
                                                class="flex items-center space-x-3"
                                            >
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="w-8 h-8 bg-purple-100 rounded flex items-center justify-center"
                                                    >
                                                        <svg
                                                            class="w-4 h-4 text-purple-600"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                            />
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span
                                                        class="text-sm font-medium text-gray-700"
                                                        >{{
                                                            media.original_name
                                                        }}</span
                                                    >
                                                    <div
                                                        class="text-xs text-gray-500"
                                                    >
                                                        {{
                                                            formatFileSize(
                                                                media.file_size
                                                            )
                                                        }}
                                                    </div>
                                                </div>
                                            </div>
                                            <a
                                                :href="media.url"
                                                target="_blank"
                                                class="text-purple-600 hover:text-purple-800 text-sm font-medium inline-flex items-center"
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
                                    <p class="text-xs text-gray-500 mt-2">
                                        These files will remain unless you
                                        delete them.
                                    </p>
                                </div>

                                <div
                                    class="bg-white rounded-lg border border-gray-200 p-4"
                                >
                                    <SimpleFileUploader
                                        v-model="form.media"
                                        label="Upload Additional Files"
                                        accept="image/*,.pdf,.xlsx,.csv"
                                        :multiple="true"
                                        :max-size="10 * 1024 * 1024"
                                        description="Drag & drop files here or click to browse"
                                        :error="form.errors.media"
                                    />
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    You can upload up to 10 files. Supported
                                    formats: JPG, PNG, GIF, PDF, DOC, DOCX, TXT,
                                    XLSX, CSV. Maximum size: 10MB each.
                                </p>
                                <InputError
                                    :message="form.errors.media"
                                    class="mt-2"
                                />
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div
                            class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200"
                        >
                            <button
                                type="button"
                                @click="goBack"
                                class="inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
                                :disabled="form.processing"
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
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center justify-center px-6 py-3 bg-purple-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50"
                            >
                                <svg
                                    v-if="!form.processing"
                                    class="w-4 h-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                <svg
                                    v-if="form.processing"
                                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
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
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import ImageUploader from "@/Components/Forms/ImageUploader.vue";
import SimpleFileUploader from "@/Components/Forms/SimpleFileUploader.vue";

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
    platform: Array.isArray(props.contentPost.platform)
        ? props.contentPost.platform
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
    meta_description: props.contentPost.meta_description || "",
    seo_keywords: props.contentPost.seo_keywords || "",
    media: [],
});

// Watch for changes to the image field and extract File object if it's a FileData object
watch(
    () => form.image,
    (newImage) => {
        if (
            newImage &&
            typeof newImage === "object" &&
            newImage.file instanceof File
        ) {
            // If it's a FileData object with a file property, extract the File object
            form.image = newImage.file;
            console.log(
                "Extracted File object from FileData:",
                newImage.file.name
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
    // Use the standard Inertia form submission which automatically handles CSRF tokens
    form.put(route("content.web.update", props.contentPost.id), {
        preserveScroll: true,
        onSuccess: () => {
            console.log("Content post updated successfully");
            router.visit(route("content.web.show", props.contentPost.id));
        },
        onError: (error) => {
            console.error("Error updating content post:", error);
            if (error.client_id) {
                console.error("Client ID error:", error.client_id);
            }
            if (error.content_type) {
                console.error("Content Type error:", error.content_type);
            }
        },
    });
};

const goBack = () => {
    router.visit(route("content.web.show", props.contentPost.id));
};
</script>
