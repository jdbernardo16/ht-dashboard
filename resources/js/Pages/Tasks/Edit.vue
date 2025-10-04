<template>
    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Edit Task: {{ task.title }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Update the task details and information
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
                        Back to Tasks
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
                    class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4"
                >
                    <h2 class="text-xl font-semibold text-white">
                        Task Details
                    </h2>
                    <p class="text-indigo-100 text-sm mt-1">
                        Update the information below to modify this task
                    </p>
                </div>

                <div class="p-6 text-gray-900">
                    <!-- Form Container -->
                    <form @submit.prevent="submitForm" class="space-y-8">
                        <!-- Basic Information Section -->
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
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
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
                                        Enter the core details for this task
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-6">
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
                                <div>
                                    <InputLabel
                                        for="description"
                                        value="Description *"
                                    />
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        rows="4"
                                        required
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
                        </div>

                        <!-- Task Details Section -->
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
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Task Details
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Configure priority, status, and
                                        scheduling
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    <InputError
                                        :message="form.errors.priority"
                                        class="mt-2"
                                    />
                                </div>

                                <!-- Status -->
                                <div>
                                    <InputLabel for="status" value="Status *" />
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
                                        <option value="">Select Status</option>
                                        <option value="pending">Pending</option>
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
                                    <select
                                        id="category"
                                        v-model="form.category"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{
                                            'border-red-500':
                                                form.errors.category,
                                        }"
                                    >
                                        <option value="">
                                            Select Category
                                        </option>
                                        <option value="Inventory Management">
                                            Inventory Management
                                        </option>
                                        <option value="Client Communication">
                                            Client Communication
                                        </option>
                                        <option value="Sales Processing">
                                            Sales Processing
                                        </option>
                                        <option value="Content Creation">
                                            Content Creation
                                        </option>
                                        <option value="Expense Tracking">
                                            Expense Tracking
                                        </option>
                                        <option value="Goal Planning">
                                            Goal Planning
                                        </option>
                                        <option value="System Maintenance">
                                            System Maintenance
                                        </option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <InputError
                                        :message="form.errors.category"
                                        class="mt-2"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Section -->
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
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Assignment
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Assign task to team members and link to
                                        goals
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                        :message="form.errors.related_goal_id"
                                        class="mt-2"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Time Tracking Section -->
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
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Time Tracking
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Track estimated and actual hours
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                        :message="form.errors.estimated_hours"
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

                        <!-- Additional Details Section -->
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
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Additional Details
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Add tags and notes for better
                                        organization
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
                                            class="flex flex-wrap items-center gap-2 p-3 border border-gray-300 rounded-lg shadow-sm focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500 min-h-12 transition-colors bg-white"
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

                        <!-- File Attachments Section -->
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
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        File Attachments
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Manage files and documents related to
                                        this task
                                    </p>
                                </div>
                            </div>

                            <!-- Existing Media Files -->
                            <div
                                v-if="task.media && task.media.length > 0"
                                class="mb-6"
                            >
                                <InputLabel
                                    value="Existing Files"
                                    class="mb-3 text-sm font-medium text-gray-700"
                                />
                                <div class="space-y-3">
                                    <div
                                        v-for="media in task.media"
                                        :key="media.id"
                                        class="flex items-center justify-between p-4 bg-white rounded-lg border border-gray-200 hover:border-gray-300 transition-colors"
                                    >
                                        <div
                                            class="flex items-center space-x-4"
                                        >
                                            <!-- File Icon -->
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="w-12 h-12 flex items-center justify-center rounded-lg bg-gray-100 border border-gray-200"
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

                                        <!-- Remove Button -->
                                        <button
                                            type="button"
                                            @click="
                                                removeExistingMedia(media.id)
                                            "
                                            class="ml-4 text-red-600 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 p-2 rounded-lg hover:bg-red-50 transition-colors"
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
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
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
                                    class="mb-3 text-sm font-medium text-gray-700"
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
                                    formats: JPG, PNG, GIF, PDF, DOC, DOCX, TXT.
                                </p>
                            </div>
                        </div>

                        <!-- Recurring Task Section -->
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
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                            ></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Recurring Task
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Configure if this task should repeat
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- Is Recurring -->
                                <div
                                    class="flex items-center p-4 bg-white rounded-lg border border-gray-200"
                                >
                                    <input
                                        id="is_recurring"
                                        v-model="form.is_recurring"
                                        type="checkbox"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    />
                                    <InputLabel
                                        for="is_recurring"
                                        value="This is a recurring task"
                                        class="ml-3 text-sm font-medium text-gray-700"
                                    />
                                </div>

                                <!-- Recurring Frequency -->
                                <div
                                    v-if="form.is_recurring"
                                    class="bg-white rounded-lg border border-gray-200 p-4"
                                >
                                    <InputLabel
                                        for="recurring_frequency"
                                        value="Frequency"
                                        class="mb-2 text-sm font-medium text-gray-700"
                                    />
                                    <select
                                        id="recurring_frequency"
                                        v-model="form.recurring_frequency"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{
                                            'border-red-500':
                                                form.errors.recurring_frequency,
                                        }"
                                    >
                                        <option value="">
                                            Select Frequency
                                        </option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="yearly">Yearly</option>
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
                            class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200"
                        >
                            <SecondaryButton
                                type="button"
                                @click="goBack"
                                :disabled="form.processing"
                                class="px-6 py-3"
                            >
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton
                                type="submit"
                                :disabled="form.processing"
                                :class="{ 'opacity-50': form.processing }"
                                class="px-6 py-3"
                            >
                                <span v-if="form.processing">Updating...</span>
                                <span v-else>Update Task</span>
                            </PrimaryButton>
                        </div>
                    </form>
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
