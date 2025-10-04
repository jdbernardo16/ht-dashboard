<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
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

// Close dropdowns when clicking outside
const handleGlobalClick = (event) => {
    // Handle mobile sidebar
    handleClickOutside(event);

    // Handle notification dropdown
    const notificationDropdown = document.getElementById(
        "notification-dropdown"
    );
    const notificationButton = document.getElementById("notification-button");

    if (notificationDropdown && notificationButton) {
        if (
            !notificationButton.contains(event.target) &&
            !notificationDropdown.contains(event.target)
        ) {
            notificationDropdown.classList.add("hidden");
        }
    }
};

// Handle user dropdown
const toggleUserDropdown = () => {
    const dropdown = document.getElementById("user-dropdown");
    if (dropdown) {
        dropdown.classList.toggle("hidden");
    }
};

onMounted(() => {
    document.addEventListener("click", handleGlobalClick);
});

onUnmounted(() => {
    document.removeEventListener("click", handleGlobalClick);
});
</script>

<template>
    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50"
    >
        <!-- Mobile Sidebar Overlay -->
        <div
            v-if="isMobileSidebarOpen"
            class="fixed inset-0 z-40 bg-gray-900 bg-opacity-50 backdrop-blur-sm lg:hidden transition-opacity duration-300"
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
                isSidebarOpen ? 'lg:ml-72' : 'lg:ml-20',
            ]"
        >
            <!-- Top Navigation Bar -->
            <nav
                class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30"
            >
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex items-center">
                            <!-- Mobile menu button -->
                            <button
                                @click="toggleMobileSidebar"
                                class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition-all duration-200 lg:hidden"
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
                                class="hidden rounded-lg p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 transition-all duration-200 lg:block"
                            >
                                <svg
                                    class="h-5 w-5 transition-transform duration-200"
                                    :class="isSidebarOpen ? 'rotate-180' : ''"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        v-if="isSidebarOpen"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
                                    />
                                    <path
                                        v-else
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 5l7 7-7 7M5 5l7 7-7 7"
                                    />
                                </svg>
                            </button>
                        </div>

                        <div class="flex items-center space-x-4">
                            <!-- Notification Bell -->
                            <div class="relative" id="notification-button">
                                <NotificationBell />
                            </div>

                            <!-- User Dropdown -->
                            <div class="relative">
                                <button
                                    @click="toggleUserDropdown"
                                    class="flex items-center space-x-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200"
                                >
                                   
                                    <div class="w-8 h-8">
                                        <img :src="$page.props.auth.user.avatar_url" alt="User profile image" class="h-full w-full object-cover rounded-full "></img>
                                    </div>
                                    <span class="hidden md:block text-gray-700">
                                        {{ $page.props.auth.user.name }}
                                    </span>
                                    <svg
                                        class="hidden md:block h-4 w-4 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 9l-7 7-7-7"
                                        />
                                    </svg>
                                </button>

                                <!-- User Dropdown Menu -->
                                <div
                                    id="user-dropdown"
                                    class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50"
                                >
                                    <div
                                        class="px-4 py-3 border-b border-gray-200"
                                    >
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ $page.props.auth.user.name }}
                                        </p>
                                        <p
                                            class="text-xs text-gray-500 truncate"
                                        >
                                            {{ $page.props.auth.user.email }}
                                        </p>
                                    </div>
                                    <div class="py-1">
                                        <Link
                                            :href="route('profile.edit')"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200"
                                        >
                                            <div
                                                class="flex items-center space-x-2"
                                            >
                                                <svg
                                                    class="h-4 w-4 text-gray-400"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                                    />
                                                </svg>
                                                <span>Profile</span>
                                            </div>
                                        </Link>
                                        <Link
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200"
                                        >
                                            <div
                                                class="flex items-center space-x-2"
                                            >
                                                <svg
                                                    class="h-4 w-4 text-gray-400"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                                    />
                                                </svg>
                                                <span>Log Out</span>
                                            </div>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-gradient-to-r from-white via-gray-50 to-white shadow-sm border-b border-gray-200"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Content wrapper with subtle background -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <slot />
                    </div>
                </div>
            </main>

            <!-- Footer (optional) -->
            <footer class="bg-white border-t border-gray-200 mt-auto">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4">
                    <div
                        class="flex justify-between items-center text-sm text-gray-500"
                    >
                        <p>
                            &copy; {{ new Date().getFullYear() }} Hidden
                            Treasures Dashboard
                        </p>
                        <p class="hidden sm:block">
                            Powered by Laravel & Vue.js
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>
