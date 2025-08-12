<template>
    <div class="w-full h-full">
        <canvas ref="chartCanvas"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from "vue";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
});

const chartCanvas = ref(null);
let chart = null;

const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: "bottom",
            labels: {
                padding: 20,
                usePointStyle: true,
                font: {
                    family: "Inter",
                    size: 12,
                },
            },
        },
    },
};

const createChart = () => {
    if (!chartCanvas.value) return;

    const ctx = chartCanvas.value.getContext("2d");
    chart = new Chart(ctx, {
        type: "pie",
        data: props.data,
        options: { ...defaultOptions, ...props.options },
    });
};

const destroyChart = () => {
    if (chart) {
        chart.destroy();
        chart = null;
    }
};

onMounted(() => {
    createChart();
});

onUnmounted(() => {
    destroyChart();
});

watch(
    () => props.data,
    () => {
        destroyChart();
        createChart();
    },
    { deep: true }
);
</script>
