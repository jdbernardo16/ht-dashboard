<template>
    <div class="time-period-dropdown">
        <select
            v-model="selectedPeriod"
            @change="handlePeriodChange"
            class="time-period-select"
        >
            <option
                v-for="option in periodOptions"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>

        <div v-if="showCustomRange" class="custom-range-picker mt-2">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs text-gray-600 mb-1"
                        >Start Date</label
                    >
                    <input
                        type="date"
                        v-model="customStartDate"
                        class="w-full text-sm border-gray-300 rounded-md"
                    />
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1"
                        >End Date</label
                    >
                    <input
                        type="date"
                        v-model="customEndDate"
                        class="w-full text-sm border-gray-300 rounded-md"
                    />
                </div>
            </div>
            <button
                @click="applyCustomRange"
                class="mt-2 w-full bg-blue-500 text-white text-xs py-1 px-2 rounded-md hover:bg-blue-600"
            >
                Apply Range
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const emit = defineEmits(["period-change"]);

const selectedPeriod = ref("daily");
const customStartDate = ref("");
const customEndDate = ref("");

const periodOptions = [
    { value: "daily", label: "Daily" },
    { value: "weekly", label: "Weekly" },
    { value: "monthly", label: "Monthly" },
    { value: "quarterly", label: "Quarterly" },
    { value: "annually", label: "Annually" },
    { value: "custom", label: "Custom Range" },
];

const showCustomRange = computed(() => selectedPeriod.value === "custom");

const handlePeriodChange = () => {
    if (selectedPeriod.value !== "custom") {
        emit("period-change", {
            period: selectedPeriod.value,
            startDate: null,
            endDate: null,
        });
    }
};

const applyCustomRange = () => {
    if (customStartDate.value && customEndDate.value) {
        emit("period-change", {
            period: "custom",
            startDate: customStartDate.value,
            endDate: customEndDate.value,
        });
    }
};

// Set default custom dates to current month
const setDefaultCustomDates = () => {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    customStartDate.value = firstDay.toISOString().split("T")[0];
    customEndDate.value = lastDay.toISOString().split("T")[0];
};

// Initialize default custom dates
setDefaultCustomDates();
</script>

<style scoped>
.time-period-select {
    @apply text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500;
}

.custom-range-picker {
    @apply p-3 bg-gray-50 border border-gray-200 rounded-md;
}
</style>
