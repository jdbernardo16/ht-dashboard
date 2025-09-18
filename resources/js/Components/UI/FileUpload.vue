<template>
    <div class="file-upload-container">
        <!-- File Input -->
        <input
            type="file"
            ref="fileInput"
            :multiple="multiple"
            :accept="accept"
            @change="handleFileSelect"
            class="hidden"
        />

        <!-- Upload Area -->
        <div
            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer transition-colors hover:border-indigo-400 hover:bg-indigo-50"
            :class="{
                'border-indigo-500 bg-indigo-50': isDragging,
                'border-red-500': error,
            }"
            @click="triggerFileInput"
            @dragover.prevent="isDragging = true"
            @dragleave="isDragging = false"
            @drop.prevent="handleDrop"
        >
            <!-- Upload Icon -->
            <div class="mx-auto h-12 w-12 text-gray-400">
                <svg
                    class="h-12 w-12 mx-auto"
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

            <!-- Upload Text -->
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-900">
                    {{ title }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ description }}
                </p>
                <p v-if="error" class="text-xs text-red-600 mt-1">
                    {{ error }}
                </p>
            </div>

            <!-- Upload Button -->
            <button
                type="button"
                class="mt-4 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                Select Files
            </button>
        </div>

        <!-- Selected Files Preview -->
        <div v-if="selectedFiles.length > 0" class="mt-4 space-y-3">
            <div
                v-for="(file, index) in selectedFiles"
                :key="file.name + index"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border"
            >
                <div class="flex items-center space-x-3">
                    <!-- File Icon -->
                    <div class="flex-shrink-0">
                        <div
                            class="w-10 h-10 flex items-center justify-center rounded bg-gray-200"
                        >
                            <span class="text-xs font-medium text-gray-600">
                                {{ getFileExtension(file.name) }}
                            </span>
                        </div>
                    </div>

                    <!-- File Info -->
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ file.name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ formatFileSize(file.size) }}
                        </p>
                    </div>
                </div>

                <!-- Remove Button -->
                <button
                    type="button"
                    @click="removeFile(index)"
                    class="ml-3 text-red-600 hover:text-red-800"
                    title="Remove file"
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
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Max Files Warning -->
        <p
            v-if="maxFiles && selectedFiles.length >= maxFiles"
            class="mt-2 text-xs text-orange-600"
        >
            Maximum {{ maxFiles }} file(s) allowed
        </p>
    </div>
</template>

<script setup>
import { ref, watch, computed } from "vue";

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    multiple: {
        type: Boolean,
        default: true,
    },
    accept: {
        type: String,
        default: "image/*,.pdf,.doc,.docx,.txt",
    },
    maxFiles: {
        type: Number,
        default: null,
    },
    maxSize: {
        type: Number,
        default: 10 * 1024 * 1024, // 10MB default
    },
    title: {
        type: String,
        default: "Drag & drop files here",
    },
    description: {
        type: String,
        default: "or click to browse files",
    },
});

const emit = defineEmits(["update:modelValue", "error"]);

const fileInput = ref(null);
const isDragging = ref(false);
const error = ref("");
const selectedFiles = ref([]);

// Watch for external modelValue changes
watch(
    () => props.modelValue,
    (newValue) => {
        if (!Array.isArray(newValue)) {
            selectedFiles.value = [];
        } else {
            selectedFiles.value = newValue;
        }
    },
    { immediate: true }
);

// Emit updates when selected files change
watch(selectedFiles, (newFiles) => {
    emit("update:modelValue", newFiles);
});

const triggerFileInput = () => {
    if (props.maxFiles && selectedFiles.value.length >= props.maxFiles) {
        return;
    }
    fileInput.value?.click();
};

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    processFiles(files);
    event.target.value = ""; // Reset input
};

const handleDrop = (event) => {
    isDragging.value = false;
    const files = Array.from(event.dataTransfer.files);
    processFiles(files);
};

const processFiles = (files) => {
    if (
        props.maxFiles &&
        selectedFiles.value.length + files.length > props.maxFiles
    ) {
        error.value = `Cannot select more than ${props.maxFiles} files`;
        emit("error", error.value);
        return;
    }

    const validFiles = files.filter((file) => {
        // Check file size
        if (file.size > props.maxSize) {
            error.value = `File "${
                file.name
            }" exceeds maximum size of ${formatFileSize(props.maxSize)}`;
            emit("error", error.value);
            return false;
        }

        // Check file type if accept is specified
        if (props.accept && props.accept !== "*") {
            const acceptPatterns = props.accept
                .split(",")
                .map((pattern) => pattern.trim());
            const fileExtension =
                "." + file.name.split(".").pop().toLowerCase();

            const isAccepted = acceptPatterns.some((pattern) => {
                if (pattern.startsWith(".")) {
                    return pattern.toLowerCase() === fileExtension;
                }
                if (pattern.endsWith("/*")) {
                    const category = pattern.split("/")[0];
                    return file.type.startsWith(category);
                }
                return pattern === file.type;
            });

            if (!isAccepted) {
                error.value = `File type not allowed: ${file.name}`;
                emit("error", error.value);
                return false;
            }
        }

        return true;
    });

    if (validFiles.length > 0) {
        error.value = "";
        selectedFiles.value = [...selectedFiles.value, ...validFiles];
    }
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
};

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

// Expose methods
defineExpose({
    clearFiles: () => {
        selectedFiles.value = [];
        error.value = "";
    },
    getFiles: () => selectedFiles.value,
});
</script>

<style scoped>
.file-upload-container {
    transition: all 0.2s ease;
}

.hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>
