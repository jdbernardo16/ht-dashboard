import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useEnhancedNotifications } from "../Composables/useEnhancedNotifications.js";
import { createApiHeaders } from "../Utils/utils.js";

interface Notification {
    id: string;
    title: string;
    message: string;
    type: string;
    is_unread: boolean;
    created_at: string;
    read_at?: string;
    data?: any;
}

interface Pagination {
    current_page: number;
    from: number;
    to: number;
    total: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

export default {
    name: "EnhancedNotificationsIndex",
    setup() {
        const filter = ref("all");
        const notifications = ref<Notification[]>([]);
        const unreadCount = ref(0);
        const totalCount = ref(0);
        const loading = ref(false);
        const error = ref<string | null>(null);
        const markingAllAsRead = ref(false);
        const markingRead = ref(new Set<string>());
        const deleting = ref(new Set<string>());
        const pagination = ref<Pagination | null>(null);
        const currentPage = ref(1);
        const selectedNotifications = ref(new Set<string>());
        const showBulkActions = ref(false);

        // Use enhanced notifications composable
        const {
            connectionStatus,
            showToast,
            toastMessage,
            toastType,
            notificationTypes,
            isHighPriority,
            getNotificationIcon,
            getNotificationColor,
            connectWithStatus,
            disconnectWithStatus,
            markAsRead: markAsReadRealtime,
            markAllAsRead: markAllAsReadRealtime,
            showNotificationToast,
            hideNotificationToast,
        } = useEnhancedNotifications();

        // Computed properties
        const hasSelectedNotifications = computed(
            () => selectedNotifications.value.size > 0
        );
        const allNotificationsSelected = computed(
            () =>
                notifications.value.length > 0 &&
                selectedNotifications.value.size === notifications.value.length
        );
        const hasUnreadNotifications = computed(() =>
            notifications.value.some((n) => n.is_unread)
        );
        const filteredNotificationsList = computed(() => {
            if (filter.value === "all") {
                return notifications.value;
            }
            if (filter.value === "unread") {
                return notifications.value.filter((n) => n.is_unread);
            }
            if (filter.value === "read") {
                return notifications.value.filter((n) => !n.is_unread);
            }
            return notifications.value.filter((n) => n.type === filter.value);
        });

        // Fetch notifications
        const fetchNotifications = async () => {
            try {
                loading.value = true;
                error.value = null;

                let url = `/api/notifications?page=${currentPage.value}&per_page=20`;
                if (filter.value !== "all") {
                    if (filter.value === "unread") {
                        url += "&read=false";
                    } else if (filter.value === "read") {
                        url += "&read=true";
                    } else {
                        url += `&type=${filter.value}`;
                    }
                }

                const response = await fetch(url, {
                    headers: createApiHeaders(),
                });

                if (!response.ok) {
                    throw new Error("Failed to fetch notifications");
                }

                const data = await response.json();
                notifications.value =
                    data.notifications.data || data.notifications;
                unreadCount.value = data.unread_count;
                totalCount.value =
                    data.notifications.total || data.notifications.length;

                // Extract pagination info
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

                // Clear selection when data changes
                selectedNotifications.value.clear();
                showBulkActions.value = false;
            } catch (err) {
                error.value = (err as Error).message;
                console.error("Error fetching notifications:", err);
            } finally {
                loading.value = false;
            }
        };

        // Fetch unread count
        const fetchUnreadCount = async () => {
            try {
                const response = await fetch(
                    "/api/notifications/unread-count",
                    {
                        headers: createApiHeaders(),
                    }
                );

                if (response.ok) {
                    const data = await response.json();
                    unreadCount.value = data.unread_count;
                }
            } catch (err) {
                console.error("Error fetching unread count:", err);
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
                    const index = notifications.value.findIndex(
                        (n) => n.id === notification.id
                    );
                    if (index !== -1) {
                        notifications.value[index].is_unread = false;
                        notifications.value[index].read_at =
                            new Date().toISOString();
                    }
                    unreadCount.value = Math.max(0, unreadCount.value - 1);
                    markAsReadRealtime(notification.id);
                }
            } catch (err) {
                console.error("Error marking notification as read:", err);
            } finally {
                markingRead.value.delete(notification.id);
            }
        };

        // Mark notification as unread
        const markAsUnread = async (notification: Notification) => {
            try {
                markingRead.value.add(notification.id);

                const response = await fetch(
                    `/api/notifications/${notification.id}`,
                    {
                        method: "PUT",
                        headers: createApiHeaders({
                            "Content-Type": "application/json",
                        }),
                        body: JSON.stringify({ read: false }),
                    }
                );

                if (response.ok) {
                    const index = notifications.value.findIndex(
                        (n) => n.id === notification.id
                    );
                    if (index !== -1) {
                        notifications.value[index].is_unread = true;
                        delete notifications.value[index].read_at;
                    }
                    unreadCount.value++;
                }
            } catch (err) {
                console.error("Error marking notification as unread:", err);
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
                    notifications.value.forEach((notification) => {
                        notification.is_unread = false;
                        notification.read_at = new Date().toISOString();
                    });
                    unreadCount.value = 0;
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
                    totalCount.value = Math.max(0, totalCount.value - 1);

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

        // Bulk actions
        const selectAllNotifications = () => {
            if (allNotificationsSelected.value) {
                selectedNotifications.value.clear();
            } else {
                filteredNotificationsList.value.forEach((n) =>
                    selectedNotifications.value.add(n.id)
                );
            }
        };

        const markSelectedAsRead = async () => {
            const promises = Array.from(selectedNotifications.value).map(
                (id) => {
                    const notification = notifications.value.find(
                        (n) => n.id === id
                    );
                    if (notification && notification.is_unread) {
                        return markAsRead(notification);
                    }
                    return Promise.resolve();
                }
            );

            try {
                await Promise.all(promises);
                selectedNotifications.value.clear();
                showBulkActions.value = false;
                showNotificationToast(
                    "Selected notifications marked as read",
                    "success"
                );
            } catch (err) {
                console.error(
                    "Error marking selected notifications as read:",
                    err
                );
            }
        };

        const deleteSelectedNotifications = async () => {
            if (
                !confirm(
                    "Are you sure you want to delete the selected notifications?"
                )
            ) {
                return;
            }

            const promises = Array.from(selectedNotifications.value).map(
                (id) => {
                    const notification = notifications.value.find(
                        (n) => n.id === id
                    );
                    if (notification) {
                        return deleteNotification(notification);
                    }
                    return Promise.resolve();
                }
            );

            try {
                await Promise.all(promises);
                selectedNotifications.value.clear();
                showBulkActions.value = false;
                showNotificationToast(
                    "Selected notifications deleted",
                    "success"
                );
            } catch (err) {
                console.error("Error deleting selected notifications:", err);
            }
        };

        // Set filter
        const setFilter = (newFilter: string) => {
            filter.value = newFilter;
            currentPage.value = 1;
            fetchNotifications();
        };

        // Pagination
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

        // Handle selection changes
        watch(selectedNotifications, (newSelection) => {
            showBulkActions.value = newSelection.size > 0;
        });

        // Listen for real-time updates
        const handleNewNotification = (event: CustomEvent) => {
            const notification = event.detail as Notification;
            notifications.value.unshift(notification);

            if (notification.is_unread) {
                unreadCount.value++;
            }

            totalCount.value++;
        };

        onMounted(() => {
            fetchNotifications();
            fetchUnreadCount();

            // Listen for real-time notifications
            window.addEventListener(
                "notification-received",
                handleNewNotification as EventListener
            );
        });

        onUnmounted(() => {
            window.removeEventListener(
                "notification-received",
                handleNewNotification as EventListener
            );
            hideNotificationToast();
        });

        return {
            // State
            filter,
            notifications,
            unreadCount,
            totalCount,
            loading,
            error,
            markingAllAsRead,
            markingRead,
            deleting,
            pagination,
            currentPage,
            selectedNotifications,
            showBulkActions,

            // Enhanced notifications
            connectionStatus,
            showToast,
            toastMessage,
            toastType,
            notificationTypes,

            // Computed
            hasSelectedNotifications,
            allNotificationsSelected,
            hasUnreadNotifications,
            filteredNotificationsList,

            // Methods
            fetchNotifications,
            markAsRead,
            markAsUnread,
            markAllAsRead,
            deleteNotification,
            selectAllNotifications,
            markSelectedAsRead,
            deleteSelectedNotifications,
            setFilter,
            nextPage,
            prevPage,
            formatTimeAgo,
            isHighPriority,
            getNotificationIcon,
            getNotificationColor,
            hideNotificationToast,
        };
    },
};
