<template>
    <div class="space-y-1">
        <label
            v-if="label"
            :for="id"
            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
        >
            {{ label }}
            <span v-if="required" class="text-destructive">*</span>
        </label>
        <div
            ref="dropZoneRef"
            @click="openFileDialog"
            class="flex flex-col items-center justify-center text-center transition border-2 border-dashed rounded-lg cursor-pointer hover:border-muted-foreground"
            :class="[
                error ? 'border-destructive' : '',
                disabled ? 'pointer-events-none opacity-50' : '',
                iconOnly
                    ? filesData?.length && filesData[0].preview
                        ? 'px-2 py-1.5'
                        : 'px-3 py-2.5'
                    : 'px-4 py-3 min-h-[150px]',
            ]"
        >
            <input
                ref="fileInput"
                type="file"
                class="hidden"
                @change="onFilesSelected"
                :multiple="multiple"
                :accept="acceptString"
            />
            <!-- Icon -->
            <img
                v-if="iconOnly && filesData?.length && filesData[0]?.preview"
                :src="filesData[0].preview"
                alt="preview"
                class="object-cover rounded-md size-10 shrink-0"
                :class="iconImgClass"
            />
            <component v-else :is="iconComponent" :class="iconClass" />

            <!-- Text Content -->
            <template v-if="!iconOnly">
                <p class="mb-1 text-xs font-medium text-muted-foreground">
                    <span class="font-semibold text-primary">
                        {{
                            props.multiple
                                ? "Select multiple files"
                                : "Select a file"
                        }}
                    </span>
                    or drag and drop
                </p>
                <p class="text-xs text-muted-foreground">{{ description }}</p>
            </template>
        </div>
        <div v-if="filesData && withPreview && !iconOnly">
            <!-- File Previews -->
            <div class="space-y-2">
                <div
                    v-for="(fileData, index) in filesData"
                    :key="index"
                    class="relative flex items-center justify-between p-3 space-x-2 border rounded-lg"
                >
                    <div class="flex items-center">
                        <img
                            v-if="fileData.preview"
                            :src="fileData.preview"
                            alt="preview"
                            class="object-cover rounded-md size-10 shrink"
                        />
                        <div
                            v-else
                            class="flex items-center justify-center rounded-lg size-10 bg-border"
                        >
                            <component
                                :is="iconComponent"
                                class="size-8 text-muted-foreground"
                            />
                        </div>
                        <div
                            class="ml-2 text-xs space-y-1 max-w-[80%] text-ellipsis"
                        >
                            <p class="truncate">{{ fileData?.file?.name }}</p>
                            <p class="text-muted-foreground">
                                {{ formatBytes(fileData?.file?.size) }}
                            </p>
                        </div>
                    </div>
                    <X
                        @click.stop="removeFile(index)"
                        class="ml-auto cursor-pointer size-5 text-destructive shrink-0"
                    />
                </div>
            </div>
        </div>
        <p
            v-if="helper"
            class="text-xs text-muted-foreground"
            v-html="helper"
        ></p>
        <!-- Validation Errors -->
        <div v-if="validationErrors.length > 0" class="text-destructive">
            <p
                v-for="(error, index) in validationErrors"
                :key="index"
                class="text-xs"
                test-id="fileType-error"
            >
                {{ error.file }}: {{ error.error }}
            </p>
        </div>
        <p test-id="upload-error" v-if="error" class="text-xs text-destructive">
            {{ error }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";

// Try to import VueUse, but handle if it's not available
let useDropZone: any;
try {
    const vueuse = require("@vueuse/core");
    useDropZone = vueuse.useDropZone;
} catch (e) {
    console.warn("@vueuse/core not available, using fallback drag and drop");
}

// Try to import Lucide icons, but handle if they're not available
let Paperclip: any,
    Image: any,
    Video: any,
    FileText: any,
    X: any,
    ImagePlus: any;
try {
    const lucide = require("lucide-vue-next");
    Paperclip = lucide.Paperclip;
    Image = lucide.Image;
    Video = lucide.Video;
    FileText = lucide.FileText;
    X = lucide.X;
    ImagePlus = lucide.ImagePlus;
} catch (e) {
    console.warn("lucide-vue-next not available, using fallback icons");
    // Simple fallback icons as SVG components
    Paperclip = {
        template: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>`,
    };
    Image = {
        template: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>`,
    };
    Video = {
        template: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>`,
    };
    FileText = {
        template: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>`,
    };
    X = {
        template: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`,
    };
    ImagePlus = {
        template: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>`,
    };
}

interface Props {
    id?: string | undefined;
    label?: string | number;
    acceptTypes?: ("image" | "video" | "pdf" | "xlsx" | "csv")[];
    description?: string;
    maxSize?: number; // in MB
    multiple?: boolean;
    helper?: string | number;
    error?: string;
    disabled?: boolean;
    required?: boolean;
    labelClass?: string;
    withPreview?: boolean;
    iconOnly?: boolean;
    iconClass?: string;
    iconImgClass?: string;
    modelValue?: any;
}

interface FileData {
    file: File;
    preview?: string;
}

interface ValidationError {
    file: string;
    error: string;
}

const props = withDefaults(defineProps<Props>(), {
    acceptTypes: () => ["image"],
    description: "Files (WebP) up to 2MB",
    maxSize: 50,
    multiple: false,
    withPreview: true,
    iconOnly: false,
    iconClass: "size-8 mb-2",
    iconImgClass: "",
});

const emit = defineEmits<{
    "update:modelValue": [value: FileData | FileData[] | null];
}>();

const dropZoneRef = ref<HTMLElement>();
const fileInput = ref<HTMLInputElement | null>(null);
const filesData = ref<FileData[]>([]);
const validationErrors = ref<ValidationError[]>([]);

const acceptString = computed(() => {
    const typeMap = {
        image: "image/jpeg,image/png,image/jpg,image/gif,image/svg+xml,image/webp",
        video: "video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv",
        pdf: "application/pdf",
        xlsx: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel",
        csv: "text/csv",
    };
    return props.acceptTypes.map((type) => typeMap[type]).join(",");
});

const iconComponent = computed(() => {
    if (props.iconOnly) return ImagePlus;
    if (props.acceptTypes.includes("image")) return Image;
    if (props.acceptTypes.includes("video")) return Video;
    if (props.acceptTypes.includes("pdf")) return FileText;
    return Paperclip;
});

const onDrop = (files: File[] | null) => {
    if (!files) return;

    validationErrors.value = [];
    const validFiles: FileData[] = [];

    files.forEach((file) => {
        if (!isValidFileType(file)) {
            validationErrors.value.push({
                file: file.name,
                error: "File type not accepted",
            });
        } else if (!isValidFileSize(file)) {
            validationErrors.value.push({
                file: file.name,
                error: `File size exceeds ${props.maxSize}MB limit`,
            });
        } else {
            validFiles.push({ file });
        }
    });

    if (!props.multiple && validFiles.length > 1) {
        validationErrors.value.push({
            file: "Multiple files",
            error: "Only single file upload is allowed",
        });
        filesData.value = validFiles.slice(0, 1);
    } else {
        filesData.value = validFiles;
    }

    generatePreviews();
};

// Initialize useDropZone after onDrop is defined
let isOverDropZone = ref(false);
if (useDropZone) {
    const dropZoneResult = useDropZone(dropZoneRef, onDrop);
    isOverDropZone = dropZoneResult.isOverDropZone;
}

const isValidFileType = (file: File) => {
    return props.acceptTypes.some((type) => {
        if (type === "pdf") {
            return file.type === "application/pdf";
        }

        if (type === "xlsx") {
            return (
                file.type ===
                    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" ||
                file.type === "application/vnd.ms-excel"
            );
        }

        if (type === "csv") {
            return file.type === "text/csv";
        }

        if (type === "video") {
            return [
                "video/mp4",
                "video/quicktime",
                "video/x-msvideo",
                "video/x-ms-wmv",
            ].includes(file.type);
        }

        if (type === "image") {
            return [
                "image/jpeg",
                "image/png",
                "image/jpg",
                "image/gif",
                "image/svg+xml",
                "image/webp",
            ].includes(file.type);
        }

        return file.type.startsWith(type);
    });
};

const isValidFileSize = (file: File) => {
    return file.size <= props.maxSize * 1024 * 1024; // Convert MB to bytes
};

const onFilesSelected = (event: Event) => {
    const files = (event.target as HTMLInputElement).files;
    if (files) {
        onDrop(Array.from(files));
    }
};

const openFileDialog = () => {
    fileInput.value?.click();
};

const generatePreviews = async () => {
    filesData.value.forEach((fileData) => {
        if (fileData.file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = (e) => {
                fileData.preview = e.target?.result as string;
                updateModelValue();
            };
            reader.readAsDataURL(fileData.file);
        } else {
            updateModelValue();
        }
    });
};

const removeFile = (index: number) => {
    filesData.value.splice(index, 1);
    updateModelValue();
};

const formatBytes = (bytes: number, decimals = 2) => {
    if (bytes === 0) return "0 Bytes";

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
};

const updateModelValue = () => {
    if (props.multiple) {
        emit(
            "update:modelValue",
            filesData.value.length > 0 ? filesData.value : null
        );
    } else {
        emit(
            "update:modelValue",
            filesData.value.length > 0 ? filesData.value[0] : null
        );
    }
};

// Watch for external modelValue changes
watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue) {
            if (Array.isArray(newValue)) {
                filesData.value = newValue;
            } else {
                filesData.value = [newValue];
            }
        } else {
            filesData.value = [];
        }
    }
);

// Initialize with existing value
onMounted(() => {
    if (props.modelValue) {
        if (Array.isArray(props.modelValue)) {
            filesData.value = props.modelValue;
        } else {
            filesData.value = [props.modelValue];
        }
    }
});
</script>
