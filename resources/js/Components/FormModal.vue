<template>
    <Modal :show="show" @close="handleClose" :max-width="maxWidth">
        <div class="bg-white rounded-lg shadow-xl">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ title }}
                </h3>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit">
                <div class="px-6 py-4">
                    <slot
                        name="form"
                        :form="form"
                        :errors="errors"
                        :loading="loading"
                    >
                        <!-- Default form content -->
                        <div class="space-y-4">
                            <div v-for="field in fields" :key="field.name">
                                <label
                                    :for="field.name"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    {{ field.label }}
                                    <span
                                        v-if="field.required"
                                        class="text-red-500"
                                        >*</span
                                    >
                                </label>

                                <!-- Text Input -->
                                <input
                                    v-if="
                                        field.type === 'text' ||
                                        field.type === 'email' ||
                                        field.type === 'number' ||
                                        field.type === 'date' ||
                                        field.type === 'datetime-local'
                                    "
                                    :type="field.type"
                                    :id="field.name"
                                    v-model="form[field.name]"
                                    :required="field.required"
                                    :placeholder="field.placeholder"
                                    :min="field.min"
                                    :max="field.max"
                                    :step="field.step"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{
                                        'border-red-300': errors[field.name],
                                    }"
                                />

                                <!-- Textarea -->
                                <textarea
                                    v-else-if="field.type === 'textarea'"
                                    :id="field.name"
                                    v-model="form[field.name]"
                                    :required="field.required"
                                    :placeholder="field.placeholder"
                                    :rows="field.rows || 3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{
                                        'border-red-300': errors[field.name],
                                    }"
                                />

                                <!-- Select -->
                                <select
                                    v-else-if="field.type === 'select'"
                                    :id="field.name"
                                    v-model="form[field.name]"
                                    :required="field.required"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{
                                        'border-red-300': errors[field.name],
                                    }"
                                >
                                    <option value="" disabled>
                                        {{
                                            field.placeholder ||
                                            "Select an option"
                                        }}
                                    </option>
                                    <option
                                        v-for="option in field.options || []"
                                        :key="option?.value || option"
                                        :value="option?.value || option"
                                    >
                                        {{ option?.label || option }}
                                    </option>
                                </select>

                                <!-- Checkbox -->
                                <div
                                    v-else-if="field.type === 'checkbox'"
                                    class="mt-2"
                                >
                                    <label class="inline-flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form[field.name]"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <span
                                            class="ml-2 text-sm text-gray-700"
                                            >{{ field.label }}</span
                                        >
                                    </label>
                                </div>

                                <!-- File Input -->
                                <input
                                    v-else-if="field.type === 'file'"
                                    :id="field.name"
                                    type="file"
                                    :accept="field.accept"
                                    @change="
                                        handleFileChange($event, field.name)
                                    "
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                />

                                <!-- Error Message -->
                                <p
                                    v-if="errors[field.name]"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ errors[field.name] }}
                                </p>
                            </div>
                        </div>
                    </slot>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button
                        type="button"
                        @click="handleClose"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        :disabled="loading"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        :disabled="loading"
                    >
                        <svg
                            v-if="loading"
                            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        {{ loading ? "Saving..." : submitText }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { ref, watch } from "vue";
import Modal from "./Modal.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    fields: {
        type: Array,
        default: () => [],
    },
    initialData: {
        type: Object,
        default: () => ({}),
    },
    submitText: {
        type: String,
        default: "Save",
    },
    maxWidth: {
        type: String,
        default: "2xl",
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close", "submit"]);

const form = ref({});
const errors = ref({});

// Initialize form with initial data
const initializeForm = () => {
    const initial = {};
    props.fields.forEach((field) => {
        initial[field.name] =
            props.initialData[field.name] || field.default || "";
    });
    form.value = initial;
    errors.value = {};
};

// Watch for changes in initialData
watch(() => props.initialData, initializeForm, { immediate: true });

const handleClose = () => {
    if (!props.loading) {
        emit("close");
    }
};

const handleSubmit = () => {
    if (!props.loading) {
        emit("submit", form.value);
    }
};

const handleFileChange = (event, fieldName) => {
    const file = event.target.files[0];
    if (file) {
        form.value[fieldName] = file;
    }
};

// Reset form when modal opens
watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            initializeForm();
        }
    }
);

// Expose methods for parent components
const setErrors = (newErrors) => {
    errors.value = newErrors;
};

const resetForm = () => {
    initializeForm();
};

defineExpose({
    setErrors,
    resetForm,
});
</script>
