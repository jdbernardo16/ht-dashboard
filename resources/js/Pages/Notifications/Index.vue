<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold text-gray-900">
                    Notifications
                </h2>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-600">
                        {{ unreadCount }} unread of {{ totalCount }} total
                    </div>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        :disabled="markingAllAsRead"
                    >
                        <svg
                            v-if="markingAllAsRead"
                            class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-700"
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
                        {{
                            markingAllAsRead
                                ? "Marking all as read..."
                                : "Mark all as read"
                        }}
                    </button>
                </div>
            </div>
        </template>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <!-- Filters -->
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex items-center space-x-4">
                    <div class="text-sm font-medium text-gray-700">
                        Filter by:
                    </div>
                    <button
                        @click="setFilter('all')"
                        :class="[
                            'px-3 py-1 text-sm font-medium rounded-md',
                            filter === 'all'
                                ? 'bg-blue-100 text-blue-700'
                                : 'text-gray-600 hover:text-gray-900',
                        ]"
                    >
                        All
                    </button>
                    <button
                        @click="setFilter('unread')"
                        :class="[
                            'px-3 py-1 text-sm font-medium rounded-md',
                            filter === 'unread'
                                ? 'bg-blue-100 text-blue-700'
                                : 'text-gray-600 hover:text-gray-900',
                        ]"
                    >
                        Unread
                    </button>
                    <button
                        @click="setFilter('read')"
                        :class="[
                            'px-3 py-1 text-sm font-medium rounded-md',
                            filter === 'read'
                                ? 'bg-blue-100 text-blue-700'
                                : 'text-gray-600 hover:text-gray-900',
                        ]"
                    >
                        Read
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="p-8 text-center">
                <svg
                    class="animate-spin h-8 w-8 text-blue-600 mx-auto"
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
                <p class="mt-2 text-sm text-gray-600">
                    Loading notifications...
                </p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="p-8 text-center">
                <svg
                    class="h-12 w-12 text-red-500 mx-auto"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                    ></path>
                </svg>
                <p class="mt-2 text-sm text-red-600">{{ error }}</p>
                <button
                    @click="fetchNotifications"
                    class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Try again
                </button>
            </div>

            <!-- Empty State -->
            <div v-else-if="notifications.length === 0" class="p-8 text-center">
                <svg
                    class="h-16 w-16 text-gray-300 mx-auto"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                    ></path>
                </svg>
                <p class="mt-4 text-sm text-gray-600">No notifications found</p>
                <p class="text-xs text-gray-500">
                    You'll see notifications here when they arrive
                </p>
            </div>

            <!-- Notifications List -->
            <div v-else class="divide-y divide-gray-200">
                <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="px-6 py-4 hover:bg-gray-50 transition-colors"
                    :class="{ 'bg-blue-50': notification.is_unread }"
                >
                    <div class="flex items-start space-x-4">
                        <!-- Notification Icon -->
                        <div
                            class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                            :class="getNotificationIconClass(notification.type)"
                        >
                            <svg
                                class="w-5 h-5 text-white"
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
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ notification.title }}
                                </p>
                                <div class="flex items-center space-x-2">
                                    <span
                                        v-if="notification.is_unread"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                    >
                                        Unread
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{
                                            formatTimeAgo(
                                                notification.created_at
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ notification.message }}
                            </p>

                            <!-- Additional Data -->
                            <!-- <div
                                v-if="
                                    notification.data &&
                                    Object.keys(notification.data).length > 0
                                "
                                class="mt-2 p-2 bg-gray-50 rounded-md"
                            >
                                <p class="text-xs text-gray-500">
                                    Additional information:
                                </p>
                                <pre class="text-xs text-gray-700 mt-1">{{
                                    JSON.stringify(notification.data, null, 2)
                                }}</pre>
                            </div> -->
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col items-center space-y-2">
                            <button
                                v-if="notification.is_unread"
                                @click="markAsRead(notification)"
                                class="text-blue-600 hover:text-blue-800 text-sm"
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
                                class="text-gray-400 hover:text-red-600 text-sm"
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

            <!-- Pagination -->
            <div
                v-if="notifications.length > 0 && pagination"
                class="px-6 py-4 border-t border-gray-200"
            >
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ pagination.from }} to {{ pagination.to }} of
                        {{ pagination.total }} results
                    </div>
                    <div class="flex space-x-2">
                        <button
                            @click="prevPage"
                            :disabled="!pagination.prev_page_url"
                            class="px-3 py-1 text-sm border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>
                        <button
                            @click="nextPage"
                            :disabled="!pagination.next_page_url"
                            class="px-3 py-1 text-sm border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { createApiHeaders } from "../../Utils/utils.js";

const filter = ref("all");
const notifications = ref([]);
const unreadCount = ref(0);
const totalCount = ref(0);
const loading = ref(false);
const error = ref(null);
const markingAllAsRead = ref(false);
const markingRead = ref(new Set());
const deleting = ref(new Set());
const pagination = ref(null);
const currentPage = ref(1);

const fetchNotifications = async () => {
    try {
        loading.value = true;
        error.value = null;

        let url = `/api/notifications?page=${currentPage.value}&per_page=20`;
        if (filter.value !== "all") {
            url += `&read=${filter.value === "read"}`;
        }

        const response = await fetch(url, {
            headers: createApiHeaders(),
        });

        if (!response.ok) {
            throw new Error("Failed to fetch notifications");
        }

        const data = await response.json();
        notifications.value = data.notifications.data || data.notifications;
        unreadCount.value = data.unread_count;
        totalCount.value =
            data.notifications.total || data.notifications.length;

        // Extract pagination info if available
        if (data.notifications.meta) {
            pagination.value = {
                current_page: data.notifications.meta.current_page,
                from: data.notifications.meta.from,
                to: data.notifications.meta.to,
                total: data.notifications.meta.total,
                last_page: data.notifications.meta.last_page,
                prev_page_url: data.notifications.meta.prev_page_url,
                next_page_url: data.notifications.meta.next_page_url,
            };
        } else if (data.notifications.current_page) {
            pagination.value = data.notifications;
        } else {
            pagination.value = null;
        }
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
            totalCount.value = Math.max(0, totalCount.value - 1);

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

const setFilter = (newFilter) => {
    filter.value = newFilter;
    currentPage.value = 1;
    fetchNotifications();
};

const nextPage = () => {
    if (pagination.value && pagination.value.next_page_url) {
        currentPage.value++;
        fetchNotifications();
    }
};

const prevPage = () => {
    if (pagination.value && pagination.value.prev_page_url) {
        currentPage.value--;
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

// Fetch notifications on mount and when filter changes
onMounted(() => {
    fetchNotifications();
});

watch(filter, () => {
    fetchNotifications();
});

watch(currentPage, () => {
    fetchNotifications();
});
</script>
