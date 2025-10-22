import { ref } from "vue";
import { createApiHeaders } from "../Utils/utils.js";

export function useEmailPreferences() {
    const preferences = ref({
        email_notifications_enabled: true,
        task_notifications: true,
        sales_notifications: true,
        expense_notifications: true,
        goal_notifications: true,
        content_notifications: true,
    });

    const loading = ref(false);
    const error = ref(null);
    const success = ref(null);

    /**
     * Fetch user's email preferences
     */
    const fetchEmailPreferences = async () => {
        try {
            loading.value = true;
            error.value = null;

            const response = await fetch("/api/user/email-preferences", {
                headers: createApiHeaders(),
            });

            if (!response.ok) {
                throw new Error("Failed to fetch email preferences");
            }

            const data = await response.json();
            preferences.value = data.preferences;
        } catch (err) {
            error.value = err.message;
            console.error("Error fetching email preferences:", err);
        } finally {
            loading.value = false;
        }
    };

    /**
     * Update user's email preferences
     */
    const updateEmailPreferences = async (updatedPreferences) => {
        try {
            loading.value = true;
            error.value = null;
            success.value = null;

            const response = await fetch("/api/user/email-preferences", {
                method: "PUT",
                headers: createApiHeaders({
                    "Content-Type": "application/json",
                }),
                body: JSON.stringify(updatedPreferences),
            });

            if (!response.ok) {
                throw new Error("Failed to update email preferences");
            }

            const data = await response.json();
            preferences.value = data.preferences;
            success.value = "Email preferences updated successfully";

            return data;
        } catch (err) {
            error.value = err.message;
            console.error("Error updating email preferences:", err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Toggle all email notifications
     */
    const toggleAllEmailNotifications = async () => {
        try {
            loading.value = true;
            error.value = null;
            success.value = null;

            const response = await fetch(
                "/api/user/email-notifications/toggle",
                {
                    method: "POST",
                    headers: createApiHeaders(),
                }
            );

            if (!response.ok) {
                throw new Error("Failed to toggle email notifications");
            }

            const data = await response.json();
            preferences.value = data.preferences;
            success.value = data.message;

            return data;
        } catch (err) {
            error.value = err.message;
            console.error("Error toggling email notifications:", err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Update a specific preference
     */
    const updatePreference = async (key, value) => {
        const updatedPreferences = { ...preferences.value, [key]: value };
        return await updateEmailPreferences(updatedPreferences);
    };

    /**
     * Reset success message
     */
    const clearSuccess = () => {
        success.value = null;
    };

    /**
     * Reset error message
     */
    const clearError = () => {
        error.value = null;
    };

    return {
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
    };
}
