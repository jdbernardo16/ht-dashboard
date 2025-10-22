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
    name: "EmailPreferences",
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

        const saving = ref(false);
        const savingPreference = ref<string | null>(null);

        // Computed properties
        const hasAnyEnabled = computed(() => {
            return Object.values(preferences.value).some(
                (value) => value === true
            );
        });

        const allPreferencesEnabled = computed(() => {
            const prefs = preferences.value;
            return (
                prefs.task_notifications &&
                prefs.sales_notifications &&
                prefs.expense_notifications &&
                prefs.goal_notifications &&
                prefs.content_notifications
            );
        });

        const individualPreferences = computed(() => {
            const { email_notifications_enabled, ...individual } =
                preferences.value;
            return individual;
        });

        // Fetch preferences on mount
        onMounted(() => {
            fetchEmailPreferences();
        });

        // Update a single preference
        const handlePreferenceChange = async (
            key: keyof EmailPreferences,
            value: boolean
        ) => {
            try {
                savingPreference.value = key;
                clearError();
                clearSuccess();

                await updatePreference(key, value);

                // Auto-hide success message after 3 seconds
                setTimeout(() => {
                    clearSuccess();
                }, 3000);
            } catch (err) {
                console.error("Error updating preference:", err);
            } finally {
                savingPreference.value = null;
            }
        };

        // Toggle all email notifications
        const handleToggleAll = async () => {
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
                console.error("Error toggling all notifications:", err);
            } finally {
                saving.value = false;
            }
        };

        // Save all preferences
        const handleSaveAll = async () => {
            try {
                saving.value = true;
                clearError();
                clearSuccess();

                await updateEmailPreferences(preferences.value);

                // Auto-hide success message after 3 seconds
                setTimeout(() => {
                    clearSuccess();
                }, 3000);
            } catch (err) {
                console.error("Error saving preferences:", err);
            } finally {
                saving.value = false;
            }
        };

        // Reset to defaults
        const handleResetToDefaults = async () => {
            const defaultPreferences: EmailPreferences = {
                email_notifications_enabled: true,
                task_notifications: true,
                sales_notifications: true,
                expense_notifications: true,
                goal_notifications: true,
                content_notifications: true,
            };

            try {
                saving.value = true;
                clearError();
                clearSuccess();

                await updateEmailPreferences(defaultPreferences);

                // Auto-hide success message after 3 seconds
                setTimeout(() => {
                    clearSuccess();
                }, 3000);
            } catch (err) {
                console.error("Error resetting preferences:", err);
            } finally {
                saving.value = false;
            }
        };

        // Get preference details
        const getPreferenceDetails = (key: string) => {
            const details = {
                task_notifications: {
                    title: "Task Notifications",
                    description:
                        "Get notified when tasks are assigned, updated, or completed",
                    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
                    color: "blue",
                },
                sales_notifications: {
                    title: "Sales Notifications",
                    description:
                        "Get notified about new sales, client updates, and sales milestones",
                    icon: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
                    color: "yellow",
                },
                expense_notifications: {
                    title: "Expense Notifications",
                    description:
                        "Get notified when expenses are submitted, approved, or rejected",
                    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
                    color: "green",
                },
                goal_notifications: {
                    title: "Goal Notifications",
                    description:
                        "Get notified about goal progress, achievements, and milestones",
                    icon: "M13 10V3L4 14h7v7l9-11h-7z",
                    color: "purple",
                },
                content_notifications: {
                    title: "Content Notifications",
                    description:
                        "Get notified when content is published, scheduled, or requires review",
                    icon: "M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z",
                    color: "indigo",
                },
            };

            return (
                details[key] || {
                    title: key
                        .replace(/_/g, " ")
                        .replace(/\b\w/g, (l) => l.toUpperCase()),
                    description: "Toggle this notification preference",
                    icon: "M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9",
                    color: "gray",
                }
            );
        };

        return {
            // State
            preferences,
            loading,
            error,
            success,
            saving,
            savingPreference,

            // Computed
            hasAnyEnabled,
            allPreferencesEnabled,
            individualPreferences,

            // Methods
            handlePreferenceChange,
            handleToggleAll,
            handleSaveAll,
            handleResetToDefaults,
            getPreferenceDetails,
            clearSuccess,
            clearError,
        };
    },
};
