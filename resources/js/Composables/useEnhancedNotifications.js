import { onMounted, onUnmounted, ref } from "vue";
import { useNotifications } from "./useNotifications.js";

export function useEnhancedNotifications() {
    const {
        notifications,
        unreadCount,
        connectToNotifications,
        disconnectFromNotifications,
        requestNotificationPermission,
        markAsRead,
        markAllAsRead,
        setNotifications,
    } = useNotifications();

    // Connection status
    const connectionStatus = ref("disconnected"); // 'connected', 'disconnected', 'connecting', 'error'
    const lastConnectionAttempt = ref(null);
    const reconnectAttempts = ref(0);
    const maxReconnectAttempts = 5;
    const reconnectDelay = 3000; // 3 seconds

    // Toast notification state
    const showToast = ref(false);
    const toastMessage = ref("");
    const toastType = ref("info"); // 'info', 'success', 'warning', 'error'
    const toastTimeout = ref(null);

    // Notification filtering
    const activeFilter = ref("all");
    const notificationTypes = ref([
        {
            value: "all",
            label: "All Notifications",
            icon: "M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9",
        },
        {
            value: "system_alert",
            label: "System Alerts",
            icon: "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z",
        },
        {
            value: "task_update",
            label: "Task Updates",
            icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
        },
        {
            value: "expense_approved",
            label: "Expense Updates",
            icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
        },
        {
            value: "goal_achieved",
            label: "Goal Achievements",
            icon: "M13 10V3L4 14h7v7l9-11h-7z",
        },
        {
            value: "sale_completed",
            label: "Sales Updates",
            icon: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
        },
        {
            value: "content_published",
            label: "Content Updates",
            icon: "M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z",
        },
        {
            value: "daily_summary",
            label: "Daily Summaries",
            icon: "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
        },
        {
            value: "reminder",
            label: "Reminders",
            icon: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
        },
    ]);

    // High priority notification types
    const highPriorityTypes = ["system_alert", "security_alert"];

    /**
     * Enhanced connection to notifications with status tracking
     */
    const connectWithStatus = (userId) => {
        if (!userId) return;

        connectionStatus.value = "connecting";
        lastConnectionAttempt.value = new Date();

        try {
            connectToNotifications(userId);
            connectionStatus.value = "connected";
            reconnectAttempts.value = 0;

            // Show success toast
            showNotificationToast(
                "Connected to real-time notifications",
                "success"
            );
        } catch (error) {
            connectionStatus.value = "error";
            console.error("Failed to connect to notifications:", error);

            // Show error toast
            showNotificationToast(
                "Failed to connect to notifications",
                "error"
            );

            // Attempt to reconnect
            scheduleReconnect(userId);
        }
    };

    /**
     * Disconnect with status update
     */
    const disconnectWithStatus = (userId) => {
        if (userId) {
            disconnectFromNotifications(userId);
            connectionStatus.value = "disconnected";
        }
    };

    /**
     * Schedule reconnection attempt
     */
    const scheduleReconnect = (userId) => {
        if (reconnectAttempts.value >= maxReconnectAttempts) {
            showNotificationToast(
                "Unable to connect to notifications after multiple attempts",
                "error"
            );
            return;
        }

        reconnectAttempts.value++;

        setTimeout(() => {
            if (connectionStatus.value !== "connected") {
                connectWithStatus(userId);
            }
        }, reconnectDelay * reconnectAttempts.value);
    };

    /**
     * Show toast notification
     */
    const showNotificationToast = (message, type = "info") => {
        // Clear existing timeout
        if (toastTimeout.value) {
            clearTimeout(toastTimeout.value);
        }

        toastMessage.value = message;
        toastType.value = type;
        showToast.value = true;

        // Auto-hide after 5 seconds
        toastTimeout.value = setTimeout(() => {
            hideNotificationToast();
        }, 5000);
    };

    /**
     * Hide toast notification
     */
    const hideNotificationToast = () => {
        showToast.value = false;
        if (toastTimeout.value) {
            clearTimeout(toastTimeout.value);
            toastTimeout.value = null;
        }
    };

    /**
     * Check if notification is high priority
     */
    const isHighPriority = (notification) => {
        return (
            highPriorityTypes.includes(notification.type) ||
            (notification.data && notification.data.priority === "high")
        );
    };

    /**
     * Get notification icon based on type
     */
    const getNotificationIcon = (type) => {
        const typeConfig = notificationTypes.value.find(
            (t) => t.value === type
        );
        return typeConfig ? typeConfig.icon : notificationTypes.value[0].icon;
    };

    /**
     * Get notification color based on type and priority
     */
    const getNotificationColor = (notification) => {
        if (isHighPriority(notification)) {
            return "bg-red-500";
        }

        const colorMap = {
            system_alert: "bg-red-500",
            task_update: "bg-blue-500",
            expense_approved: "bg-green-500",
            goal_achieved: "bg-purple-500",
            sale_completed: "bg-yellow-500",
            content_published: "bg-indigo-500",
            daily_summary: "bg-gray-500",
            reminder: "bg-orange-500",
        };

        return colorMap[notification.type] || "bg-gray-500";
    };

    /**
     * Filter notifications by type
     */
    const filteredNotifications = () => {
        if (activeFilter.value === "all") {
            return notifications.value;
        }

        return notifications.value.filter((n) => n.type === activeFilter.value);
    };

    /**
     * Set notification filter
     */
    const setFilter = (filter) => {
        activeFilter.value = filter;
    };

    /**
     * Play notification sound (optional)
     */
    const playNotificationSound = () => {
        try {
            const audio = new Audio("/sounds/notification.mp3");
            audio.volume = 0.3;
            audio.play().catch((e) => {
                // Ignore errors if sound file doesn't exist or can't be played
                console.log("Could not play notification sound:", e);
            });
        } catch (error) {
            // Ignore sound errors
        }
    };

    /**
     * Enhanced notification handler
     */
    const handleNewNotification = (notification) => {
        // Show toast for new notification
        showNotificationToast(
            `${notification.title}: ${notification.message}`,
            isHighPriority(notification) ? "warning" : "info"
        );

        // Play sound for new notification
        playNotificationSound();
    };

    // Listen for new notifications
    onMounted(() => {
        window.addEventListener("notification-received", (event) => {
            handleNewNotification(event.detail);
        });
    });

    onUnmounted(() => {
        window.removeEventListener(
            "notification-received",
            handleNewNotification
        );
        hideNotificationToast();
    });

    return {
        // Enhanced connection status
        connectionStatus,
        lastConnectionAttempt,
        reconnectAttempts,

        // Toast notifications
        showToast,
        toastMessage,
        toastType,
        showNotificationToast,
        hideNotificationToast,

        // Enhanced filtering
        activeFilter,
        notificationTypes,
        filteredNotifications,
        setFilter,

        // Enhanced notification helpers
        isHighPriority,
        getNotificationIcon,
        getNotificationColor,

        // Connection methods
        connectWithStatus,
        disconnectWithStatus,

        // Original methods
        notifications,
        unreadCount,
        connectToNotifications,
        disconnectFromNotifications,
        requestNotificationPermission,
        markAsRead,
        markAllAsRead,
        setNotifications,
    };
}
