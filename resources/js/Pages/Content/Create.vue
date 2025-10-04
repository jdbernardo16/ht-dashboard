<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Create New Content Post
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Plan and create new content for your marketing channels
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
                        Fill in the content information and scheduling details
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
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                        :class="{
                                            'border-red-500 focus:border-red-500':
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

                                <!-- Category -->
                                <div>
                                    <InputLabel
                                        for="category_id"
                                        value="Category"
                                    />
                                    <select
                                        id="category_id"
                                        v-model="form.category_id"
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                        :class="{
                                            'border-red-500 focus:border-red-500':
                                                form.errors.category_id,
                                        }"
                                    >
                                        <option value="">
                                            Select a category
                                        </option>
                                        <option
                                            v-for="category in categories"
                                            :key="category.id"
                                            :value="category.id"
                                        >
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.category_id"
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
                                        placeholder="Enter content title..."
                                        :class="{
                                            'border-red-500 focus:border-red-500':
                                                form.errors.title,
                                        }"
                                    />
                                    <InputError
                                        :message="form.errors.title"
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
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                        rows="4"
                                        placeholder="Describe the content..."
                                        :class="{
                                            'border-red-500 focus:border-red-500':
                                                form.errors.description,
                                        }"
                                    ></textarea>
                                    <InputError
                                        :message="form.errors.description"
                                        class="mt-2"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Content Details Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-pink-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-pink-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Content Details
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Set platforms, type, and categorization
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <!-- Platform -->
                                <div>
                                    <InputLabel
                                        value="Platforms *"
                                        class="mb-3"
                                    />
                                    <div
                                        class="grid grid-cols-2 md:grid-cols-3 gap-3"
                                    >
                                        <div
                                            v-for="platformOption in platformOptions"
                                            :key="platformOption.value"
                                            class="flex items-center p-3 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
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
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                        :class="{
                                            'border-red-500 focus:border-red-500':
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

                                <!-- Content Category -->
                                <div>
                                    <InputLabel
                                        for="content_category"
                                        value="Content Category"
                                    />
                                    <select
                                        id="content_category"
                                        v-model="form.content_category"
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                        :class="{
                                            'border-red-500 focus:border-red-500':
                                                form.errors.content_category,
                                        }"
                                    >
                                        <option value="">
                                            Select Category
                                        </option>
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
                            </div>
                        </div>

                        <!-- Scheduling Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-indigo-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-indigo-600"
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
                                        Set dates and status for publishing
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                            'border-red-500 focus:border-red-500':
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
                                            'border-red-500 focus:border-red-500':
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
                                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                        :class="{
                                            'border-red-500 focus:border-red-500':
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
                                        placeholder="1"
                                        :class="{
                                            'border-red-500 focus:border-red-500':
                                                form.errors.post_count,
                                        }"
                                    />
                                    <InputError
                                        :message="form.errors.post_count"
                                        class="mt-2"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Media & Links Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-green-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-green-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Media & Links
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Add URLs and media content
                                    </p>
                                </div>
                            </div>

                            <!-- Content URL -->
                            <div>
                                <InputLabel
                                    for="content_url"
                                    value="Content URL *"
                                />
                                <TextInput
                                    id="content_url"
                                    v-model="form.content_url"
                                    type="url"
                                    required
                                    class="mt-1 block w-full"
                                    placeholder="https://example.com/content"
                                    :class="{
                                        'border-red-500 focus:border-red-500':
                                            form.errors.content_url,
                                    }"
                                />
                                <InputError
                                    :message="form.errors.content_url"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Featured Image -->
                            <div class="mt-6">
                                <InputLabel
                                    for="image"
                                    value="Featured Image"
                                />
                                <div class="mt-1">
                                    <ImageUploader
                                        v-model="form.image"
                                        label="Upload featured image"
                                        description="Click or drag image here to upload"
                                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                        :max-size="5 * 1024 * 1024"
                                        :max-width="2000"
                                        :max-height="2000"
                                        :min-width="100"
                                        :min-height="100"
                                        :error="form.errors.image"
                                    />
                                    <p class="mt-2 text-xs text-gray-500">
                                        Recommended: Square image, minimum
                                        100x100px, maximum 5MB
                                    </p>
                                </div>
                                <InputError
                                    :message="form.errors.image"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Media Files -->
                            <div class="mt-6">
                                <InputLabel for="media" value="Media Files" />
                                <div class="mt-1">
                                    <SimpleFileUploader
                                        v-model="form.media"
                                        label="Upload media files"
                                        description="Click or drag files here to upload"
                                        :multiple="true"
                                        accept="image/*,.pdf,.doc,.docx,.txt"
                                        :max-files="10"
                                        :max-size="10 * 1024 * 1024"
                                        :error="form.errors.media"
                                    />
                                    <p class="mt-2 text-xs text-gray-500">
                                        Supports images, PDFs, and documents
                                        (max 10MB each)
                                    </p>
                                </div>
                                <InputError
                                    :message="form.errors.media"
                                    class="mt-2"
                                />
                            </div>
                        </div>

                        <!-- Tags & Notes Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-10 h-10 bg-yellow-100 rounded-full"
                                    >
                                        <svg
                                            class="w-5 h-5 text-yellow-600"
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
                                        Tags & Notes
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Add tags and additional information
                                    </p>
                                </div>
                            </div>

                            <!-- Tags -->
                            <div class="mb-6">
                                <InputLabel for="tags" value="Tags" />
                                <div class="mt-1">
                                    <!-- Tag Input Container -->
                                    <div class="relative">
                                        <div
                                            class="flex flex-wrap items-center gap-2 p-3 border border-gray-300 rounded-lg shadow-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 min-h-12 transition-colors bg-white"
                                            :class="{
                                                'border-red-500 focus:border-red-500':
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
                                                class="text-purple-600 hover:text-purple-800 focus:outline-none transition-colors p-1 rounded"
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

                                    <!-- Tag Suggestions -->
                                    <div
                                        v-if="tagSuggestions.length > 0"
                                        class="mt-2 p-3 bg-gray-50 border border-gray-200 rounded-lg"
                                    >
                                        <p class="text-xs text-gray-500 mb-2">
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
                            <div class="mb-6">
                                <InputLabel for="notes" value="Notes" />
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    placeholder="Add any additional notes..."
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    :class="{
                                        'border-red-500 focus:border-red-500':
                                            form.errors.notes,
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
                                    placeholder="Brief description for SEO..."
                                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors"
                                    :class="{
                                        'border-red-500 focus:border-red-500':
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
                            <div class="mt-6">
                                <InputLabel
                                    for="seo_keywords"
                                    value="SEO Keywords"
                                />
                                <TextInput
                                    id="seo_keywords"
                                    v-model="form.seo_keywords"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="Enter keywords separated by commas..."
                                    :class="{
                                        'border-red-500 focus:border-red-500':
                                            form.errors.seo_keywords,
                                    }"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Separate keywords with commas (e.g., sports
                                    cards, trading cards, collectibles)
                                </p>
                                <InputError
                                    :message="form.errors.seo_keywords"
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
                                class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="loading"
                                class="px-6 py-3 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:opacity-50 transition-all duration-200"
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
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import ImageUploader from "@/Components/Forms/ImageUploader.vue";
import SimpleFileUploader from "@/Components/Forms/SimpleFileUploader.vue";

const props = defineProps({
    clients: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const loading = ref(false);

const form = useForm({
    client_id: "",
    category_id: "",
    title: "",
    description: "",
    platform: [],
    content_type: "",
    content_url: "",
    post_count: 1,
    scheduled_date: new Date().toISOString().split("T")[0],
    published_date: new Date().toISOString().split("T")[0],
    status: "draft",
    content_category: "",
    tags: [],
    notes: "",
    meta_description: "",
    seo_keywords: "",
    image: null,
    media: [],
});

// Platform options
const platformOptions = [
    { value: "facebook", label: "Facebook" },
    { value: "instagram", label: "Instagram" },
    { value: "twitter", label: "Twitter" },
    { value: "linkedin", label: "LinkedIn" },
    { value: "tiktok", label: "TikTok" },
    { value: "youtube", label: "YouTube" },
];

// Enhanced Tag Handling
const tagInput = ref("");
const tagInputRef = ref(null);
const isTagInputFocused = ref(false);
const currentTagPreview = ref("");
const tagSuggestions = ref([
    "marketing",
    "social-media",
    "promotion",
    "announcement",
    "blog",
    "video",
    "tutorial",
    "product-launch",
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

const onMediaChange = (files) => {
    form.media = files;
};

const submitForm = () => {
    loading.value = true;

    form.post(route("content.web.store"), {
        onSuccess: () => {
            router.visit(route("content.web.index"));
        },
        onError: (error) => {
            console.error("Form errors:", error);
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
