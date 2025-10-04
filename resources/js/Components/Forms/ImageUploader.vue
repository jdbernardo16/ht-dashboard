<template>
    <div class="image-uploader" :class="{ 'has-error': hasError }">
        <div
            class="upload-area"
            :class="{
                'drag-over': isDragOver,
                'has-image': hasImage,
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
                :accept="accept"
                :disabled="disabled"
                class="hidden"
                @change="handleFileSelect"
            />

            <div class="upload-content">
                <!-- Image preview when image is present -->
                <div
                    v-if="hasImage && currentPreviewUrl"
                    class="image-preview group"
                >
                    <img
                        :src="currentPreviewUrl"
                        :alt="previewAlt"
                        class="preview-image"
                        @load="onImageLoad"
                        @error="onImageError"
                    />

                    <!-- Image overlay with actions -->
                    <div class="image-overlay">
                        <button
                            @click.stop="removeImage"
                            class="remove-btn"
                            :disabled="disabled"
                            type="button"
                            title="Remove image"
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
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>

                        <button
                            @click.stop="triggerFileInput"
                            class="replace-btn"
                            :disabled="disabled"
                            type="button"
                            title="Replace image"
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
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
                                />
                            </svg>
                        </button>
                    </div>

                    <!-- Image info -->
                    <div v-if="imageInfo" class="image-info">
                        <p class="image-name">{{ imageInfo.name }}</p>
                        <p class="image-details">
                            {{ formatFileSize(imageInfo.size) }}
                            <span v-if="imageInfo.dimensions">
                                • {{ imageInfo.dimensions.width }}×{{
                                    imageInfo.dimensions.height
                                }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Upload prompt when no image -->
                <div v-else class="upload-prompt">
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
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
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
                        <p v-if="dimensionsText" class="text-xs text-gray-400">
                            {{ dimensionsText }}
                        </p>
                    </div>
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
import { ref, computed, watch, onUnmounted } from "vue";

const props = defineProps({
    modelValue: {
        type: [File, String, null],
        default: null,
    },
    id: {
        type: String,
        default: () =>
            `image-upload-${Math.random().toString(36).substr(2, 9)}`,
    },
    label: {
        type: String,
        default: "Upload image",
    },
    description: {
        type: String,
        default: "Click or drag image here",
    },
    accept: {
        type: String,
        default: "image/*",
    },
    maxSize: {
        type: Number,
        default: 10 * 1024 * 1024, // 10MB
    },
    maxWidth: {
        type: Number,
        default: 4000,
    },
    maxHeight: {
        type: Number,
        default: 4000,
    },
    minWidth: {
        type: Number,
        default: 100,
    },
    minHeight: {
        type: Number,
        default: 100,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
    previewUrl: {
        type: String,
        default: null,
    },
});

const emit = defineEmits([
    "update:modelValue",
    "error",
    "change",
    "image-loaded",
]);

const fileInput = ref(null);
const isDragOver = ref(false);
const validationErrors = ref([]);
const imageInfo = ref(null);
const internalPreviewUrl = ref(null);

// Computed properties
const hasImage = computed(() => {
    return (
        props.modelValue instanceof File ||
        props.previewUrl ||
        internalPreviewUrl.value
    );
});

const currentPreviewUrl = computed(() => {
    if (props.previewUrl) return props.previewUrl;
    if (internalPreviewUrl.value) return internalPreviewUrl.value;
    return null;
});

const previewAlt = computed(() => {
    return imageInfo.value?.name || "Uploaded image";
});

const hasError = computed(
    () => props.error || validationErrors.value.length > 0
);

const allErrors = computed(() => {
    const errors = [];
    if (props.error) errors.push(props.error);
    errors.push(...validationErrors.value);
    return errors;
});

const dimensionsText = computed(() => {
    if (
        props.minWidth &&
        props.minHeight &&
        props.maxWidth &&
        props.maxHeight
    ) {
        return `Dimensions: ${props.minWidth}×${props.minHeight} to ${props.maxWidth}×${props.maxHeight} pixels`;
    }
    return null;
});

// Methods
const triggerFileInput = () => {
    if (!props.disabled) {
        fileInput.value?.click();
    }
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        processFile(file);
    }
    // Clear the input to allow selecting the same file again
    event.target.value = "";
};

const handleDrop = (event) => {
    isDragOver.value = false;
    const file = event.dataTransfer.files[0];
    if (file) {
        processFile(file);
    }
};

const processFile = (file) => {
    clearErrors();

    if (props.disabled) return;

    // Validate file
    const errors = validateFile(file);
    if (errors.length > 0) {
        validationErrors.value = errors;
        emit("error", errors);
        return;
    }

    // Set image info
    imageInfo.value = {
        name: file.name,
        size: file.size,
        type: file.type,
    };

    // Create preview URL
    if (internalPreviewUrl.value) {
        URL.revokeObjectURL(internalPreviewUrl.value);
    }
    internalPreviewUrl.value = URL.createObjectURL(file);

    // Update model value
    emit("update:modelValue", file);
    emit("change", file);
};

const validateFile = (file) => {
    const errors = [];

    // Check if it's an image
    if (!file.type.startsWith("image/")) {
        errors.push("The selected file is not an image.");
        return errors;
    }

    // File size validation
    if (file.size > props.maxSize) {
        errors.push(
            `File "${file.name}" is too large. Maximum size is ${formatFileSize(
                props.maxSize
            )}`
        );
    }

    // File type validation (basic check against accept attribute)
    if (props.accept !== "image/*" && props.accept !== "*") {
        const acceptTypes = props.accept
            .split(",")
            .map((type) => type.trim().toLowerCase());

        const isValidType = acceptTypes.some((acceptType) => {
            if (acceptType.startsWith(".")) {
                const fileExtension =
                    "." + file.name.split(".").pop().toLowerCase();
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

    // Image dimensions validation (will be checked after image loads)
    // This is handled in onImageLoad method

    return errors;
};

const removeImage = () => {
    if (props.disabled) return;

    // Clean up preview URL
    if (internalPreviewUrl.value) {
        URL.revokeObjectURL(internalPreviewUrl.value);
        internalPreviewUrl.value = null;
    }

    imageInfo.value = null;
    emit("update:modelValue", null);
    emit("change", null);
};

const clearErrors = () => {
    validationErrors.value = [];
    emit("error", null);
};

const onImageLoad = (event) => {
    const img = event.target;

    // Update image info with dimensions
    if (imageInfo.value) {
        imageInfo.value.dimensions = {
            width: img.naturalWidth,
            height: img.naturalHeight,
        };
    }

    // Validate dimensions
    const errors = [];
    if (
        img.naturalWidth < props.minWidth ||
        img.naturalHeight < props.minHeight
    ) {
        errors.push(
            `Image dimensions are too small. Minimum size is ${props.minWidth}×${props.minHeight} pixels.`
        );
    }

    if (
        img.naturalWidth > props.maxWidth ||
        img.naturalHeight > props.maxHeight
    ) {
        errors.push(
            `Image dimensions are too large. Maximum size is ${props.maxWidth}×${props.maxHeight} pixels.`
        );
    }

    if (errors.length > 0) {
        validationErrors.value = errors;
        emit("error", errors);
    }

    emit("image-loaded", {
        width: img.naturalWidth,
        height: img.naturalHeight,
        file: props.modelValue,
    });
};

const onImageError = () => {
    validationErrors.value = ["Failed to load image preview."];
    emit("error", ["Failed to load image preview."]);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
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

// Watch for external model value changes
watch(
    () => props.modelValue,
    (newValue) => {
        // If model value is cleared externally, clean up preview
        if (!newValue) {
            if (internalPreviewUrl.value) {
                URL.revokeObjectURL(internalPreviewUrl.value);
                internalPreviewUrl.value = null;
            }
            imageInfo.value = null;
        }
    }
);

// Clean up preview URL on unmount
onUnmounted(() => {
    if (internalPreviewUrl.value) {
        URL.revokeObjectURL(internalPreviewUrl.value);
    }
});
</script>

<style scoped>
.image-uploader {
    @apply w-full;
}

.upload-area {
    @apply border-2 border-dashed border-gray-300 rounded-lg overflow-hidden cursor-pointer transition-colors relative;
    @apply min-h-[200px] flex items-center justify-center;
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

.upload-content {
    @apply w-full h-full flex items-center justify-center;
}

.upload-prompt {
    @apply text-center space-y-3 p-6;
}

.upload-icon {
    @apply flex justify-center;
}

.upload-text {
    @apply space-y-1;
}

.image-preview {
    @apply relative w-full h-full;
}

.preview-image {
    @apply w-full h-full object-contain;
}

.image-overlay {
    @apply absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200;
    @apply flex items-center justify-center space-x-4 opacity-0 group-hover:opacity-100;
}

.remove-btn {
    @apply p-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors;
}

.replace-btn {
    @apply p-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition-colors;
}

.image-info {
    @apply absolute bottom-0 left-0 right-0 bg-black bg-opacity-75 text-white p-2;
}

.image-name {
    @apply text-sm font-medium truncate;
}

.image-details {
    @apply text-xs opacity-75;
}

.error-container {
    @apply mt-2 flex items-start space-x-2 p-3 bg-red-50 border border-red-200 rounded-lg;
}

.error-icon {
    @apply flex-shrink-0 mt-0.5;
}

.error-messages {
    @apply flex-1 min-w-0;
}

.error-message {
    @apply text-sm text-red-600;
}

.clear-errors {
    @apply flex-shrink-0 p-1 text-red-400 hover:text-red-600 transition-colors;
}
</style>
