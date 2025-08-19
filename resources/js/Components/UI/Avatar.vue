<template>
    <div
        :class="
            cn(
                'relative flex h-10 w-10 shrink-0 overflow-hidden rounded-full',
                sizes[size],
                $attrs.class
            )
        "
    >
        <img
            v-if="src"
            :src="src"
            :alt="alt"
            :class="cn('aspect-square h-full w-full object-cover')"
        />
        <div
            v-else
            :class="
                cn(
                    'flex h-full w-full items-center justify-center bg-muted',
                    fallbackClasses
                )
            "
        >
            <span
                v-if="fallback"
                :class="cn('text-sm font-medium', textSizes[size])"
            >
                {{ fallback }}
            </span>
            <slot v-else />
        </div>
    </div>
</template>

<script setup lang="ts">
import { cn } from "@/Utils/cn";

interface Props {
    src?: string;
    alt?: string;
    fallback?: string;
    size?: "sm" | "md" | "lg" | "xl";
}

withDefaults(defineProps<Props>(), {
    size: "md",
});

const sizes = {
    sm: "h-8 w-8",
    md: "h-10 w-10",
    lg: "h-12 w-12",
    xl: "h-16 w-16",
};

const fallbackClasses = "bg-primary text-primary-foreground";
const textSizes = {
    sm: "text-xs",
    md: "text-sm",
    lg: "text-base",
    xl: "text-lg",
};
</script>
