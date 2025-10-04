<template>
    <div class="file-uploader" :class="{ 'has-error': hasError }">
        <div
            class="upload-area"
            :class="{
                'drag-over': isDragOver,
                'has-files': hasFiles,
                disabled: disabled,
            }"
            @dragover.prevent="isDragOver = true"
            @dragleave.prevent="isDragOver = false"
            @drop.prevent="handleDrop"
            @click="triggerFileInput"
        >
            <input
                ref="fileInput"
                type="file"
                :id="id"
                :multiple="multiple"
                :accept="accept"
                :disabled="disabled"
                class="hidden"
                @change="handleFileSelect"
            />

            <div class="upload-content">
                <!-- Upload prompt when no files -->
                <div v-if="!hasFiles" class="upload-prompt">
                    <div class="upload-icon">
                        <svg
                            class="w-12 h-12 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                            />
                        </svg>
                    </div>
                    <div class="upload-text">
                        <p class="text-lg font-medium text-gray-900">
                            {{ label }}
                        </p>
                        <p class="text-sm text-gray-500">{{ description }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            Maximum file size: {{ formatFileSize(maxSize) }}
                        </p>
                    </div>
                </div>

                <!-- File list when files are present -->
                <div v-else class="file-list">
                    <div
                        v-for="(file, index) in fileArray"
                        :key="index"
                        class="file-item"
                    >
                        <div class="file-icon">
                            <component
                                :is="getFileIcon(file)"
                                class="w-8 h-8"
                            />
                        </div>
                        <div class="file-info">
                            <p class="file-name">{{ file.name }}</p>
                            <p class="file-size">
                                {{ formatFileSize(file.size) }}
                            </p>
                        </div>
                        <button
                            @click.stop="removeFile(index)"
                            class="remove-btn"
                            :disabled="disabled"
                            type="button"
                        >
                            <svg
                                class="w-4 h-4"
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
                        </button>
                    </div>

                    <!-- Add more files button -->
                    <button
                        @click.stop="triggerFileInput"
                        class="add-more-btn"
                        :disabled="
                            disabled ||
                            (maxFiles && fileArray.length >= maxFiles)
                        "
                        type="button"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                            />
                        </svg>
                        Add {{ multiple ? "more files" : "different file" }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Error display -->
        <div v-if="hasError" class="error-container">
            <div class="error-icon">
                <svg
                    class="w-5 h-5 text-red-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"
                    />
                </svg>
            </div>
            <div class="error-messages">
                <div
                    v-for="(error, index) in allErrors"
                    :key="index"
                    class="error-message"
                >
                    {{ error }}
                </div>
            </div>
            <button @click="clearErrors" class="clear-errors" type="button">
                <svg
                    class="w-4 h-4"
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
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({
    modelValue: {
        type: [File, Array],
        default: null,
    },
    id: {
        type: String,
        default: () => `file-upload-${Math.random().toString(36).substr(2, 9)}`,
    },
    label: {
        type: String,
        default: "Upload files",
    },
    description: {
        type: String,
        default: "Click or drag files here",
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    accept: {
        type: String,
        default: "*",
    },
    maxSize: {
        type: Number,
        default: 10 * 1024 * 1024, // 10MB
    },
    maxFiles: {
        type: Number,
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["update:modelValue", "error", "change"]);

const fileInput = ref(null);
const isDragOver = ref(false);
const validationErrors = ref([]);

// Computed properties
const fileArray = computed({
    get: () => {
        if (props.multiple) {
            return Array.isArray(props.modelValue) ? props.modelValue : [];
        } else {
            return props.modelValue ? [props.modelValue] : [];
        }
    },
    set: (value) => {
        if (props.multiple) {
            emit("update:modelValue", value);
        } else {
            emit("update:modelValue", value[0] || null);
        }
    },
});

const hasFiles = computed(() => fileArray.value.length > 0);
const hasError = computed(
    () => props.error || validationErrors.value.length > 0
);
const allErrors = computed(() => {
    const errors = [];
    if (props.error) errors.push(props.error);
    errors.push(...validationErrors.value);
    return errors;
});

// Methods
const triggerFileInput = () => {
    if (!props.disabled) {
        fileInput.value?.click();
    }
};

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    processFiles(files);

    // Clear the input to allow selecting the same file again
    event.target.value = "";
};

const handleDrop = (event) => {
    isDragOver.value = false;
    const files = Array.from(event.dataTransfer.files);
    processFiles(files);
};

const processFiles = (newFiles) => {
    clearErrors();

    if (props.disabled) return;

    const validFiles = [];
    const errors = [];

    // Check file count limit
    if (
        props.maxFiles &&
        fileArray.value.length + newFiles.length > props.maxFiles
    ) {
        errors.push(`Maximum ${props.maxFiles} files allowed`);
        validationErrors.value = errors;
        emit("error", errors);
        return;
    }

    // Validate each file
    newFiles.forEach((file) => {
        const fileErrors = validateFile(file);
        if (fileErrors.length > 0) {
            errors.push(...fileErrors);
        } else {
            validFiles.push(file);
        }
    });

    // Update errors
    if (errors.length > 0) {
        validationErrors.value = errors;
        emit("error", errors);
    }

    // Update model value with valid files
    if (validFiles.length > 0) {
        if (props.multiple) {
            fileArray.value = [...fileArray.value, ...validFiles];
        } else {
            fileArray.value = [validFiles[0]];
        }
        emit("change", fileArray.value);
    }
};

const validateFile = (file) => {
    const errors = [];

    // File size validation
    if (file.size > props.maxSize) {
        errors.push(
            `File "${file.name}" is too large. Maximum size is ${formatFileSize(
                props.maxSize
            )}`
        );
    }

    // File type validation (basic check against accept attribute)
    if (props.accept !== "*") {
        const acceptTypes = props.accept
            .split(",")
            .map((type) => type.trim().toLowerCase());
        const fileName = file.name.toLowerCase();
        const fileExtension = "." + fileName.split(".").pop();

        const isValidType = acceptTypes.some((acceptType) => {
            if (acceptType.startsWith(".")) {
                return acceptType === fileExtension;
            } else if (acceptType.endsWith("/*")) {
                const category = acceptType.split("/")[0];
                return file.type.startsWith(category);
            } else {
                return file.type === acceptType;
            }
        });

        if (!isValidType) {
            errors.push(
                `File "${file.name}" has invalid type. Allowed types: ${props.accept}`
            );
        }
    }

    return errors;
};

const removeFile = (index) => {
    if (props.disabled) return;

    const newFiles = [...fileArray.value];
    newFiles.splice(index, 1);
    fileArray.value = newFiles;
    emit("change", fileArray.value);
};

const clearErrors = () => {
    validationErrors.value = [];
    emit("error", null);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const getFileIcon = (file) => {
    if (file.type.startsWith("image/")) {
        return "ImageIcon";
    } else if (file.type === "application/pdf") {
        return "DocumentIcon";
    } else if (file.type.includes("sheet") || file.type.includes("excel")) {
        return "ChartBarIcon";
    } else if (file.type.includes("text")) {
        return "DocumentTextIcon";
    } else {
        return "PaperClipIcon";
    }
};

// Icon components (simplified versions)
const ImageIcon = {
    template:
        '<svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>',
};
const DocumentIcon = {
    template:
        '<svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
};
const ChartBarIcon = {
    template:
        '<svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>',
};
const DocumentTextIcon = {
    template:
        '<svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
};
const PaperClipIcon = {
    template:
        '<svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>',
};

// Watch for external error changes
watch(
    () => props.error,
    (newError) => {
        if (newError) {
            validationErrors.value = [newError];
        }
    }
);
</script>

<style scoped>
.file-uploader {
    @apply w-full;
}

.upload-area {
    @apply border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer transition-colors;
}

.upload-area:hover:not(.disabled) {
    @apply border-gray-400 bg-gray-50;
}

.upload-area.drag-over {
    @apply border-blue-500 bg-blue-50;
}

.upload-area.has-error {
    @apply border-red-500 bg-red-50;
}

.upload-area.disabled {
    @apply opacity-50 cursor-not-allowed;
}

.upload-area.disabled:hover {
    @apply border-gray-300 bg-transparent;
}

.upload-prompt {
    @apply space-y-3;
}

.upload-icon {
    @apply flex justify-center;
}

.upload-text {
    @apply space-y-1;
}

.file-list {
    @apply space-y-3;
}

.file-item {
    @apply flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-200;
}

.file-icon {
    @apply flex-shrink-0;
}

.file-info {
    @apply flex-1 min-w-0;
}

.file-name {
    @apply text-sm font-medium text-gray-900 truncate;
}

.file-size {
    @apply text-xs text-gray-500;
}

.remove-btn {
    @apply flex-shrink-0 p-1 text-gray-400 hover:text-red-500 transition-colors;
}

.add-more-btn {
    @apply w-full p-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-gray-400 hover:text-gray-600 transition-colors flex items-center justify-center space-x-2;
}

.add-more-btn:disabled {
    @apply opacity-50 cursor-not-allowed;
}

.error-container {
    @apply mt-2 p-3 bg-red-50 border border-red-200 rounded-lg flex items-start space-x-2;
}

.error-icon {
    @apply flex-shrink-0 mt-0.5;
}

.error-messages {
    @apply flex-1;
}

.error-message {
    @apply text-sm text-red-700;
}

.clear-errors {
    @apply flex-shrink-0 p-1 text-red-400 hover:text-red-600 transition-colors;
}
</style>
