<template>
    <div
        :class="
            cn(
                'rounded-xl border bg-card text-card-foreground shadow-soft',
                $attrs.class || ''
            )
        "
    >
        <slot />
    </div>
</template>

<script setup>
import { cn } from "@/Utils/utils";
</script>
