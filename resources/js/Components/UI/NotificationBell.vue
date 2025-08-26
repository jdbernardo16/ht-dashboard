<template>
    <div class="relative">
        <!-- Notification Bell Button -->
        <button
            @click="toggleNotifications"
            class="relative p-2 text-gray-600 hover:text-gray-900 transition-colors focus:outline-none"
            :class="{ 'text-blue-600': showNotifications }"
        >
            <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
            </svg>

            <!-- Unread Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 flex items-center justify-center w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full"
            >
                {{ unreadCount > 99 ? "99+" : unreadCount }}
            </span>
        </button>

        <!-- Notifications Dropdown -->
        <div
            v-if="showNotifications"
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50 max-h-96 overflow-y-auto"
        >
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Notifications
                    </h3>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                        :disabled="markingAllAsRead"
                    >
                        {{
                            markingAllAsRead ? "Marking..." : "Mark all as read"
                        }}
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="p-4 text-center text-gray-500">
                <svg
                    class="animate-spin h-5 w-5 text-blue-600 mx-auto"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                </svg>
                <p class="mt-2 text-sm">Loading notifications...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="p-4 text-center text-red-600">
                <p class="text-sm">{{ error }}</p>
                <button
                    @click="fetchNotifications"
                    class="mt-2 text-blue-600 hover:text-blue-800 text-sm"
                >
                    Try again
                </button>
            </div>

            <!-- Empty State -->
            <div
                v-else-if="notifications.length === 0"
                class="p-4 text-center text-gray-500"
            >
                <svg
                    class="w-12 h-12 mx-auto text-gray-300"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                    />
                </svg>
                <p class="mt-2 text-sm">No notifications yet</p>
            </div>

            <!-- Notifications List -->
            <div v-else class="divide-y divide-gray-100">
                <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="p-4 hover:bg-gray-50 transition-colors"
                    :class="{ 'bg-blue-50': notification.is_unread }"
                >
                    <div class="flex items-start space-x-3">
                        <!-- Notification Icon -->
                        <div
                            class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                            :class="getNotificationIconClass(notification.type)"
                        >
                            <svg
                                class="w-4 h-4 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    :d="
                                        getNotificationIconPath(
                                            notification.type
                                        )
                                    "
                                />
                            </svg>
                        </div>

                        <!-- Notification Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                {{ notification.title }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ notification.message }}
                            </p>
                            <p class="text-xs text-gray-500 mt-2">
                                {{ formatTimeAgo(notification.created_at) }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col items-center space-y-1">
                            <button
                                v-if="notification.is_unread"
                                @click="markAsRead(notification)"
                                class="text-blue-600 hover:text-blue-800 text-xs"
                                :disabled="markingRead.has(notification.id)"
                            >
                                {{
                                    markingRead.has(notification.id)
                                        ? "..."
                                        : "Mark read"
                                }}
                            </button>
                            <button
                                @click="deleteNotification(notification)"
                                class="text-gray-400 hover:text-red-600 text-xs"
                                :disabled="deleting.has(notification.id)"
                            >
                                {{
                                    deleting.has(notification.id)
                                        ? "..."
                                        : "Delete"
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View All Link -->
            <div
                v-if="notifications.length > 0"
                class="p-4 border-t border-gray-200"
            >
                <router-link
                    to="/notifications"
                    class="block text-center text-blue-600 hover:text-blue-800 text-sm font-medium"
                    @click="showNotifications = false"
                >
                    View all notifications
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import { createApiHeaders } from "../../Utils/utils.js";

const showNotifications = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const loading = ref(false);
const error = ref(null);
const markingAllAsRead = ref(false);
const markingRead = ref(new Set());
const deleting = ref(new Set());

// Polling interval
let pollingInterval = null;

const fetchNotifications = async () => {
    try {
        loading.value = true;
        error.value = null;

        const response = await fetch("/api/notifications?per_page=5", {
            headers: createApiHeaders(),
        });

        if (!response.ok) {
            throw new Error("Failed to fetch notifications");
        }

        const data = await response.json();
        notifications.value = data.notifications.data || data.notifications;
        unreadCount.value = data.unread_count;
    } catch (err) {
        error.value = err.message;
        console.error("Error fetching notifications:", err);
    } finally {
        loading.value = false;
    }
};

const fetchUnreadCount = async () => {
    try {
        const response = await fetch("/api/notifications/unread-count", {
            headers: createApiHeaders(),
        });

        if (response.ok) {
            const data = await response.json();
            unreadCount.value = data.unread_count;
        }
    } catch (err) {
        console.error("Error fetching unread count:", err);
    }
};

const markAsRead = async (notification) => {
    try {
        markingRead.value.add(notification.id);

        const response = await fetch(`/api/notifications/${notification.id}`, {
            method: "PUT",
            headers: createApiHeaders({
                "Content-Type": "application/json",
            }),
            body: JSON.stringify({ read: true }),
        });

        if (response.ok) {
            // Update local state
            const index = notifications.value.findIndex(
                (n) => n.id === notification.id
            );
            if (index !== -1) {
                notifications.value[index].is_unread = false;
                notifications.value[index].read_at = new Date().toISOString();
            }
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        }
    } catch (err) {
        console.error("Error marking notification as read:", err);
    } finally {
        markingRead.value.delete(notification.id);
    }
};

const markAllAsRead = async () => {
    try {
        markingAllAsRead.value = true;

        const response = await fetch("/api/notifications/mark-all-read", {
            method: "POST",
            headers: createApiHeaders(),
        });

        if (response.ok) {
            // Update all notifications to read
            notifications.value.forEach((notification) => {
                notification.is_unread = false;
                notification.read_at = new Date().toISOString();
            });
            unreadCount.value = 0;
        }
    } catch (err) {
        console.error("Error marking all notifications as read:", err);
    } finally {
        markingAllAsRead.value = false;
    }
};

const deleteNotification = async (notification) => {
    try {
        deleting.value.add(notification.id);

        const response = await fetch(`/api/notifications/${notification.id}`, {
            method: "DELETE",
            headers: createApiHeaders(),
        });

        if (response.ok) {
            // Remove from local list
            notifications.value = notifications.value.filter(
                (n) => n.id !== notification.id
            );

            // Update unread count if notification was unread
            if (notification.is_unread) {
                unreadCount.value = Math.max(0, unreadCount.value - 1);
            }
        }
    } catch (err) {
        console.error("Error deleting notification:", err);
    } finally {
        deleting.value.delete(notification.id);
    }
};

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
    if (showNotifications.value) {
        fetchNotifications();
    }
};

const getNotificationIconClass = (type) => {
    const typeColors = {
        system_alert: "bg-red-500",
        task_update: "bg-blue-500",
        expense_approved: "bg-green-500",
        goal_achieved: "bg-purple-500",
        sale_completed: "bg-yellow-500",
        content_published: "bg-indigo-500",
        daily_summary: "bg-gray-500",
        reminder: "bg-orange-500",
        default: "bg-gray-500",
    };
    return typeColors[type] || typeColors.default;
};

const getNotificationIconPath = (type) => {
    const iconPaths = {
        system_alert:
            "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z",
        task_update:
            "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
        expense_approved: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
        goal_achieved: "M13 10V3L4 14h7v7l9-11h-7z",
        sale_completed:
            "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
        default:
            "M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9",
    };
    return iconPaths[type] || iconPaths.default;
};

const formatTimeAgo = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return "Just now";
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400)
        return `${Math.floor(diffInSeconds / 3600)}h ago`;
    if (diffInSeconds < 604800)
        return `${Math.floor(diffInSeconds / 86400)}d ago`;

    return date.toLocaleDateString();
};

// Start polling for unread count
onMounted(() => {
    fetchUnreadCount();
    pollingInterval = setInterval(fetchUnreadCount, 30000); // Poll every 30 seconds
});

// Clean up polling
onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (showNotifications.value && !event.target.closest(".relative")) {
        showNotifications.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>
