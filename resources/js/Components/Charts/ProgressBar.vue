<template>
    <div class="w-full">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-gray-700">{{ label }}</span>
            <span class="text-sm text-gray-500">{{ value }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div
                class="bg-primary-600 h-2 rounded-full transition-all duration-300 ease-out"
                :style="{ width: `${value}%` }"
            ></div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: Number,
        required: true,
        validator: (value) => value >= 0 && value <= 100,
    },
});
</script>
