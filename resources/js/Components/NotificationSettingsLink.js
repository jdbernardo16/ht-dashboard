import { computed, ref } from "vue";

export default {
    name: "NotificationSettingsLink",
    props: {
        variant: {
            type: String,
            default: "default", // 'default', 'compact', 'dropdown'
        },
        showIcon: {
            type: Boolean,
            default: true,
        },
        showText: {
            type: Boolean,
            default: true,
        },
    },
    setup(props) {
        const isOpen = ref(false);

        // Computed classes based on variant
        const linkClasses = computed(() => {
            const baseClasses =
                "flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200";

            switch (props.variant) {
                case "compact":
                    return `${baseClasses} text-gray-600 hover:text-gray-900 hover:bg-gray-100`;
                case "dropdown":
                    return `${baseClasses} text-gray-700 hover:bg-gray-100 hover:text-gray-900 w-full text-left`;
                default:
                    return `${baseClasses} text-blue-600 hover:bg-blue-50 hover:text-blue-700`;
            }
        });

        // Toggle dropdown if variant is dropdown
        const toggleDropdown = () => {
            if (props.variant === "dropdown") {
                isOpen.value = !isOpen.value;
            }
        };

        // Close dropdown
        const closeDropdown = () => {
            isOpen.value = false;
        };

        return {
            isOpen,
            linkClasses,
            toggleDropdown,
            closeDropdown,
        };
    },
    template: `
        <div class="notification-settings-link" :class="{ 'relative': variant === 'dropdown' }">
            <a 
                href="/email-preferences"
                :class="linkClasses"
                @click="toggleDropdown"
            >
                <svg 
                    v-if="showIcon" 
                    class="w-4 h-4" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                    />
                    <path 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                </svg>
                <span v-if="showText">Notification Settings</span>
            </a>
            
            <!-- Dropdown menu for compact variant -->
            <div 
                v-if="variant === 'dropdown' && isOpen" 
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
            >
                <a 
                    href="/email-preferences" 
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    @click="closeDropdown"
                >
                    Email Preferences
                </a>
                <a 
                    href="/notifications" 
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    @click="closeDropdown"
                >
                    View All Notifications
                </a>
                <hr class="my-1">
                <button 
                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    @click="closeDropdown"
                >
                    Mark All as Read
                </button>
            </div>
        </div>
    `,
};
