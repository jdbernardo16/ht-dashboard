<template>
    <div class="space-y-3">
        <div
            ref="dropzoneRef"
            :class="[
                'border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer transition-colors hover:border-gray-400',
                { 'border-blue-500 bg-blue-50': isDragOver },
                { 'border-red-600': !!error },
                { 'opacity-50 cursor-not-allowed': disabled },
            ]"
            @click="triggerFileInput"
            @dragover.prevent="handleDragOver"
            @dragleave.prevent="handleDragLeave"
            @drop.prevent="handleDrop"
        >
            <!-- Upload content -->
            <div class="flex flex-col items-center space-y-2">
                <div class="size-8 text-gray-400">
                    <svg
                        class="h-8 w-8 text-gray-400"
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
                <div class="file-uploader__text">
                    <p class="text-sm font-medium text-gray-900">
                        {{ label }}
                        <span v-if="required" class="text-red-600">*</span>
                    </p>
                    <p class="text-sm text-gray-500">{{ description }}</p>
                </div>
            </div>

            <!-- File input -->
            <input
                ref="fileInput"
                type="file"
                :id="id"
                :accept="acceptString"
                :multiple="multiple"
                :disabled="disabled"
                class="hidden"
                @change="onFileChange"
            />
        </div>

        <!-- Preview area -->
        <div v-if="withPreview && previewUrl" class="relative inline-block">
            <img
                :src="previewUrl"
                :alt="`Preview of ${getFileName()}`"
                class="w-32 h-32 object-cover rounded-lg border"
            />
            <button
                type="button"
                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                @click="removeFile"
            >
                <svg
                    class="size-4"
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

        <!-- Error display -->
        <div v-if="validationErrors.length > 0" class="space-y-1">
            <p
                v-for="(error, index) in validationErrors"
                :key="index"
                class="text-sm text-red-600"
            >
                {{ error.file }}: {{ error.error }}
            </p>
        </div>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
// import { useDropZone } from "@vueuse/core";
// import { Upload, X } from "lucide-vue-next";

interface FileData {
    file?: File;
    preview?: string;
    id?: number;
    url?: string;
}

interface Props {
    id?: string;
    label?: string;
    acceptTypes?: ("image" | "video" | "pdf" | "xlsx" | "csv")[];
    description?: string;
    maxSize?: number; // in MB
    multiple?: boolean;
    helper?: string;
    error?: string;
    disabled?: boolean;
    required?: boolean;
    withPreview?: boolean;
    modelValue?: FileData | FileData[] | null;
}

interface ValidationError {
    file: string;
    error: string;
}

const props = withDefaults(defineProps<Props>(), {
    acceptTypes: () => ["image"],
    description: "Drag and drop files here or click to browse",
    maxSize: 2,
    multiple: false,
    withPreview: true,
});

const emit = defineEmits<{
    "update:modelValue": [value: FileData | FileData[] | null];
}>();

const fileInput = ref<HTMLInputElement>();
const isDragOver = ref(false);
const validationErrors = ref<ValidationError[]>([]);
const fileData = ref<FileData | FileData[] | null>(props.modelValue || null);

// Accept string for file input
const acceptString = computed(() => {
    const acceptMap: Record<string, string[]> = {
        image: ["image/*"],
        video: ["video/*"],
        pdf: [".pdf", "application/pdf"],
        xlsx: [
            ".xlsx",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        ],
        csv: [".csv", "text/csv"],
    };

    return props.acceptTypes.flatMap((type) => acceptMap[type] || []).join(",");
});

// Preview URL for images
const previewUrl = computed(() => {
    if (Array.isArray(fileData.value)) {
        return fileData.value[0]?.preview || fileData.value[0]?.url;
    }
    return fileData.value?.preview || fileData.value?.url;
});

// Drag and drop handling
// Simplified drag and drop handling
const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = true;
};

const handleDragLeave = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = false;
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = false;
    if (event.dataTransfer?.files) {
        handleFiles(event.dataTransfer.files);
    }
};

// File validation
const isValidFileType = (file: File): boolean => {
    return props.acceptTypes.some((type) => {
        if (type === "pdf") return file.type === "application/pdf";
        if (type === "xlsx")
            return (
                file.type ===
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
            );
        if (type === "csv") return file.type === "text/csv";
        return file.type.startsWith(type);
    });
};

const isValidFileSize = (file: File): boolean => {
    return file.size <= props.maxSize * 1024 * 1024;
};

const validateFile = (file: File): ValidationError | null => {
    if (!isValidFileType(file)) {
        return {
            file: file.name,
            error: `Invalid file type. Allowed: ${props.acceptTypes.join(
                ", "
            )}`,
        };
    }
    if (!isValidFileSize(file)) {
        return {
            file: file.name,
            error: `File too large. Max size: ${props.maxSize}MB`,
        };
    }
    return null;
};

// File handling
const handleFiles = (files: FileList | File[]) => {
    validationErrors.value = [];
    const fileArray = Array.from(files);
    const newFiles: FileData[] = [];

    for (const file of fileArray) {
        const error = validateFile(file);
        if (error) {
            validationErrors.value.push(error);
            continue;
        }

        const fileDataItem: FileData = { file };

        // Generate preview for images
        if (file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = (e) => {
                fileDataItem.preview = e.target?.result as string;
                if (props.multiple) {
                    const currentFiles = Array.isArray(fileData.value)
                        ? fileData.value
                        : [];
                    fileData.value = [...currentFiles, fileDataItem];
                } else {
                    fileData.value = fileDataItem;
                }
                emitUpdate();
            };
            reader.readAsDataURL(file);
        } else {
            newFiles.push(fileDataItem);
        }
    }

    if (newFiles.length > 0) {
        if (props.multiple) {
            const currentFiles = Array.isArray(fileData.value)
                ? fileData.value
                : [];
            fileData.value = [...currentFiles, ...newFiles];
        } else {
            fileData.value = newFiles[0];
        }
        emitUpdate();
    }
};

const triggerFileInput = () => {
    if (!props.disabled) {
        fileInput.value?.click();
    }
};

const onFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        handleFiles(input.files);
        input.value = ""; // Reset input
    }
};

const removeFile = () => {
    fileData.value = null;
    validationErrors.value = [];
    emitUpdate();
};

const emitUpdate = () => {
    emit("update:modelValue", fileData.value);
};

// Watch for external modelValue changes
watch(
    () => props.modelValue,
    (newValue) => {
        fileData.value = newValue;
    }
);

// Initialize with existing value
onMounted(() => {
    if (props.modelValue) {
        fileData.value = props.modelValue;
    }
});

// Helper method to get file name
const getFileName = () => {
    if (Array.isArray(fileData.value)) {
        return fileData.value[0]?.file?.name || "file";
    }
    return fileData.value?.file?.name || "file";
};
</script>
