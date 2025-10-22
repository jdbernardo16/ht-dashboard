import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { useEnhancedNotifications } from "../Composables/useEnhancedNotifications.js";

export default {
    name: "ToastNotification",
    props: {
        position: {
            type: String,
            default: "top-right", // 'top-right', 'top-left', 'bottom-right', 'bottom-left'
        },
        autoHide: {
            type: Boolean,
            default: true,
        },
        duration: {
            type: Number,
            default: 5000, // milliseconds
        },
        showCloseButton: {
            type: Boolean,
            default: true,
        },
        maxToasts: {
            type: Number,
            default: 3,
        },
    },
    setup(props) {
        const { showToast, toastMessage, toastType, hideNotificationToast } =
            useEnhancedNotifications();

        const toasts = ref([]);
        const toastIdCounter = ref(0);

        // Position classes
        const positionClasses = computed(() => {
            const positions = {
                "top-right": "top-4 right-4",
                "top-left": "top-4 left-4",
                "bottom-right": "bottom-4 right-4",
                "bottom-left": "bottom-4 left-4",
            };
            return positions[props.position] || positions["top-right"];
        });

        // Toast type classes
        const getToastClasses = (type) => {
            const classes = {
                info: "bg-blue-500 text-white",
                success: "bg-green-500 text-white",
                warning: "bg-yellow-500 text-white",
                error: "bg-red-500 text-white",
            };
            return classes[type] || classes["info"];
        };

        // Toast icons
        const getToastIcon = (type) => {
            const icons = {
                info: "M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z", // Information
                success: "M5 13l4 4L19 7", // Checkmark
                warning:
                    "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z", // Warning
                error: "M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z", // X
            };
            return icons[type] || icons["info"];
        };

        // Add new toast
        const addToast = (message, type = "info", options = {}) => {
            const id = ++toastIdCounter.value;
            const toast = {
                id,
                message,
                type,
                timestamp: Date.now(),
                ...options,
            };

            toasts.value.unshift(toast);

            // Limit number of toasts
            if (toasts.value.length > props.maxToasts) {
                toasts.value = toasts.value.slice(0, props.maxToasts);
            }

            // Auto-hide if enabled
            if (props.autoHide) {
                setTimeout(() => {
                    removeToast(id);
                }, options.duration || props.duration);
            }

            return id;
        };

        // Remove toast
        const removeToast = (id) => {
            const index = toasts.value.findIndex((toast) => toast.id === id);
            if (index !== -1) {
                toasts.value.splice(index, 1);
            }
        };

        // Clear all toasts
        const clearAllToasts = () => {
            toasts.value = [];
        };

        // Watch for global toast changes
        watch(
            () => ({
                show: showToast.value,
                message: toastMessage.value,
                type: toastType.value,
            }),
            ({ show, message, type }) => {
                if (show && message) {
                    addToast(message, type);
                    hideNotificationToast();
                }
            }
        );

        // Handle keyboard shortcuts
        const handleKeydown = (event) => {
            // Escape key clears all toasts
            if (event.key === "Escape") {
                clearAllToasts();
            }
        };

        onMounted(() => {
            document.addEventListener("keydown", handleKeydown);
        });

        onUnmounted(() => {
            document.removeEventListener("keydown", handleKeydown);
        });

        return {
            toasts,
            positionClasses,
            getToastClasses,
            getToastIcon,
            addToast,
            removeToast,
            clearAllToasts,
        };
    },
    template: `
        <div 
            :class="[
                'toast-container',
                'fixed z-50 flex flex-col gap-2 pointer-events-none',
                positionClasses
            ]"
        >
            <transition-group
                name="toast"
                tag="div"
                class="flex flex-col gap-2"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        'toast-notification',
                        'flex items-start gap-3 p-4 rounded-lg shadow-lg min-w-[300px] max-w-md pointer-events-auto',
                        'transform transition-all duration-300 ease-out',
                        getToastClasses(toast.type)
                    ]"
                >
                    <!-- Toast Icon -->
                    <div class="flex-shrink-0">
                        <svg 
                            class="w-5 h-5" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path 
                                stroke-linecap="round" 
                                stroke-linejoin="round" 
                                stroke-width="2" 
                                :d="getToastIcon(toast.type)"
                            />
                        </svg>
                    </div>
                    
                    <!-- Toast Content -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium">
                            {{ toast.message }}
                        </p>
                        <p 
                            v-if="toast.description"
                            class="text-xs opacity-90 mt-1"
                        >
                            {{ toast.description }}
                        </p>
                    </div>
                    
                    <!-- Close Button -->
                    <button
                        v-if="showCloseButton"
                        @click="removeToast(toast.id)"
                        class="flex-shrink-0 p-1 rounded-md hover:bg-black hover:bg-opacity-10 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path 
                                stroke-linecap="round" 
                                stroke-linejoin="round" 
                                stroke-width="2" 
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </transition-group>
        </div>
    `,
    style: `
        .toast-enter-active,
        .toast-leave-active {
            transition: all 0.3s ease;
        }
        
        .toast-enter-from {
            opacity: 0;
            transform: translateX(100%);
        }
        
        .toast-leave-to {
            opacity: 0;
            transform: translateX(100%);
        }
        
        .toast-move {
            transition: transform 0.3s ease;
        }
    `,
};
