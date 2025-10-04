<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

const page = usePage();
const user = computed(() => {
    try {
        return page.props.auth?.user || null;
    } catch (e) {
        console.error("Error accessing auth user:", e);
        return null;
    }
});

onMounted(() => {
    console.log("Sidebar mounted - client side hydration");
    console.log("Current route:", route().current());
    console.log("User auth state:", !!user.value);
});

const isClient = typeof window !== "undefined";

if (!isClient) {
    console.log("Sidebar executing in SSR mode");
    try {
        console.log("Current route (SSR):", route?.()?.current());
        console.log("User auth state (SSR):", !!user?.value);
    } catch (e) {
        console.error("SSR context error:", e);
    }
}

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: true,
    },
    isMobile: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["toggle", "close"]);

const navigationItems = computed(() => {
    if (!route) {
        console.error("Route helper not available");
        return [];
    }

    const baseItems = [
        {
            name: "Dashboard",
            href: route?.("dashboard") || "#",
            icon: "M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z",
            active: route().current("dashboard"),
            color: "blue",
        },
        {
            name: "Tasks",
            href: route("tasks.web.index"),
            icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
            active: route().current("tasks.web.*"),
            color: "green",
        },
        {
            name: "Sales",
            href: route("sales.web.index"),
            icon: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
            active: route().current("sales.web.*"),
            color: "yellow",
        },
        {
            name: "Clients",
            href: route("clients.web.index"),
            icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
            active: route().current("clients.web.*"),
            color: "purple",
        },
        {
            name: "Content Posts",
            href: route("content.web.index"),
            icon: "M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z",
            active: route().current("content.web.*"),
            color: "pink",
        },
        {
            name: "Expenses",
            href: route("expenses.web.index"),
            icon: "M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z",
            active: route().current("expenses.web.*"),
            color: "red",
        },
        {
            name: "Goals",
            href: route("goals.web.index"),
            icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
            active: route().current("goals.web.*"),
            color: "indigo",
        },
        {
            name: "Notifications",
            href: route("notifications.index"),
            icon: "M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9",
            active: route().current("notifications.*"),
            color: "orange",
        },
    ];

    const roleBasedItems = [];

    // Add role-specific dashboard links
    if (user.value.role === "admin") {
        roleBasedItems.push({
            name: "Admin Dashboard",
            href: route("admin.dashboard"),
            icon: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z",
            active: route().current("admin.dashboard"),
            color: "emerald",
        });
    }

    if (user.value.role === "manager" || user.value.role === "admin") {
        roleBasedItems.push({
            name: "Manager Dashboard",
            href: route("manager.dashboard"),
            icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
            active: route().current("manager.dashboard"),
            color: "cyan",
        });
    }

    if (user.value.role === "va") {
        roleBasedItems.push({
            name: "VA Dashboard",
            href: route("va.dashboard"),
            icon: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z",
            active: route().current("va.dashboard"),
            color: "teal",
        });
    }

    return [...roleBasedItems, ...baseItems];
});

const bottomNavigationItems = [
    {
        name: "Profile",
        href: route?.("profile.edit") || "#",
        icon: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
        active: route().current("profile.edit"),
        color: "gray",
    },
];

const toggleSidebar = () => {
    emit("toggle");
};

const closeSidebar = () => {
    if (props.isMobile) {
        emit("close");
    }
};

// Color classes for navigation items
const getColorClasses = (color, isActive) => {
    const colors = {
        blue: isActive
            ? "bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/25"
            : "text-gray-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-blue-100 hover:text-blue-700 group",
        green: isActive
            ? "bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg shadow-green-500/25"
            : "text-gray-300 hover:bg-gradient-to-r hover:from-green-50 hover:to-green-100 hover:text-green-700 group",
        yellow: isActive
            ? "bg-gradient-to-r from-yellow-500 to-yellow-600 text-white shadow-lg shadow-yellow-500/25"
            : "text-gray-300 hover:bg-gradient-to-r hover:from-yellow-50 hover:to-yellow-100 hover:text-yellow-700 group",
        purple: isActive
            ? "bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/25"
            : "text-gray-300 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 hover:text-purple-700 group",
        pink: isActive
            ? "bg-gradient-to-r from-pink-500 to-pink-600 text-white shadow-lg shadow-pink-500/25"
            : "text-gray-300 hover:bg-gradient-to-r hover:from-pink-50 hover:to-pink-100 hover:text-pink-700 group",
        red: isActive
            ? "bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg shadow-red-500/25"
            : "text-gray-300 hover:bg-gradient-to-r hover:from-red-50 hover:to-red-100 hover:text-red-700 group",
        indigo: isActive
            ? "bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow-lg shadow-indigo-500/25"
            : "text-gray-300 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-indigo-100 hover:text-indigo-700 group",
        orange: isActive
            ? "bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25"
            : "text-gray-300 hover:bg-gradient-to-r hover:from-orange-50 hover:to-orange-100 hover:text-orange-700 group",
        emerald: isActive
            ? "bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/25"
            : "text-gray-300 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-emerald-100 hover:text-emerald-700 group",
        cyan: isActive
            ? "bg-gradient-to-r from-cyan-500 to-cyan-600 text-white shadow-lg shadow-cyan-500/25"
            : "text-gray-300 hover:bg-gradient-to-r hover:from-cyan-50 hover:to-cyan-100 hover:text-cyan-700 group",
        teal: isActive
            ? "bg-gradient-to-r from-teal-500 to-teal-600 text-white shadow-lg shadow-teal-500/25"
            : "text-gray-300 hover:bg-gradient-to-r hover:from-teal-50 hover:to-teal-100 hover:text-teal-700 group",
        gray: isActive
            ? "bg-gradient-to-r from-gray-500 to-gray-600 text-white shadow-lg shadow-gray-500/25"
            : "text-gray-300 hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-100 hover:text-gray-700 group",
    };

    return colors[color] || colors.blue;
};

const getIconColorClass = (color, isActive) => {
    if (isActive) return "text-white";

    const iconColors = {
        blue: "group-hover:text-blue-600",
        green: "group-hover:text-green-600",
        yellow: "group-hover:text-yellow-600",
        purple: "group-hover:text-purple-600",
        pink: "group-hover:text-pink-600",
        red: "group-hover:text-red-600",
        indigo: "group-hover:text-indigo-600",
        orange: "group-hover:text-orange-600",
        emerald: "group-hover:text-emerald-600",
        cyan: "group-hover:text-cyan-600",
        teal: "group-hover:text-teal-600",
        gray: "group-hover:text-gray-600",
    };

    return iconColors[color] || iconColors.blue;
};
</script>

<template>
    <aside
        v-show="isClient"
        :class="[
            'fixed inset-y-0 left-0 z-50 flex flex-col bg-gradient-to-b from-gray-900 via-gray-800 to-gray-900 text-white transition-all duration-300 ease-in-out shadow-2xl',
            isOpen ? 'w-72' : 'w-20',
            isMobile ? 'translate-x-0' : 'translate-x-0',
            !isOpen && !isMobile
                ? '-translate-x-full lg:translate-x-0 lg:w-20'
                : '',
        ]"
    >
        <!-- Logo Section with Gradient Background -->
        <div class="relative overflow-hidden">
            <div
                class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-600 opacity-90"
            ></div>
            <div class="relative flex h-20 items-center justify-between px-6">
                <Link
                    :href="route('dashboard')"
                    class="flex items-center space-x-4 group"
                    @click="closeSidebar"
                >
                    <div class="relative">
                        <div
                            class="absolute inset-0 bg-white rounded-lg opacity-20 group-hover:opacity-30 transition-opacity duration-200"
                        ></div>
                        <ApplicationLogo
                            v-show="isClient"
                            class="relative h-10 w-auto"
                        />
                    </div>
                    <div v-if="isOpen" class="space-y-1">
                        <h1 class="text-xl font-bold text-white leading-tight">
                            Hidden Treasures
                        </h1>
                        <p class="text-xs text-indigo-100">
                            Business Dashboard
                        </p>
                    </div>
                </Link>
                <button
                    v-if="!isMobile"
                    @click="toggleSidebar"
                    class="rounded-lg p-2 text-white/80 hover:bg-white/20 hover:text-white transition-all duration-200"
                >
                    <svg
                        class="h-5 w-5 transition-transform duration-200"
                        :class="isOpen ? 'rotate-180' : ''"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            v-if="isOpen"
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
        </div>

        <!-- Navigation Items -->
        <nav class="flex-1 space-y-2 px-4 py-6 overflow-y-auto">
            <div
                v-for="item in navigationItems"
                :key="item.name"
                class="group/nav-item"
            >
                <Link
                    :href="item.href"
                    :class="[
                        'flex items-center space-x-4 rounded-xl px-4 py-3 text-sm font-medium transition-all duration-200 transform hover:scale-[1.02]',
                        getColorClasses(item.color, item.active),
                        !isOpen && 'justify-center px-3',
                    ]"
                    @click="closeSidebar"
                >
                    <div class="relative flex-shrink-0">
                        <div
                            v-if="item.active"
                            class="absolute inset-0 bg-white/20 rounded-lg blur-sm"
                        ></div>
                        <svg
                            class="relative h-6 w-6 transition-all duration-200"
                            :class="getIconColorClass(item.color, item.active)"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="item.icon"
                            />
                        </svg>
                    </div>
                    <div v-if="isOpen" class="flex-1 min-w-0">
                        <p class="truncate font-semibold">
                            {{ item.name }}
                        </p>
                        <p v-if="item.active" class="text-xs opacity-80 mt-0.5">
                            Currently viewing
                        </p>
                    </div>
                    <div v-if="isOpen && item.active" class="flex-shrink-0">
                        <div
                            class="w-2 h-2 bg-white rounded-full animate-pulse"
                        ></div>
                    </div>
                </Link>
            </div>
        </nav>

        <!-- User Profile Section with Card Design -->
        <div class="relative">
            <div
                class="absolute inset-0 bg-gradient-to-t from-gray-900 to-gray-800"
            ></div>
            <div class="relative border-t border-gray-700 p-6">
                <!-- User Card -->
                <div
                    class="bg-gradient-to-r from-gray-700/50 to-gray-600/50 rounded-xl p-4 backdrop-blur-sm border border-gray-600/30"
                >
                    <div class="flex items-center space-x-4">
                        <div class="relative flex-shrink-0">
                            <div
                                class="h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center shadow-lg"
                            >
                                <span class="text-lg font-bold text-white">
                                    {{ user?.name?.charAt(0) || "U" }}
                                </span>
                            </div>
                            <div
                                class="absolute -bottom-1 -right-1 h-4 w-4 bg-green-400 rounded-full border-2 border-gray-800"
                            ></div>
                        </div>
                        <div v-if="isOpen" class="flex-1 min-w-0">
                            <p
                                class="text-sm font-semibold text-white truncate"
                            >
                                {{ user?.name || "User" }}
                            </p>
                            <p class="text-xs text-gray-300 truncate">
                                {{ user?.email || "user@example.com" }}
                            </p>
                            <div class="mt-2">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-500/20 text-indigo-300 border border-indigo-500/30"
                                >
                                    {{
                                        user?.role?.charAt(0).toUpperCase() +
                                            user?.role?.slice(1) || "User"
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Navigation -->
                <div class="mt-4 space-y-2">
                    <Link
                        v-for="item in bottomNavigationItems"
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            'flex items-center space-x-3 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200',
                            getColorClasses(item.color, item.active),
                            !isOpen && 'justify-center px-3',
                        ]"
                        @click="closeSidebar"
                    >
                        <svg
                            class="h-5 w-5 flex-shrink-0 transition-colors duration-200"
                            :class="getIconColorClass(item.color, item.active)"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="item.icon"
                            />
                        </svg>
                        <span v-if="isOpen" class="truncate font-medium">
                            {{ item.name }}
                        </span>
                    </Link>
                </div>
            </div>
        </div>
    </aside>
</template>
