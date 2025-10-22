import { onUnmounted, ref } from "vue";

export function useNotifications() {
    const notifications = ref([]);
    const unreadCount = ref(0);
    let channel = null;

    const connectToNotifications = (userId) => {
        if (!userId) return;

        console.log("Connecting to notifications for user:", userId);
        console.log("Echo configuration:", {
            broadcaster: window.Echo.options.broadcaster,
            key: window.Echo.options.key,
            wsHost: window.Echo.options.wsHost,
            wsPort: window.Echo.options.wsPort,
        });

        // Disconnect existing channel if any
        if (channel) {
            window.Echo.leave(`notifications.${userId}`);
        }

        // Connect to private notification channel
        channel = window.Echo.private(`notifications.${userId}`)
            .subscribed(() => {
                console.log(
                    "Successfully subscribed to notifications channel for user:",
                    userId
                );
            })
            .error((error) => {
                console.error(
                    "Failed to subscribe to notifications channel:",
                    error
                );
            })
            .listen(".notification.sent", (e) => {
                console.log("Received real-time notification:", e);

                // Add new notification to the list
                notifications.value.unshift(e);

                // Update unread count
                if (!e.read_at) {
                    unreadCount.value++;
                }

                // Show browser notification if permission granted
                if (
                    "Notification" in window &&
                    Notification.permission === "granted"
                ) {
                    new Notification(e.title, {
                        body: e.message,
                        icon: "/images/ht-logo.png",
                        tag: e.id,
                    });
                }

                // Trigger custom event for other components to listen to
                window.dispatchEvent(
                    new CustomEvent("notification-received", {
                        detail: e,
                    })
                );
            });
    };

    const disconnectFromNotifications = (userId) => {
        if (channel && userId) {
            window.Echo.leave(`notifications.${userId}`);
            channel = null;
        }
    };

    const requestNotificationPermission = async () => {
        if ("Notification" in window) {
            const permission = await Notification.requestPermission();
            return permission === "granted";
        }
        return false;
    };

    const markAsRead = (notificationId) => {
        const notification = notifications.value.find(
            (n) => n.id === notificationId
        );
        if (notification && !notification.read_at) {
            notification.read_at = new Date().toISOString();
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        }
    };

    const markAllAsRead = () => {
        notifications.value.forEach((notification) => {
            if (!notification.read_at) {
                notification.read_at = new Date().toISOString();
            }
        });
        unreadCount.value = 0;
    };

    const setNotifications = (notificationList) => {
        notifications.value = notificationList;
        unreadCount.value = notificationList.filter((n) => !n.read_at).length;
    };

    onUnmounted(() => {
        if (channel) {
            window.Echo.leave(channel.name);
        }
    });

    return {
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
