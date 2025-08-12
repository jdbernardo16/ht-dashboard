<template>
    <div
        :class="
            cn(
                'relative flex h-10 w-10 shrink-0 overflow-hidden rounded-full',
                $attrs.class || ''
            )
        "
    >
        <img
            v-if="src"
            :src="src"
            :alt="alt"
            class="aspect-square h-full w-full object-cover"
        />
        <span
            v-else
            class="flex h-full w-full items-center justify-center rounded-full bg-primary-100 text-primary-600 font-medium"
        >
            {{ initials }}
        </span>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { cn } from "@/Utils/utils";

const props = defineProps({
    src: {
        type: String,
        default: null,
    },
    alt: {
        type: String,
        default: "Avatar",
    },
    fallback: {
        type: String,
        default: "",
    },
});

const initials = computed(() => {
    if (!props.fallback) return "?";
    return props.fallback
        .split(" ")
        .map((part) => part[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
});
</script>
