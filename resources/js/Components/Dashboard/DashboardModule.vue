<template>
    <Card
        :class="
            cn('hover:shadow-lg transition-shadow duration-200', $attrs.class)
        "
    >
        <CardHeader v-if="$slots.header || title || showTimePeriod">
            <div class="flex items-center justify-between">
                <div>
                    <CardTitle v-if="title">{{ title }}</CardTitle>
                    <CardDescription v-if="description">{{
                        description
                    }}</CardDescription>
                </div>
                <div v-if="showTimePeriod" class="time-period-container">
                    <TimePeriodDropdown
                        @period-change="$emit('period-change', $event)"
                    />
                </div>
            </div>
            <slot name="header" />
        </CardHeader>

        <CardContent :class="cn('pt-6', contentClass)">
            <slot />
        </CardContent>

        <CardFooter v-if="$slots.footer">
            <slot name="footer" />
        </CardFooter>
    </Card>
</template>

<script setup lang="ts">
import { cn } from "@/Utils/cn";
import Card from "@/Components/UI/Card.vue";
import CardHeader from "@/Components/UI/CardHeader.vue";
import CardTitle from "@/Components/UI/CardTitle.vue";
import CardDescription from "@/Components/UI/CardDescription.vue";
import CardContent from "@/Components/UI/CardContent.vue";
import CardFooter from "@/Components/UI/CardFooter.vue";
import TimePeriodDropdown from "./TimePeriodDropdown.vue";

interface Props {
    title?: string;
    description?: string;
    contentClass?: string;
    showTimePeriod?: boolean;
}

withDefaults(defineProps<Props>(), {
    contentClass: "",
    showTimePeriod: false,
});

defineEmits(["period-change"]);
</script>

<style scoped>
.time-period-container {
    @apply ml-4;
}
</style>
