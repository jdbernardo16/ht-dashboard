# BaseFileUploader Implementation Guide

## Table of Contents

-   [Introduction](#introduction)
-   [Prerequisites](#prerequisites)
-   [Installation and Setup](#installation-and-setup)
-   [Frontend Implementation](#frontend-implementation)
-   [Backend Implementation](#backend-implementation)
-   [Integration Steps](#integration-steps)
-   [Implementation Examples](#implementation-examples)
-   [Customization](#customization)
-   [Testing](#testing)
-   [Troubleshooting](#troubleshooting)
-   [Best Practices](#best-practices)

## Introduction

This implementation guide provides step-by-step instructions for integrating the BaseFileUploader component into a fresh Laravel project without the praxxys backend. The guide covers both frontend and backend implementation, including all necessary components and configurations.

### Target Audience

This guide is intended for developers who:

-   Are familiar with Laravel and Vue.js
-   Need to implement file upload functionality
-   Want to understand the complete file upload workflow
-   Are working on a fresh Laravel project

### What You'll Learn

By following this guide, you will:

-   Set up a complete file upload system
-   Implement the BaseFileUploader component
-   Create backend services for file processing
-   Handle file validation and storage
-   Implement drag-and-drop functionality
-   Create a responsive file upload interface

## Prerequisites

### System Requirements

-   PHP 8.0 or higher
-   Composer
-   Node.js and npm
-   Laravel 9 or higher
-   Vue 3
-   TypeScript

### Required Knowledge

-   Basic Laravel development
-   Vue 3 with Composition API
-   TypeScript fundamentals
-   RESTful API concepts
-   File upload handling

### Dependencies

#### Frontend Dependencies

```json
{
    "dependencies": {
        "vue": "^3.2.0",
        "typescript": "^4.5.0",
        "@vueuse/core": "^9.0.0",
        "lucide-vue-next": "^0.263.0"
    }
}
```

#### Backend Dependencies

```json
{
    "require": {
        "laravel/framework": "^9.0",
        "intervention/image": "^2.7",
        "spatie/laravel-medialibrary": "^10.0"
    }
}
```

## Installation and Setup

### Step 1: Create a New Laravel Project

```bash
# Create a new Laravel project
composer create-project laravel/laravel file-upload-project

# Navigate to the project directory
cd file-upload-project
```

### Step 2: Install Frontend Dependencies

```bash
# Install Node.js dependencies
npm install

# Install required Vue.js packages
npm install vue@next @vueuse/core lucide-vue-next

# Install TypeScript
npm install typescript @types/node @types/vue --save-dev
```

### Step 3: Set Up Vue 3 with TypeScript

Create a `tsconfig.json` file in your project root:

```json
{
    "compilerOptions": {
        "target": "esnext",
        "module": "esnext",
        "moduleResolution": "node",
        "strict": true,
        "jsx": "preserve",
        "esModuleInterop": true,
        "allowSyntheticDefaultImports": true,
        "skipLibCheck": true,
        "forceConsistentCasingInFileNames": true,
        "useDefineForClassFields": true,
        "sourceMap": true,
        "baseUrl": ".",
        "types": ["vite/client"],
        "paths": {
            "@/*": ["resources/ts/*"]
        }
    },
    "include": [
        "resources/ts/**/*.ts",
        "resources/ts/**/*.d.ts",
        "resources/ts/**/*.tsx",
        "resources/ts/**/*.vue"
    ],
    "references": [
        {
            "path": "./tsconfig.node.json"
        }
    ]
}
```

### Step 4: Configure Vite

Update your `vite.config.js` file:

```javascript
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/ts/app.ts"],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "resources/ts"),
        },
    },
});
```

### Step 5: Set Up Database

```bash
# Create a new database
mysql -u root -p
CREATE DATABASE file_upload_db;

# Configure .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=file_upload_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

## Frontend Implementation

### Step 1: Create the BaseFileUploader Component

Create the file `resources/ts/Components/Shared/Fields/BaseFileUploader.vue`:

```vue
<script setup lang="ts">
import { useDropZone } from "@vueuse/core";
import { ref, computed, onMounted, watch } from "vue";
import {
    Paperclip,
    Image,
    Video,
    FileText,
    X,
    ImagePlus,
} from "lucide-vue-next";

interface Props {
    id?: string | undefined;
    label?: string | number;
    acceptTypes: ("image" | "video" | "pdf" | "xlsx" | "csv")[];
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

const images = defineModel<FileData | FileData[]>();
const filesData = ref<FileData[]>([]);
const fileInput = ref<HTMLInputElement | null>(null);
const validationErrors = ref<ValidationError[]>([]);

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

const dropZoneRef = ref<HTMLElement>();

const { isOverDropZone } = useDropZone(dropZoneRef, onDrop);

const acceptString = computed(() => {
    const typeMap = {
        image: "image/*",
        video: "video/*",
        pdf: "application/pdf",
        xlsx: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        csv: "text/csv",
    };
    return props.acceptTypes.map((type) => typeMap[type]).join(",");
});

const isValidFileType = (file: File) => {
    return props.acceptTypes.some((type) => {
        if (type === "pdf") {
            return file.type === "application/pdf";
        }

        if (type == "xlsx") {
            return (
                file.type ===
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
            );
        }

        if (type == "csv") {
            return file.type === "text/csv";
        }

        return file.type.startsWith(type);
    });
};

const isValidFileSize = (file: File) => {
    return file.size <= props.maxSize * 1024 * 1024; // Convert MB to bytes
};

const iconComponent = computed(() => {
    if (props.iconOnly) return ImagePlus;
    if (props.acceptTypes.includes("image")) return Image;
    if (props.acceptTypes.includes("video")) return Video;
    if (props.acceptTypes.includes("pdf")) return FileText;
    return Paperclip;
});

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
                images.value = fileData;
            };
            reader.readAsDataURL(fileData.file);
        } else {
            images.value = fileData;
        }
    });
};

const removeFile = (index: number) => {
    filesData.value.splice(index, 1);
};

const formatBytes = (bytes: number, decimals = 2) => {
    if (bytes === 0) return "0 Bytes";

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
};

watch(
    () => images.value,
    (value) => {
        if (value) {
            if (Array.isArray(value)) {
                filesData.value = value;
                return;
            }
            const vals = Object.keys(value).filter(
                (val) => val == "preview" && value.preview
            );

            if (vals.length) {
                filesData.value = [value];
                return;
            }
        }
    }
);

onMounted(() => {
    const value = images.value;

    if (value) {
        if (Array.isArray(value)) {
            filesData.value = value;
            return;
        }
        const vals = Object.keys(value).filter(
            (val) => val == "preview" && value.preview
        );

        if (vals.length) {
            filesData.value = [value];
            return;
        }
    }

    filesData.value = [];
});
</script>

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
```

### Step 2: Create the useFileUpload Composable

Create the file `resources/ts/Composables/useFileUpload.ts`:

```typescript
import { ref, Ref, UnwrapRef } from "vue";

interface FileObject {
    url?: string;
    name: string;
    type: string;
    isExisting: boolean;
    file?: File;
}

export function useFileUpload(
    data: Ref<any> | object,
    imagePathKey: string,
    fileObjectKey: string
) {
    const getData = () => {
        if ("value" in data) {
            return data.value;
        }
        return data;
    };

    const setData = (newData: UnwrapRef<any>) => {
        if ("value" in data) {
            data.value = newData;
        } else {
            Object.assign(data, newData);
        }
    };

    const initializeFileField = () => {
        const currentData = getData();
        if (currentData[imagePathKey] && !currentData[fileObjectKey]) {
            const existingFile: FileObject = {
                url: currentData[imagePathKey],
                name:
                    currentData[imagePathKey].split("/").pop() || imagePathKey,
                type: "image",
                isExisting: true,
            };
            setData({
                ...currentData,
                [fileObjectKey]: existingFile,
            });
        }
    };

    const handleFileUpload = (fileData: any) => {
        let actualFile: File | null = null;
        const currentData = getData();

        // Extract File object from various possible formats
        if (fileData instanceof File) {
            actualFile = fileData;
        } else if (fileData?.file instanceof File) {
            actualFile = fileData.file;
        } else if (fileData?.data instanceof File) {
            actualFile = fileData.data;
        }

        const newData = {
            ...currentData,
            [fileObjectKey]: actualFile,
        };

        // Handle file removal
        if (
            fileData === null ||
            fileData === undefined ||
            actualFile === null
        ) {
            newData[imagePathKey] = "";
            newData[fileObjectKey] = null;
        }

        setData(newData);
    };

    return {
        initializeFileField,
        handleFileUpload,
    };
}
```

### Step 3: Create a Sample Page Component

Create the file `resources/ts/Pages/Demo/FileUploadDemo.vue`:

```vue
<script setup lang="ts">
import { ref, reactive, onMounted } from "vue";
import BaseFileUploader from "@/Components/Shared/Fields/BaseFileUploader.vue";
import { useFileUpload } from "@/Composables/useFileUpload";

const form = reactive({
    title: "",
    description: "",
    background_image: "",
    background_image_file: null as any,
    document: "",
    document_file: null as any,
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

const { handleFileUpload: handleImageUpload } = useFileUpload(
    form,
    "background_image",
    "background_image_file"
);

const { handleFileUpload: handleDocumentUpload } = useFileUpload(
    form,
    "document",
    "document_file"
);

const submitForm = async () => {
    isSubmitting.value = true;
    errors.value = {};

    try {
        const formData = new FormData();
        formData.append("title", form.title);
        formData.append("description", form.description);

        if (form.background_image_file) {
            formData.append("background_image", form.background_image_file);
        }

        if (form.document_file) {
            formData.append("document", form.document_file);
        }

        const response = await fetch("/api/upload-demo", {
            method: "POST",
            body: formData,
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
            },
        });

        const data = await response.json();

        if (response.ok) {
            alert("Files uploaded successfully!");
            // Reset form or handle success
        } else {
            if (data.errors) {
                errors.value = data.errors;
            } else {
                alert("Upload failed: " + data.message);
            }
        }
    } catch (error) {
        console.error("Upload error:", error);
        alert("An error occurred during upload");
    } finally {
        isSubmitting.value = false;
    }
};

onMounted(() => {
    // Initialize any existing file data
});
</script>

<template>
    <div class="max-w-4xl p-6 mx-auto">
        <h1 class="mb-6 text-2xl font-bold">File Upload Demo</h1>

        <form @submit.prevent="submitForm" class="space-y-6">
            <div>
                <label
                    for="title"
                    class="block text-sm font-medium text-gray-700"
                >
                    Title
                </label>
                <input
                    id="title"
                    v-model="form.title"
                    type="text"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    :class="{ 'border-red-500': errors.title }"
                />
                <p v-if="errors.title" class="mt-1 text-sm text-red-600">
                    {{ errors.title }}
                </p>
            </div>

            <div>
                <label
                    for="description"
                    class="block text-sm font-medium text-gray-700"
                >
                    Description
                </label>
                <textarea
                    id="description"
                    v-model="form.description"
                    rows="3"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    :class="{ 'border-red-500': errors.description }"
                ></textarea>
                <p v-if="errors.description" class="mt-1 text-sm text-red-600">
                    {{ errors.description }}
                </p>
            </div>

            <div>
                <BaseFileUploader
                    v-model="form.background_image_file"
                    @update:model-value="handleImageUpload"
                    label="Background Image"
                    :accept-types="['image']"
                    :max-size="5"
                    :error="errors.background_image"
                    description="Upload a background image (JPG, PNG, WebP)"
                />
            </div>

            <div>
                <BaseFileUploader
                    v-model="form.document_file"
                    @update:model-value="handleDocumentUpload"
                    label="Document"
                    :accept-types="['pdf', 'xlsx', 'csv']"
                    :max-size="10"
                    :error="errors.document"
                    description="Upload a document (PDF, Excel, CSV)"
                />
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="px-4 py-2 text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                    {{ isSubmitting ? "Uploading..." : "Submit" }}
                </button>
            </div>
        </form>
    </div>
</template>
```

### Step 4: Update App.ts

Update your `resources/ts/app.ts` file:

```typescript
import { createApp } from "vue";
import { createPinia } from "pinia";
import FileUploadDemo from "./Pages/Demo/FileUploadDemo.vue";

const app = createApp(FileUploadDemo);
const pinia = createPinia();

app.use(pinia);
app.mount("#app");
```

## Backend Implementation

### Step 1: Create File Upload Service

Create the file `app/Services/FileUploadService.php`:

```php
<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FileUploadService
{
    protected $fileFields = [
        'background_image' => 'background_image_path',
        'document' => 'document_path',
    ];

    public function storeFile(UploadedFile $file, string $directory = 'uploads'): array
    {
        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');

        // Generate URL
        $url = Storage::url($path);

        // Handle image optimization
        if (str_starts_with($file->getMimeType(), 'image/')) {
            $this->optimizeImage($file, $path);
        }

        return [
            'id' => Str::uuid(),
            'url' => $url,
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    public function processFormData(array $data): array
    {
        $processedData = $data;

        foreach ($this->fileFields as $fileField => $pathField) {
            if (isset($processedData[$fileField]) && $processedData[$fileField] instanceof UploadedFile) {
                $fileInfo = $this->storeFile($processedData[$fileField]);
                $processedData[$pathField] = $fileInfo['url'];
                unset($processedData[$fileField]);
            }
        }

        return $processedData;
    }

    protected function optimizeImage(UploadedFile $file, string $path): void
    {
        try {
            $image = Image::make($file);

            // Resize large images
            if ($image->width() > 1920 || $image->height() > 1080) {
                $image->resize(1920, 1080, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Optimize and save
            $image->save(storage_path('app/public/' . $path), 75);
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::error('Image optimization failed: ' . $e->getMessage());
        }
    }

    public function validateFile(UploadedFile $file, array $rules): array
    {
        $validator = \Validator::make(['file' => $file], [
            'file' => $rules,
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->get('file'),
            ];
        }

        return ['valid' => true];
    }

    public function deleteFile(string $path): bool
    {
        try {
            return Storage::disk('public')->delete($path);
        } catch (\Exception $e) {
            \Log::error('File deletion failed: ' . $e->getMessage());
            return false;
        }
    }
}
```

### Step 2: Create Upload Controller

Create the file `app/Http/Controllers/Api/FileUploadController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FileUploadController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function uploadDemo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'document' => 'nullable|file|mimes:pdf,xlsx,csv|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $request->all();

            // Process file uploads
            $processedData = $this->fileUploadService->processFormData($data);

            // Here you would save to database
            // $demo = DemoModel::create($processedData);

            return response()->json([
                'success' => true,
                'message' => 'Files uploaded successfully',
                'data' => $processedData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function uploadSingle(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240',
            'type' => 'required|string|in:image,document,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $type = $request->input('type');

            $directory = match($type) {
                'image' => 'images',
                'document' => 'documents',
                default => 'misc'
            };

            $fileInfo = $this->fileUploadService->storeFile($file, $directory);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => $fileInfo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function uploadMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:10',
            'files.*' => 'file|max:10240',
            'type' => 'required|string|in:image,document,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $files = $request->file('files');
            $type = $request->input('type');

            $directory = match($type) {
                'image' => 'images',
                'document' => 'documents',
                default => 'misc'
            };

            $uploadedFiles = [];

            foreach ($files as $file) {
                $fileInfo = $this->fileUploadService->storeFile($file, $directory);
                $uploadedFiles[] = $fileInfo;
            }

            return response()->json([
                'success' => true,
                'message' => 'Files uploaded successfully',
                'data' => $uploadedFiles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
```

### Step 3: Create API Routes

Update your `routes/api.php` file:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FileUploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// File upload routes
Route::post('/upload-demo', [FileUploadController::class, 'uploadDemo']);
Route::post('/upload/single', [FileUploadController::class, 'uploadSingle']);
Route::post('/upload/multiple', [FileUploadController::class, 'uploadMultiple']);
```

### Step 4: Create Web Route for Demo

Update your `routes/web.php` file:

```php
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/upload-demo', function () {
    return view('upload-demo');
});
```

### Step 5: Create Demo View

Create the file `resources/views/upload-demo.blade.php`:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload Demo</title>
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div id="app"></div>
</body>
</html>
```

## Integration Steps

### Step 1: Install Required Packages

```bash
# Install Intervention Image for image processing
composer require intervention/image

# Install Laravel Media Library (optional)
composer require spatie/laravel-medialibrary

# Publish Intervention Image config
php artisan vendor:publish --provider="Intervention\Image\ImageServiceProviderLaravelRecent"
```

### Step 2: Configure Filesystem

Update your `config/filesystems.php`:

```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

### Step 3: Create Storage Link

```bash
php artisan storage:link
```

### Step 4: Build Frontend Assets

```bash
npm run build
```

### Step 5: Start Development Server

```bash
# Start Laravel development server
php artisan serve

# Start Vite development server (in another terminal)
npm run dev
```

## Implementation Examples

### Example 1: Hero Section with Background Image

```vue
<script setup lang="ts">
import { reactive } from "vue";
import BaseFileUploader from "@/Components/Shared/Fields/BaseFileUploader.vue";
import { useFileUpload } from "@/Composables/useFileUpload";

const heroSection = reactive({
    title: "Welcome to Our Site",
    subtitle: "Discover amazing features",
    background_image: "",
    background_image_file: null as any,
    cta_text: "Get Started",
    cta_link: "/contact",
});

const { handleFileUpload } = useFileUpload(
    heroSection,
    "background_image",
    "background_image_file"
);

const saveHeroSection = async () => {
    // Implementation for saving hero section
    console.log("Saving hero section:", heroSection);
};
</script>

<template>
    <div class="p-6 space-y-6">
        <h2 class="text-xl font-semibold">Hero Section</h2>

        <div>
            <label class="block text-sm font-medium text-gray-700">Title</label>
            <input
                v-model="heroSection.title"
                type="text"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
            />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700"
                >Subtitle</label
            >
            <textarea
                v-model="heroSection.subtitle"
                rows="2"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
            ></textarea>
        </div>

        <div>
            <BaseFileUploader
                v-model="heroSection.background_image_file"
                @update:model-value="handleFileUpload"
                label="Background Image"
                :accept-types="['image']"
                :max-size="5"
                description="Upload a background image for the hero section"
            />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700"
                >CTA Text</label
            >
            <input
                v-model="heroSection.cta_text"
                type="text"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
            />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700"
                >CTA Link</label
            >
            <input
                v-model="heroSection.cta_link"
                type="text"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
            />
        </div>

        <button
            @click="saveHeroSection"
            class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700"
        >
            Save Hero Section
        </button>
    </div>
</template>
```

### Example 2: Product Gallery

```vue
<script setup lang="ts">
import { reactive, ref } from "vue";
import BaseFileUploader from "@/Components/Shared/Fields/BaseFileUploader.vue";
import { useFileUpload } from "@/Composables/useFileUpload";

const product = reactive({
    name: "",
    description: "",
    price: "",
    images: [] as string[],
    images_files: [] as any[],
});

const errors = ref<Record<string, string>>({});

const { handleFileUpload: handleImagesUpload } = useFileUpload(
    product,
    "images",
    "images_files"
);

const saveProduct = async () => {
    // Implementation for saving product
    console.log("Saving product:", product);
};
</script>

<template>
    <div class="p-6 space-y-6">
        <h2 class="text-xl font-semibold">Product Gallery</h2>

        <div>
            <label class="block text-sm font-medium text-gray-700"
                >Product Name</label
            >
            <input
                v-model="product.name"
                type="text"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
            />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700"
                >Description</label
            >
            <textarea
                v-model="product.description"
                rows="3"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
            ></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Price</label>
            <input
                v-model="product.price"
                type="number"
                step="0.01"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
            />
        </div>

        <div>
            <BaseFileUploader
                v-model="product.images_files"
                @update:model-value="handleImagesUpload"
                label="Product Images"
                :accept-types="['image']"
                :max-size="5"
                :multiple="true"
                :error="errors.images"
                description="Upload multiple product images"
            />
        </div>

        <button
            @click="saveProduct"
            class="px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-700"
        >
            Save Product
        </button>
    </div>
</template>
```

### Example 3: Document Management

```vue
<script setup lang="ts">
import { reactive } from "vue";
import BaseFileUploader from "@/Components/Shared/Fields/BaseFileUploader.vue";
import { useFileUpload } from "@/Composables/useFileUpload";

const document = reactive({
    title: "",
    category: "",
    description: "",
    file: "",
    file_file: null as any,
});

const { handleFileUpload } = useFileUpload(document, "file", "file_file");

const saveDocument = async () => {
    // Implementation for saving document
    console.log("Saving document:", document);
};
</script>

<template>
    <div class="p-6 space-y-6">
        <h2 class="text-xl font-semibold">Document Management</h2>

        <div>
            <label class="block text-sm font-medium text-gray-700"
                >Document Title</label
            >
            <input
                v-model="document.title"
                type="text"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
            />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700"
                >Category</label
            >
            <select
                v-model="document.category"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
            >
                <option value="">Select a category</option>
                <option value="report">Report</option>
                <option value="presentation">Presentation</option>
                <option value="spreadsheet">Spreadsheet</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700"
                >Description</label
            >
            <textarea
                v-model="document.description"
                rows="3"
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
            ></textarea>
        </div>

        <div>
            <BaseFileUploader
                v-model="document.file_file"
                @update:model-value="handleFileUpload"
                label="Document File"
                :accept-types="['pdf', 'xlsx', 'csv']"
                :max-size="10"
                description="Upload a document (PDF, Excel, CSV)"
            />
        </div>

        <button
            @click="saveDocument"
            class="px-4 py-2 text-white bg-purple-600 rounded-md hover:bg-purple-700"
        >
            Save Document
        </button>
    </div>
</template>
```

## Customization

### Custom File Types

To add support for additional file types, update the `acceptTypes` prop and validation:

```typescript
// In BaseFileUploader.vue
interface Props {
    acceptTypes: ("image" | "video" | "pdf" | "xlsx" | "csv" | "zip" | "mp3")[];
}

// Update isValidFileType method
const isValidFileType = (file: File) => {
    return props.acceptTypes.some((type) => {
        if (type === "pdf") {
            return file.type === "application/pdf";
        }

        if (type == "xlsx") {
            return (
                file.type ===
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
            );
        }

        if (type == "csv") {
            return file.type === "text/csv";
        }

        if (type == "zip") {
            return (
                file.type === "application/zip" ||
                file.type === "application/x-zip-compressed"
            );
        }

        if (type == "mp3") {
            return file.type === "audio/mpeg";
        }

        return file.type.startsWith(type);
    });
};
```

### Custom Storage Locations

Update the `FileUploadService` to support custom storage locations:

```php
public function storeFile(UploadedFile $file, string $directory = 'uploads', string $disk = 'public'): array
{
    // Generate unique filename
    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
    $path = $file->storeAs($directory, $filename, $disk);

    // Generate URL
    $url = Storage::disk($disk)->url($path);

    return [
        'id' => Str::uuid(),
        'url' => $url,
        'filename' => $file->getClientOriginalName(),
        'path' => $path,
        'size' => $file->getSize(),
        'mime_type' => $file->getMimeType(),
        'disk' => $disk,
    ];
}
```

### Custom Validation Rules

Add custom validation rules in the controller:

```php
public function uploadWithCustomValidation(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'file' => [
            'required',
            'file',
            'max:10240',
            function ($attribute, $value, $fail) {
                // Custom validation logic
                if ($value->getClientOriginalExtension() === 'exe') {
                    $fail('Executable files are not allowed.');
                }

                // Check file content type
                $mimeType = $value->getMimeType();
                if (str_starts_with($mimeType, 'application/x-msdownload')) {
                    $fail('This file type is not allowed.');
                }
            },
        ],
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Process file upload
    // ...
}
```

## Testing

### Frontend Testing

Create a test file `tests/Components/BaseFileUploader.spec.ts`:

```typescript
import { mount } from "@vue/test-utils";
import { describe, it, expect, vi } from "vitest";
import BaseFileUploader from "@/Components/Shared/Fields/BaseFileUploader.vue";

describe("BaseFileUploader", () => {
    it("renders correctly with default props", () => {
        const wrapper = mount(BaseFileUploader);
        expect(wrapper.find("label").exists()).toBe(false);
        expect(wrapper.find(".border-dashed").exists()).toBe(true);
    });

    it("renders label when provided", () => {
        const wrapper = mount(BaseFileUploader, {
            props: {
                label: "Upload File",
            },
        });
        expect(wrapper.find("label").text()).toBe("Upload File");
    });

    it("validates file types", async () => {
        const wrapper = mount(BaseFileUploader, {
            props: {
                acceptTypes: ["image"],
            },
        });

        const validFile = new File([""], "test.jpg", { type: "image/jpeg" });
        const invalidFile = new File([""], "test.pdf", {
            type: "application/pdf",
        });

        // Mock file drop
        await wrapper.vm.onDrop([validFile]);
        expect(wrapper.vm.validationErrors).toHaveLength(0);

        await wrapper.vm.onDrop([invalidFile]);
        expect(wrapper.vm.validationErrors).toHaveLength(1);
    });

    it("validates file sizes", async () => {
        const wrapper = mount(BaseFileUploader, {
            props: {
                maxSize: 1, // 1MB
            },
        });

        // Create a 2MB file
        const largeFile = new File(["x".repeat(2 * 1024 * 1024)], "large.jpg", {
            type: "image/jpeg",
        });

        await wrapper.vm.onDrop([largeFile]);
        expect(wrapper.vm.validationErrors).toHaveLength(1);
        expect(wrapper.vm.validationErrors[0].error).toContain(
            "exceeds 1MB limit"
        );
    });
});
```

### Backend Testing

Create a test file `tests/Feature/FileUploadTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Configure storage for testing
        Storage::fake('public');
    }

    public function test_file_upload_success()
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);

        $service = new FileUploadService();
        $result = $service->storeFile($file, 'test-uploads');

        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertStringContainsString('test-uploads', $result['path']);

        // Check file was stored
        Storage::disk('public')->assertExists($result['path']);
    }

    public function test_file_validation()
    {
        $file = UploadedFile::fake()->create('test.exe', 100);

        $service = new FileUploadService();
        $validation = $service->validateFile($file, ['image', 'mimes:jpeg,png']);

        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['errors']);
    }

    public function test_api_upload_endpoint()
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->postJson('/api/upload/single', [
            'file' => $file,
            'type' => 'image',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'url',
                'filename',
            ],
        ]);
    }

    public function test_api_upload_validation()
    {
        $response = $this->postJson('/api/upload/single', [
            'file' => 'invalid',
            'type' => 'image',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors',
        ]);
    }
}
```

## Troubleshooting

### Common Issues

#### 1. Files Not Uploading

**Problem**: Files are not being uploaded to the server.

**Solution**:

-   Check if the `enctype="multipart/form-data"` is set on your form
-   Verify that your file input has the correct `name` attribute
-   Check browser console for JavaScript errors
-   Verify network requests in browser dev tools

#### 2. File Size Limit Exceeded

**Problem**: Large files are not being uploaded.

**Solution**:

-   Check PHP configuration for `upload_max_filesize` and `post_max_size`
-   Update your `.htaccess` file if needed:
    ```apache
    php_value upload_max_filesize 20M
    php_value post_max_size 20M
    ```
-   Verify Laravel validation rules

#### 3. File Type Validation Failing

**Problem**: Valid files are being rejected.

**Solution**:

-   Check the MIME type mapping in `isValidFileType` method
-   Verify that the file's actual MIME type matches the expected type
-   Update the `acceptTypes` prop to include the correct file types

#### 4. Storage Issues

**Problem**: Files are not being stored correctly.

**Solution**:

-   Run `php artisan storage:link` to create the symbolic link
-   Check filesystem permissions
-   Verify the storage disk configuration
-   Ensure the storage directory is writable

#### 5. CORS Issues

**Problem**: Cross-origin requests are being blocked.

**Solution**:

-   Configure CORS in your Laravel application
-   Update your `config/cors.php` file:
    ```php
    'paths' => ['api/*', 'upload/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_headers' => ['*'],
    ```

### Debugging Tips

1. **Enable Debug Mode**: Set `APP_DEBUG=true` in your `.env` file
2. **Check Logs**: Monitor `storage/logs/laravel.log` for errors
3. **Network Tab**: Use browser dev tools to inspect network requests
4. **Database Logging**: Log database queries for debugging
5. **File Monitoring**: Monitor file uploads in real-time

## Best Practices

### Security

1. **Validate Files**: Always validate file types and sizes on both frontend and backend
2. **Sanitize Filenames**: Remove special characters from filenames
3. **Use Secure Storage**: Store files outside the public directory when possible
4. **Scan for Malware**: Implement virus scanning for uploaded files
5. **Limit Access**: Use proper authentication and authorization

### Performance

1. **Optimize Images**: Compress and resize images before storing
2. **Use CDN**: Serve files through a Content Delivery Network
3. **Implement Caching**: Cache frequently accessed files
4. **Lazy Loading**: Load file previews only when needed
5. **Background Processing**: Process large files in background jobs

### User Experience

1. **Provide Feedback**: Show upload progress and status
2. **Handle Errors Gracefully**: Display user-friendly error messages
3. **Support Drag-and-Drop**: Implement intuitive file selection
4. **Show Previews**: Display image previews before upload
5. **Allow Multiple Uploads**: Support batch file uploads when appropriate

### Code Organization

1. **Use Services**: Encapsulate file logic in service classes
2. **Implement Interfaces**: Use interfaces for better testability
3. **Follow Naming Conventions**: Use consistent naming patterns
4. **Document Code**: Add comprehensive comments and documentation
5. **Write Tests**: Implement unit and integration tests

This implementation guide provides a complete walkthrough for setting up the BaseFileUploader component in a fresh Laravel project. By following these steps, you'll have a fully functional file upload system with drag-and-drop support, validation, and proper error handling.
