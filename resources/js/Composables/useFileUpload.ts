import { ref, computed, Ref, UnwrapRef } from "vue";

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

// Keep the original useFileUpload for backward compatibility
interface FileUploadOptions {
    fieldName: string;
    multiple?: boolean;
    maxSize?: number;
    acceptTypes?: string[];
}

interface FileUploadResult {
    files: any;
    errors: any;
    handleFileUpload: (files: FileList | File[]) => void;
    clearFiles: () => void;
    getFormData: () => FormData;
    hasFiles: boolean;
    fileCount: number;
}

export const useFileUploadLegacy = (
    options: FileUploadOptions
): FileUploadResult => {
    const {
        fieldName,
        multiple = false,
        maxSize = 10 * 1024 * 1024,
        acceptTypes = ["image/*"],
    } = options;

    const files = ref<File[]>([]);
    const errors = ref<string[]>([]);

    const hasFiles = computed(() => files.value.length > 0);
    const fileCount = computed(() => files.value.length);

    const isValidFileType = (file: File): boolean => {
        if (!acceptTypes.length || acceptTypes.includes("*")) return true;

        return acceptTypes.some((acceptType) => {
            if (acceptType.startsWith(".")) {
                const extension = acceptType.toLowerCase();
                const fileExtension =
                    "." + file.name.split(".").pop()?.toLowerCase();
                return fileExtension === extension;
            }

            if (acceptType.endsWith("/*")) {
                const category = acceptType.split("/")[0];
                return file.type.startsWith(category);
            }

            // Handle specific MIME types
            if (
                acceptType ===
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
            ) {
                return (
                    file.type ===
                        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" ||
                    file.type === "application/vnd.ms-excel"
                );
            }

            if (acceptType === "image/*") {
                return [
                    "image/jpeg",
                    "image/png",
                    "image/jpg",
                    "image/gif",
                    "image/svg+xml",
                    "image/webp",
                ].includes(file.type);
            }

            if (acceptType === "video/*") {
                return [
                    "video/mp4",
                    "video/quicktime",
                    "video/x-msvideo",
                    "video/x-ms-wmv",
                ].includes(file.type);
            }

            return file.type === acceptType;
        });
    };

    const isValidFileSize = (file: File): boolean => {
        return file.size <= maxSize;
    };

    const validateFile = (file: File): string | null => {
        if (!isValidFileType(file)) {
            return `File type not allowed: ${
                file.name
            }. Allowed types: ${acceptTypes.join(", ")}`;
        }

        if (!isValidFileSize(file)) {
            return `File too large: ${
                file.name
            }. Maximum size: ${formatFileSize(maxSize)}`;
        }

        return null;
    };

    const handleFileUpload = (fileList: FileList | File[]) => {
        errors.value = [];
        const newFiles = Array.from(fileList);
        const validFiles: File[] = [];

        for (const file of newFiles) {
            const error = validateFile(file);
            if (error) {
                errors.value.push(error);
                continue;
            }

            validFiles.push(file);
        }

        if (multiple) {
            files.value = [...files.value, ...validFiles];
        } else {
            files.value = validFiles.length > 0 ? [validFiles[0]] : [];
        }
    };

    const clearFiles = () => {
        files.value = [];
        errors.value = [];
    };

    const getFormData = (): FormData => {
        const formData = new FormData();

        if (multiple) {
            files.value.forEach((file, index) => {
                formData.append(`${fieldName}[${index}]`, file);
            });
        } else if (files.value.length > 0) {
            formData.append(fieldName, files.value[0]);
        }

        return formData;
    };

    const formatFileSize = (bytes: number): string => {
        if (bytes === 0) return "0 Bytes";
        const k = 1024;
        const sizes = ["Bytes", "KB", "MB", "GB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
    };

    return {
        files,
        errors,
        handleFileUpload,
        clearFiles,
        getFormData,
        hasFiles: hasFiles.value,
        fileCount: fileCount.value,
    };
};

export default useFileUpload;
