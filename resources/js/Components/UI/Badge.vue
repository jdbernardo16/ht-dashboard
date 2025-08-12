<template>
    <div
        :class="
            cn(
                'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
                variantClasses[variant],
                $attrs.class || ''
            )
        "
    >
        <slot />
    </div>
</template>

<script setup>
import { cn } from "@/Utils/utils";

const props = defineProps({
    variant: {
        type: String,
        default: "default",
        validator: (value) =>
            ["default", "secondary", "destructive", "outline"].includes(value),
    },
});

const variantClasses = {
    default: "border-transparent bg-primary text-primary-foreground shadow",
    secondary: "border-transparent bg-secondary text-secondary-foreground",
    destructive:
        "border-transparent bg-destructive text-destructive-foreground shadow",
    outline: "text-foreground",
};
</script>
