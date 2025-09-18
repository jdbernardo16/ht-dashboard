<script setup>
import { ref, computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import Sidebar from "@/Components/Sidebar.vue";
import NotificationBell from "@/Components/UI/NotificationBell.vue";

const page = usePage();
const showingNavigationDropdown = ref(false);
const isSidebarOpen = ref(true);
const isMobileSidebarOpen = ref(false);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const toggleMobileSidebar = () => {
    isMobileSidebarOpen.value = !isMobileSidebarOpen.value;
};

const closeMobileSidebar = () => {
    isMobileSidebarOpen.value = false;
};

// Close mobile sidebar when clicking outside
const handleClickOutside = (event) => {
    if (
        isMobileSidebarOpen.value &&
        !event.target.closest(".sidebar-container")
    ) {
        closeMobileSidebar();
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Mobile Sidebar Overlay -->
        <div
            v-if="isMobileSidebarOpen"
            class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
            @click="closeMobileSidebar"
        />

        <!-- Sidebar -->
        <Sidebar
            :is-open="isSidebarOpen"
            :is-mobile="false"
            @toggle="toggleSidebar"
            class="hidden lg:block"
        />

        <!-- Mobile Sidebar -->
        <Sidebar
            :is-open="isMobileSidebarOpen"
            :is-mobile="true"
            @close="closeMobileSidebar"
            class="lg:hidden"
        />

        <!-- Main Content -->
        <div
            :class="[
                'min-h-screen transition-all duration-300 ease-in-out',
                isSidebarOpen ? 'lg:ml-64' : 'lg:ml-20',
            ]"
        >
            <!-- Top Navigation Bar -->
            <nav class="border-b border-gray-200 bg-white shadow-sm">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex items-center">
                            <!-- Mobile menu button -->
                            <button
                                @click="toggleMobileSidebar"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 lg:hidden"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                </svg>
                            </button>

                            <!-- Desktop toggle button -->
                            <button
                                @click="toggleSidebar"
                                class="hidden rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 lg:block"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                </svg>
                            </button>
                        </div>

                        <div class="flex items-center">
                            <!-- Notification Bell -->
                            <div class="mr-4">
                                <NotificationBell />
                            </div>

                            <!-- User Dropdown -->
                            <div class="relative ml-3">
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm text-gray-700">
                                        {{ $page.props.auth.user.name }}
                                    </span>
                                    <Link
                                        :href="route('profile.edit')"
                                        class="text-sm text-gray-500 hover:text-gray-700"
                                    >
                                        Profile
                                    </Link>
                                    <Link
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="text-sm text-gray-500 hover:text-gray-700"
                                    >
                                        Log Out
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <div class="py-6">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
