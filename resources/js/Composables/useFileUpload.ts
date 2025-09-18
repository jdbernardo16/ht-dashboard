import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";

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

export const useFileUpload = (options: FileUploadOptions): FileUploadResult => {
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
