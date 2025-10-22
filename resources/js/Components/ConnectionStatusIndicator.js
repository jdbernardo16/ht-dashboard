import { computed, onMounted, onUnmounted, ref } from "vue";
import { useEnhancedNotifications } from "../Composables/useEnhancedNotifications.js";

export default {
    name: "ConnectionStatusIndicator",
    props: {
        position: {
            type: String,
            default: "bottom-right", // 'bottom-right', 'bottom-left', 'top-right', 'top-left'
        },
        showText: {
            type: Boolean,
            default: true,
        },
        showIcon: {
            type: Boolean,
            default: true,
        },
        compact: {
            type: Boolean,
            default: false,
        },
    },
    setup(props) {
        const { connectionStatus, reconnectAttempts } =
            useEnhancedNotifications();
        const isVisible = ref(true);
        const lastHover = ref(null);

        // Position classes
        const positionClasses = computed(() => {
            const positions = {
                "bottom-right": "bottom-4 right-4",
                "bottom-left": "bottom-4 left-4",
                "top-right": "top-4 right-4",
                "top-left": "top-4 left-4",
            };
            return positions[props.position] || positions["bottom-right"];
        });

        // Status color classes
        const statusClasses = computed(() => {
            const classes = {
                connected: "bg-green-500 text-white",
                connecting: "bg-yellow-500 text-white",
                disconnected: "bg-gray-500 text-white",
                error: "bg-red-500 text-white",
            };
            return classes[connectionStatus.value] || classes["disconnected"];
        });

        // Status text
        const statusText = computed(() => {
            const texts = {
                connected: "Connected",
                connecting: "Connecting...",
                disconnected: "Disconnected",
                error: "Connection Error",
            };
            return texts[connectionStatus.value] || "Unknown";
        });

        // Status icon
        const statusIcon = computed(() => {
            const icons = {
                connected: "M5 13l4 4L19 7", // Checkmark
                connecting:
                    "M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15", // Refresh
                disconnected: "M6 18L18 6M6 6l12 12", // X
                error: "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z", // Warning
            };
            return icons[connectionStatus.value] || icons["disconnected"];
        });

        // Show/hide logic
        const shouldShow = computed(() => {
            if (props.compact) {
                return connectionStatus.value !== "connected";
            }
            return true;
        });

        // Auto-hide after 5 seconds when connected
        let hideTimeout = null;

        const scheduleHide = () => {
            if (hideTimeout) {
                clearTimeout(hideTimeout);
            }

            if (connectionStatus.value === "connected" && !props.compact) {
                hideTimeout = setTimeout(() => {
                    isVisible.value = false;
                }, 5000);
            }
        };

        const showIndicator = () => {
            isVisible.value = true;
            if (hideTimeout) {
                clearTimeout(hideTimeout);
            }
        };

        const handleMouseEnter = () => {
            showIndicator();
            lastHover.value = new Date();
        };

        const handleMouseLeave = () => {
            if (connectionStatus.value === "connected" && !props.compact) {
                setTimeout(() => {
                    const now = new Date();
                    if (
                        lastHover.value &&
                        now.getTime() - lastHover.value.getTime() > 1000
                    ) {
                        isVisible.value = false;
                    }
                }, 2000);
            }
        };

        // Watch connection status changes
        const unwatchConnectionStatus = watch(
            () => connectionStatus.value,
            (newStatus, oldStatus) => {
                if (newStatus !== oldStatus) {
                    isVisible.value = true;
                    scheduleHide();
                }
            }
        );

        onMounted(() => {
            isVisible.value = true;
            scheduleHide();
        });

        onUnmounted(() => {
            if (hideTimeout) {
                clearTimeout(hideTimeout);
            }
            unwatchConnectionStatus();
        });

        return {
            connectionStatus,
            reconnectAttempts,
            isVisible,
            shouldShow,
            positionClasses,
            statusClasses,
            statusText,
            statusIcon,
            handleMouseEnter,
            handleMouseLeave,
            showIndicator,
        };
    },
    template: `
        <div 
            v-if="shouldShow && isVisible"
            :class="[
                'connection-status-indicator',
                'fixed z-50 flex items-center gap-2 px-3 py-2 rounded-full text-xs font-medium shadow-lg transition-all duration-300',
                positionClasses,
                statusClasses,
                compact ? 'py-1 px-2' : 'py-2 px-3'
            ]"
            @mouseenter="handleMouseEnter"
            @mouseleave="handleMouseLeave"
        >
            <!-- Status Dot -->
            <div 
                v-if="showIcon"
                class="w-2 h-2 rounded-full bg-current"
                :class="{
                    'animate-pulse': connectionStatus === 'connecting' || connectionStatus === 'error'
                }"
            ></div>
            
            <!-- Status Icon -->
            <svg 
                v-if="showIcon && !compact"
                class="w-3 h-3" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path 
                    stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    :d="statusIcon"
                />
            </svg>
            
            <!-- Status Text -->
            <span v-if="showText && !compact">
                {{ statusText }}
            </span>
            
            <!-- Reconnect attempts indicator -->
            <span 
                v-if="connectionStatus === 'error' && reconnectAttempts > 0"
                class="ml-1 opacity-75"
            >
                ({{ reconnectAttempts }})
            </span>
        </div>
    `,
};
