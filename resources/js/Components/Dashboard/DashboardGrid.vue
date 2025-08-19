<script setup>
// DashboardGrid component for 2x3 grid layout (6 modules)
</script>

<template>
    <div
        style="
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            max-width: 1405px;
            margin: 0 auto;
        "
    >
        <slot />
    </div>
</template>

<style scoped>
.dashboard-grid {
    @apply grid gap-6;
    @apply grid-cols-1; /* Mobile: 1 column */
    @apply md:grid-cols-2; /* Tablet: 2 columns */
    @apply lg:grid-cols-3; /* Desktop: 3 columns for 2x3 layout */

    /* Figma design constraints */
    max-width: 1405px;
    margin: 0 auto;
}

/* Responsive adjustments */
@media (min-width: 1405px) {
    .dashboard-grid {
        width: 1405px;
    }
}
</style>
