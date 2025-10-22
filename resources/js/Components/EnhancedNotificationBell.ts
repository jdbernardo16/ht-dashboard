import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useEnhancedNotifications } from "../Composables/useEnhancedNotifications.js";
import { createApiHeaders } from "../Utils/utils.js";

interface User {
    id: number;
    name: string;
    email: string;
    avatar_url?: string;
}

interface Notification {
    id: string;
    title: string;
    message: string;
    type: string;
    is_unread: boolean;
    created_at: string;
    data?: any;
}

export default {
    name: "EnhancedNotificationBell",
    props: {
        user: {
            type: Object as () => User,
            required: true,
        },
    },
    setup(props: { user: User }) {
        const showNotifications = ref(false);
        const loading = ref(false);
        const error = ref<string | null>(null);
        const markingAllAsRead = ref(false);
        const markingRead = ref(new Set<string>());
        const deleting = ref(new Set<string>());
        const notificationFilter = ref("all");

        // Use enhanced notifications composable
        const {
            notifications,
            unreadCount,
            connectionStatus,
            showToast,
            toastMessage,
            toastType,
            notificationTypes,
            filteredNotifications,
            isHighPriority,
            getNotificationIcon,
            getNotificationColor,
            connectWithStatus,
            disconnectWithStatus,
            markAsRead: markAsReadRealtime,
            markAllAsRead: markAllAsReadRealtime,
            setNotifications,
            showNotificationToast,
            hideNotificationToast,
            setFilter,
        } = useEnhancedNotifications();

        // Computed properties
        const hasUnreadNotifications = computed(() => unreadCount.value > 0);
        const hasHighPriorityNotifications = computed(() =>
            notifications.value.some((n) => isHighPriority(n))
        );
        const filteredNotificationList = computed(() => {
            let filtered = notifications.value;

            if (notificationFilter.value !== "all") {
                filtered = filtered.filter(
                    (n) => n.type === notificationFilter.value
                );
            }

            return filtered.slice(0, 5); // Show only 5 in dropdown
        });

        // Fetch notifications
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
                notifications.value =
                    data.notifications.data || data.notifications;
            } catch (err) {
                error.value = (err as Error).message;
                console.error("Error fetching notifications:", err);
            } finally {
                loading.value = false;
            }
        };

        // Mark notification as read
        const markAsRead = async (notification: Notification) => {
            try {
                markingRead.value.add(notification.id);

                const response = await fetch(
                    `/api/notifications/${notification.id}`,
                    {
                        method: "PUT",
                        headers: createApiHeaders({
                            "Content-Type": "application/json",
                        }),
                        body: JSON.stringify({ read: true }),
                    }
                );

                if (response.ok) {
                    markAsReadRealtime(notification.id);
                }
            } catch (err) {
                console.error("Error marking notification as read:", err);
            } finally {
                markingRead.value.delete(notification.id);
            }
        };

        // Mark all notifications as read
        const markAllAsRead = async () => {
            try {
                markingAllAsRead.value = true;

                const response = await fetch(
                    "/api/notifications/mark-all-read",
                    {
                        method: "POST",
                        headers: createApiHeaders(),
                    }
                );

                if (response.ok) {
                    markAllAsReadRealtime();
                    showNotificationToast(
                        "All notifications marked as read",
                        "success"
                    );
                }
            } catch (err) {
                console.error("Error marking all notifications as read:", err);
            } finally {
                markingAllAsRead.value = false;
            }
        };

        // Delete notification
        const deleteNotification = async (notification: Notification) => {
            try {
                deleting.value.add(notification.id);

                const response = await fetch(
                    `/api/notifications/${notification.id}`,
                    {
                        method: "DELETE",
                        headers: createApiHeaders(),
                    }
                );

                if (response.ok) {
                    notifications.value = notifications.value.filter(
                        (n) => n.id !== notification.id
                    );

                    if (notification.is_unread) {
                        unreadCount.value = Math.max(0, unreadCount.value - 1);
                    }

                    showNotificationToast("Notification deleted", "info");
                }
            } catch (err) {
                console.error("Error deleting notification:", err);
            } finally {
                deleting.value.delete(notification.id);
            }
        };

        // Toggle notifications dropdown
        const toggleNotifications = () => {
            showNotifications.value = !showNotifications.value;
            if (showNotifications.value) {
                fetchNotifications();
            }
        };

        // Set notification filter
        const setNotificationFilter = (filter: string) => {
            notificationFilter.value = filter;
            setFilter(filter);
        };

        // Format time ago
        const formatTimeAgo = (dateString: string): string => {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor(
                (now.getTime() - date.getTime()) / 1000
            );

            if (diffInSeconds < 60) return "Just now";
            if (diffInSeconds < 3600)
                return `${Math.floor(diffInSeconds / 60)}m ago`;
            if (diffInSeconds < 86400)
                return `${Math.floor(diffInSeconds / 3600)}h ago`;
            if (diffInSeconds < 604800)
                return `${Math.floor(diffInSeconds / 86400)}d ago`;

            return date.toLocaleDateString();
        };

        // Get connection status text
        const getConnectionStatusText = (): string => {
            switch (connectionStatus.value) {
                case "connected":
                    return "Connected";
                case "connecting":
                    return "Connecting...";
                case "disconnected":
                    return "Disconnected";
                case "error":
                    return "Connection Error";
                default:
                    return "Unknown";
            }
        };

        // Initialize real-time notifications
        onMounted(async () => {
            if (props.user?.id) {
                connectWithStatus(props.user.id);
            }

            fetchNotifications();
        });

        // Watch for user changes
        watch(
            () => props.user?.id,
            (newUserId, oldUserId) => {
                if (oldUserId) {
                    disconnectWithStatus(oldUserId);
                }
                if (newUserId) {
                    connectWithStatus(newUserId);
                }
            }
        );

        // Clean up
        onUnmounted(() => {
            if (props.user?.id) {
                disconnectWithStatus(props.user.id);
            }
            hideNotificationToast();
        });

        // Close dropdown when clicking outside
        const handleClickOutside = (event: Event) => {
            const target = event.target as Element;
            if (
                showNotifications.value &&
                !target.closest(".notification-bell")
            ) {
                showNotifications.value = false;
            }
        };

        onMounted(() => {
            document.addEventListener("click", handleClickOutside);
        });

        onUnmounted(() => {
            document.removeEventListener("click", handleClickOutside);
        });

        return {
            // State
            showNotifications,
            loading,
            error,
            markingAllAsRead,
            markingRead,
            deleting,
            notificationFilter,

            // Computed
            hasUnreadNotifications,
            hasHighPriorityNotifications,
            filteredNotificationList,

            // Enhanced notifications
            notifications,
            unreadCount,
            connectionStatus,
            showToast,
            toastMessage,
            toastType,
            notificationTypes,

            // Methods
            fetchNotifications,
            markAsRead,
            markAllAsRead,
            deleteNotification,
            toggleNotifications,
            setNotificationFilter,
            formatTimeAgo,
            getConnectionStatusText,
            isHighPriority,
            getNotificationIcon,
            getNotificationColor,
            hideNotificationToast,
        };
    },
};
