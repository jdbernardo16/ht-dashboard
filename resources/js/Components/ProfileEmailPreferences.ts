import { computed, onMounted, ref } from "vue";
import { useEmailPreferences } from "../Composables/useEmailPreferences.js";

interface EmailPreferences {
    email_notifications_enabled: boolean;
    task_notifications: boolean;
    sales_notifications: boolean;
    expense_notifications: boolean;
    goal_notifications: boolean;
    content_notifications: boolean;
}

export default {
    name: "ProfileEmailPreferences",
    setup() {
        const {
            preferences,
            loading,
            error,
            success,
            fetchEmailPreferences,
            updateEmailPreferences,
            toggleAllEmailNotifications,
            updatePreference,
            clearSuccess,
            clearError,
        } = useEmailPreferences();

        const expanded = ref(false);
        const saving = ref(false);

        // Computed properties
        const hasAnyEnabled = computed(() => {
            return Object.values(preferences.value).some(
                (value) => value === true
            );
        });

        const enabledCount = computed(() => {
            const { email_notifications_enabled, ...individual } =
                preferences.value;
            return Object.values(individual).filter((value) => value === true)
                .length;
        });

        const totalCount = computed(() => {
            const { email_notifications_enabled, ...individual } =
                preferences.value;
            return Object.keys(individual).length;
        });

        const statusText = computed(() => {
            if (!preferences.value.email_notifications_enabled) {
                return "Disabled";
            }
            if (enabledCount.value === totalCount.value) {
                return "All enabled";
            }
            return `${enabledCount.value} of ${totalCount.value} enabled`;
        });

        const statusColor = computed(() => {
            if (!preferences.value.email_notifications_enabled) {
                return "disabled";
            }
            if (enabledCount.value === totalCount.value) {
                return "enabled";
            }
            return "partial";
        });

        // Fetch preferences on mount
        onMounted(() => {
            fetchEmailPreferences();
        });

        // Quick toggle for master switch
        const handleQuickToggle = async () => {
            try {
                saving.value = true;
                clearError();
                clearSuccess();

                await toggleAllEmailNotifications();

                // Auto-hide success message after 3 seconds
                setTimeout(() => {
                    clearSuccess();
                }, 3000);
            } catch (err) {
                console.error("Error toggling notifications:", err);
            } finally {
                saving.value = false;
            }
        };

        // Toggle expansion
        const toggleExpanded = () => {
            expanded.value = !expanded.value;
        };

        return {
            // State
            preferences,
            loading,
            error,
            success,
            expanded,
            saving,

            // Computed
            hasAnyEnabled,
            enabledCount,
            totalCount,
            statusText,
            statusColor,

            // Methods
            handleQuickToggle,
            toggleExpanded,
            clearSuccess,
            clearError,
        };
    },
};
