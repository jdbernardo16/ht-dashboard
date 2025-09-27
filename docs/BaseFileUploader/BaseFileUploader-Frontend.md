# BaseFileUploader Frontend Documentation

## Table of Contents

-   [Component Overview](#component-overview)
-   [Props Interface](#props-interface)
-   [Data Structures](#data-structures)
-   [Component Methods](#component-methods)
-   [Template Structure](#template-structure)
-   [Styling and UI](#styling-and-ui)
-   [File Validation](#file-validation)
-   [Integration with useFileUpload](#integration-with-usefileupload)
-   [Usage Examples](#usage-examples)

## Component Overview

The BaseFileUploader is a Vue 3 component written in TypeScript that provides a comprehensive file upload interface with drag-and-drop functionality. It's designed to be reusable across different parts of the application with configurable options for file types, sizes, and display modes.

**Location**: `vendor/praxxys/backend/resources/ts/Components/Shared/Fields/BaseFileUploader.vue`

### Key Features

-   **Drag-and-drop file upload** using VueUse's `useDropZone` composable
-   **File type validation** supporting images, videos, PDFs, Excel files, and CSVs
-   **File size validation** with configurable maximum size
-   **Preview generation** for image files using FileReader API
-   **Multiple file support** with optional single file restriction
-   **Icon-only mode** for compact display in forms
-   **Comprehensive error handling** with user-friendly error messages
-   **Reactive state management** with Vue 3's Composition API

### Dependencies

```typescript
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
import { Label as UiLabel } from "PRXBackend/Components/ui/label";
```

## Props Interface

The component accepts the following props with TypeScript type definitions:

```typescript
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
```

### Default Values

```typescript
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
```

### Props Description

| Prop           | Type                                                 | Default                    | Description                                          |
| -------------- | ---------------------------------------------------- | -------------------------- | ---------------------------------------------------- |
| `id`           | `string \| undefined`                                | `undefined`                | Unique identifier for the input element              |
| `label`        | `string \| number`                                   | `undefined`                | Label text displayed above the upload area           |
| `acceptTypes`  | `("image" \| "video" \| "pdf" \| "xlsx" \| "csv")[]` | `["image"]`                | Array of accepted file types                         |
| `description`  | `string`                                             | `"Files (WebP) up to 2MB"` | Helper text describing accepted files                |
| `maxSize`      | `number`                                             | `50`                       | Maximum file size in megabytes                       |
| `multiple`     | `boolean`                                            | `false`                    | Whether multiple files can be selected               |
| `helper`       | `string \| number`                                   | `undefined`                | Additional helper text displayed below the component |
| `error`        | `string`                                             | `undefined`                | Error message to display                             |
| `disabled`     | `boolean`                                            | `undefined`                | Whether the component is disabled                    |
| `required`     | `boolean`                                            | `undefined`                | Whether the field is required                        |
| `labelClass`   | `string`                                             | `undefined`                | CSS classes for the label element                    |
| `withPreview`  | `boolean`                                            | `true`                     | Whether to show file previews                        |
| `iconOnly`     | `boolean`                                            | `false`                    | Whether to show only the upload icon                 |
| `iconClass`    | `string`                                             | `"size-8 mb-2"`            | CSS classes for the icon element                     |
| `iconImgClass` | `string`                                             | `""`                       | CSS classes for the preview image in icon-only mode  |

## Data Structures

### FileData Interface

```typescript
interface FileData {
    file: File;
    preview?: string;
}
```

Represents a file with its optional preview URL. The preview is generated for image files using the FileReader API.

### ValidationError Interface

```typescript
interface ValidationError {
    file: string;
    error: string;
}
```

Represents a validation error with the file name and error message.

### Reactive References

```typescript
const images = defineModel<FileData | FileData[]>();
const filesData = ref<FileData[]>([]);
const fileInput = ref<HTMLInputElement | null>(null);
const validationErrors = ref<ValidationError[]>([]);
```

-   `images`: Two-way binding for file data (single file or array)
-   `filesData`: Internal array of file data with previews
-   `fileInput`: Reference to the hidden file input element
-   `validationErrors`: Array of validation errors for display

## Component Methods

### onDrop(files: File[] | null)

Handles file drop events and processes the files:

```typescript
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
```

### isValidFileType(file: File)

Validates if a file matches the accepted types:

```typescript
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
```

### isValidFileSize(file: File)

Validates if a file is within the size limit:

```typescript
const isValidFileSize = (file: File) => {
    return file.size <= props.maxSize * 1024 * 1024; // Convert MB to bytes
};
```

### generatePreviews()

Generates preview URLs for image files:

```typescript
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
```

### removeFile(index: number)

Removes a file from the upload list:

```typescript
const removeFile = (index: number) => {
    filesData.value.splice(index, 1);
};
```

### formatBytes(bytes: number, decimals = 2)

Formats file size in human-readable format:

```typescript
const formatBytes = (bytes: number, decimals = 2) => {
    if (bytes === 0) return "0 Bytes";

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
};
```

### onFilesSelected(event: Event)

Handles file selection from the file input:

```typescript
const onFilesSelected = (event: Event) => {
    const files = (event.target as HTMLInputElement).files;
    if (files) {
        onDrop(Array.from(files));
    }
};
```

### openFileDialog()

Opens the file selection dialog:

```typescript
const openFileDialog = () => {
    fileInput.value?.click();
};
```

## Template Structure

The component template is structured into several main sections:

### Label Section

```vue
<ui-label v-if="label" :for="id">
    {{ label }}
    <span v-if="required" class="text-destructive">*</span>
</ui-label>
```

### Drop Zone Section

```vue
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
    <!-- Hidden file input -->
    <input
        ref="fileInput"
        type="file"
        class="hidden"
        @change="onFilesSelected"
        :multiple="multiple"
        :accept="acceptString"
    />
    
    <!-- Icon or preview image -->
    <img
        v-if="iconOnly && filesData?.length && filesData[0]?.preview"
        :src="filesData[0].preview"
        alt="preview"
        class="object-cover rounded-md size-10 shrink-0"
        :class="iconImgClass"
    />
    <component v-else :is="iconComponent" :class="iconClass" />
    
    <!-- Text content (hidden in icon-only mode) -->
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
```

### File Previews Section

```vue
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
```

### Helper Text and Error Messages

```vue
<p v-if="helper" class="text-xs text-muted-foreground" v-html="helper"></p>
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
<p
    test-id="upload-error"
    v-if="error"
    class="text-xs text-destructive"
>{{ error }}</p>
```

## Styling and UI

### Dynamic Classes

The component uses dynamic class binding for responsive styling:

```typescript
// Drop zone classes
:class="[
    error ? 'border-destructive' : '',
    disabled ? 'pointer-events-none opacity-50' : '',
    iconOnly
        ? filesData?.length && filesData[0].preview
            ? 'px-2 py-1.5'
            : 'px-3 py-2.5'
        : 'px-4 py-3 min-h-[150px]',
]"
```

### Icon Selection

The component dynamically selects the appropriate icon based on accepted file types:

```typescript
const iconComponent = computed(() => {
    if (props.iconOnly) return ImagePlus;
    if (props.acceptTypes.includes("image")) return Image;
    if (props.acceptTypes.includes("video")) return Video;
    if (props.acceptTypes.includes("pdf")) return FileText;
    return Paperclip;
});
```

### Accept String Generation

The component generates the appropriate `accept` attribute value for the file input:

```typescript
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
```

## File Validation

### Type Validation

The component validates file types against the `acceptTypes` prop:

```typescript
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
```

### Size Validation

The component validates file sizes against the `maxSize` prop:

```typescript
const isValidFileSize = (file: File) => {
    return file.size <= props.maxSize * 1024 * 1024; // Convert MB to bytes
};
```

### Error Handling

Validation errors are collected and displayed to the user:

```typescript
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
```

## Integration with useFileUpload

The component is designed to work with the `useFileUpload` composable for state management:

```typescript
import { useFileUpload } from "@/Composables/useFileUpload";

const { handleFileUpload } = useFileUpload(
    data,
    "background_image", // Path key
    "background_image_file" // File object key
);
```

### Usage in Parent Component

```vue
<base-file-uploader
    :model-value="data.background_image_file"
    @update:model-value="handleFileUpload"
    :path="data.background_image"
    label="Background Image"
    :accept-types="['image']"
    :error="errors[getErrorKey('background_image_file')]"
    description="Upload a background image for the hero section"
/>
```

## Usage Examples

### Basic Image Upload

```vue
<base-file-uploader
    v-model="imageFile"
    label="Product Image"
    :accept-types="['image']"
    :max-size="5"
    description="Upload a product image (JPG, PNG, WebP)"
/>
```

### Multiple File Upload

```vue
<base-file-uploader
    v-model="documentFiles"
    label="Documents"
    :accept-types="['pdf', 'xlsx', 'csv']"
    :max-size="10"
    :multiple="true"
    description="Upload multiple documents"
/>
```

### Icon-only Mode

```vue
<base-file-uploader
    v-model="logoFile"
    :accept-types="['image']"
    :max-size="2"
    :icon-only="true"
    icon-class="size-12"
    icon-img-class="rounded-full"
/>
```

### With Validation Errors

```vue
<base-file-uploader
    v-model="profileImage"
    label="Profile Image"
    :accept-types="['image']"
    :max-size="2"
    :error="errors.profile_image"
    :required="true"
    description="Upload your profile image"
/>
```

### With Helper Text

```vue
<base-file-uploader
    v-model="bannerImage"
    label="Banner Image"
    :accept-types="['image']"
    :max-size="5"
    helper="Recommended size: 1200x400 pixels"
    description="Upload a banner image for the homepage"
/>
```

This comprehensive frontend documentation provides all the necessary information for developers to understand, use, and extend the BaseFileUploader component in their applications.
