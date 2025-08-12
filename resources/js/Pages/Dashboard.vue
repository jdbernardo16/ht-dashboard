<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import Card from "@/Components/UI/Card.vue";
import CardHeader from "@/Components/UI/CardHeader.vue";
import CardTitle from "@/Components/UI/CardTitle.vue";
import CardDescription from "@/Components/UI/CardDescription.vue";
import CardContent from "@/Components/UI/CardContent.vue";
import PieChart from "@/Components/Charts/PieChart.vue";
import ProgressBar from "@/Components/Charts/ProgressBar.vue";
import { ref } from "vue";

// Sample data for charts
const activityData = ref({
    labels: ["Tasks", "Sales", "Content", "Meetings"],
    datasets: [
        {
            data: [35, 25, 20, 20],
            backgroundColor: ["#8884d8", "#82ca9d", "#ffc658", "#ff8042"],
            borderWidth: 0,
        },
    ],
});

const salesProgress = ref(75);
const goalsProgress = ref(60);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-3xl font-bold tracking-tight text-gray-900">
                Dashboard
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Welcome back! Here's your performance overview.
            </p>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Dashboard Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Activity Distribution Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Activity Distribution</CardTitle>
                            <CardDescription
                                >Your daily activities
                                breakdown</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div class="h-64">
                                <PieChart :data="activityData" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Sales Metrics Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Sales Metrics</CardTitle>
                            <CardDescription
                                >Your sales performance this
                                month</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <ProgressBar
                                    label="Sales Target"
                                    :value="salesProgress"
                                />
                                <ProgressBar
                                    label="Goal Achievement"
                                    :value="goalsProgress"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Tasks Overview Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Tasks Overview</CardTitle>
                            <CardDescription
                                >Your current task status</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium"
                                        >Total Tasks</span
                                    >
                                    <span
                                        class="text-2xl font-bold text-primary-600"
                                        >24</span
                                    >
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium"
                                        >Completed</span
                                    >
                                    <span
                                        class="text-2xl font-bold text-green-600"
                                        >18</span
                                    >
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium"
                                        >Pending</span
                                    >
                                    <span
                                        class="text-2xl font-bold text-orange-600"
                                        >6</span
                                    >
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Goals Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Goals Progress</CardTitle>
                            <CardDescription
                                >Track your monthly goals</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div class="text-center">
                                    <div
                                        class="text-3xl font-bold text-primary-600"
                                    >
                                        $12,450
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Revenue Goal
                                    </div>
                                </div>
                                <ProgressBar
                                    label="Progress"
                                    :value="goalsProgress"
                                />
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
