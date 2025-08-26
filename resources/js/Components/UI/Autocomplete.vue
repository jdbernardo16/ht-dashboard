<template>
    <div class="relative">
        <!-- Input field -->
        <div class="relative">
            <input
                type="text"
                :value="displayValue"
                @input="handleInput"
                @focus="showSuggestions = true"
                @blur="handleBlur"
                @keydown="handleKeydown"
                :placeholder="placeholder"
                :disabled="disabled"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                :class="inputClass"
                ref="inputRef"
            />
            <div
                v-if="loading"
                class="absolute inset-y-0 right-0 flex items-center pr-3"
            >
                <svg
                    class="animate-spin h-5 w-5 text-gray-400"
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
            </div>
        </div>

        <!-- Suggestions dropdown -->
        <div
            v-if="showSuggestions && suggestions.length > 0"
            class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200 max-h-60 overflow-auto"
        >
            <ul class="py-1">
                <li
                    v-for="(suggestion, index) in suggestions"
                    :key="suggestion.id"
                    @mousedown="selectSuggestion(suggestion)"
                    class="px-4 py-2 cursor-pointer hover:bg-gray-100 transition-colors"
                    :class="{
                        'bg-gray-100': index === highlightedIndex,
                        'text-blue-600': index === highlightedIndex,
                    }"
                >
                    <div class="font-medium">{{ suggestion.name }}</div>
                    <div class="text-sm text-gray-500">
                        {{ suggestion.email }}
                    </div>
                </li>
            </ul>
        </div>

        <!-- Add new customer option -->
        <div
            v-if="showSuggestions && showAddNewOption"
            class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200"
        >
            <div
                @mousedown="handleAddNew"
                class="px-4 py-2 cursor-pointer hover:bg-gray-100 transition-colors border-t border-gray-100"
                :class="{
                    'bg-gray-100': highlightedIndex === suggestions.length,
                    'text-blue-600': highlightedIndex === suggestions.length,
                }"
            >
                <div class="font-medium">
                    + Add new customer: "{{ searchQuery }}"
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from "vue";
import axios from "axios";

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: "",
    },
    placeholder: {
        type: String,
        default: "Search customers...",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    inputClass: {
        type: String,
        default: "",
    },
    allowCreate: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["update:modelValue", "select", "create"]);

const inputRef = ref(null);
const searchQuery = ref("");
const suggestions = ref([]);
const showSuggestions = ref(false);
const loading = ref(false);
const highlightedIndex = ref(-1);
const selectedCustomer = ref(null);

const displayValue = computed(() => {
    return selectedCustomer.value
        ? selectedCustomer.value.name
        : searchQuery.value;
});

const showAddNewOption = computed(() => {
    return (
        props.allowCreate &&
        searchQuery.value.length > 2 &&
        !suggestions.value.some((s) =>
            s.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        )
    );
});

// Fetch suggestions from API
const fetchSuggestions = async (query) => {
    if (query.length < 2) {
        suggestions.value = [];
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get("/api/clients/search", {
            params: { search: query },
        });
        suggestions.value = response.data;
    } catch (error) {
        console.error("Error fetching suggestions:", error);
        suggestions.value = [];
    } finally {
        loading.value = false;
    }
};

// Handle input changes
const handleInput = (event) => {
    searchQuery.value = event.target.value;
    selectedCustomer.value = null;
    emit("update:modelValue", "");
    highlightedIndex.value = -1;

    // Debounce the search
    clearTimeout(window.suggestionTimeout);
    window.suggestionTimeout = setTimeout(() => {
        fetchSuggestions(searchQuery.value);
    }, 300);
};

// Handle blur event
const handleBlur = () => {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
};

// Handle keyboard navigation
const handleKeydown = (event) => {
    if (!showSuggestions.value) return;

    switch (event.key) {
        case "ArrowDown":
            event.preventDefault();
            highlightedIndex.value = Math.min(
                highlightedIndex.value + 1,
                suggestions.value.length + (showAddNewOption.value ? 1 : 0) - 1
            );
            break;
        case "ArrowUp":
            event.preventDefault();
            highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1);
            break;
        case "Enter":
            event.preventDefault();
            if (highlightedIndex.value >= 0) {
                if (highlightedIndex.value < suggestions.value.length) {
                    selectSuggestion(suggestions.value[highlightedIndex.value]);
                } else if (showAddNewOption.value) {
                    handleAddNew();
                }
            }
            break;
        case "Escape":
            showSuggestions.value = false;
            highlightedIndex.value = -1;
            break;
    }
};

// Select a suggestion
const selectSuggestion = (suggestion) => {
    selectedCustomer.value = suggestion;
    searchQuery.value = "";
    emit("update:modelValue", suggestion.id);
    emit("select", suggestion);
    showSuggestions.value = false;
    suggestions.value = [];
};

// Handle adding a new customer
const handleAddNew = async () => {
    if (!props.allowCreate) return;

    try {
        const response = await axios.post("/api/clients", {
            first_name: searchQuery.value.split(" ")[0],
            last_name:
                searchQuery.value.split(" ").slice(1).join(" ") || "Customer",
            email: `${searchQuery.value
                .toLowerCase()
                .replace(/\s+/g, ".")}@example.com`,
        });

        const newCustomer = response.data;
        selectedCustomer.value = newCustomer;
        searchQuery.value = "";
        emit("update:modelValue", newCustomer.id);
        emit("select", newCustomer);
        emit("create", newCustomer);
        showSuggestions.value = false;
        suggestions.value = [];
    } catch (error) {
        console.error("Error creating customer:", error);
        alert("Failed to create new customer. Please try again.");
    }
};

// Watch for external value changes
watch(
    () => props.modelValue,
    (newValue) => {
        if (!newValue) {
            selectedCustomer.value = null;
            searchQuery.value = "";
        }
    }
);

// Focus method for external control
const focus = () => {
    inputRef.value?.focus();
};

defineExpose({ focus });
</script>
