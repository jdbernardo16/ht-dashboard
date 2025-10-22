<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref } from "vue";

const page = usePage();
const user = page.props.auth.user;

const connectionStatus = ref("disconnected");
const lastMessage = ref(null);
const messages = ref([]);
const testResults = ref([]);
const isRunningTests = ref(false);

let echoInstance = null;
let testNotificationChannel = null;

onMounted(() => {
    initializeWebSocketDebugging();
});

onUnmounted(() => {
    cleanup();
});

function initializeWebSocketDebugging() {
    addLog("info", "Initializing WebSocket debugging...");

    // Check if Echo is available
    if (!window.Echo) {
        addLog(
            "error",
            "Echo is not available. Make sure Laravel Echo is properly loaded."
        );
        return;
    }

    // Log Echo configuration
    addLog("info", "Echo Configuration:", {
        broadcaster: window.Echo.options.broadcaster,
        key: window.Echo.options.key,
        wsHost: window.Echo.options.wsHost,
        wsPort: window.Echo.options.wsPort,
        wssPort: window.Echo.options.wssPort,
        forceTLS: window.Echo.options.forceTLS,
        enabledTransports: window.Echo.options.enabledTransports,
        disabledTransports: window.Echo.options.disabledTransports,
    });

    // Monitor connection state
    if (window.Echo.connector && window.Echo.connector.pusher) {
        const connection = window.Echo.connector.pusher.connection;

        connection.bind("connected", () => {
            connectionStatus.value = "connected";
            addLog("success", "WebSocket connected successfully");
        });

        connection.bind("disconnected", () => {
            connectionStatus.value = "disconnected";
            addLog("warning", "WebSocket disconnected");
        });

        connection.bind("error", (error) => {
            connectionStatus.value = "error";
            addLog("error", "WebSocket connection error:", error);
        });

        connection.bind("connecting", () => {
            connectionStatus.value = "connecting";
            addLog("info", "WebSocket connecting...");
        });
    }

    // Connect to notification channel
    connectToNotificationChannel();

    // Request notification permission
    requestNotificationPermission();
}

function connectToNotificationChannel() {
    if (!user || !user.id) {
        addLog("error", "User not authenticated");
        return;
    }

    const channelName = `notifications.${user.id}`;
    addLog("info", `Connecting to notification channel: ${channelName}`);

    testNotificationChannel = window.Echo.private(channelName)
        .subscribed(() => {
            addLog("success", `Successfully subscribed to ${channelName}`);
        })
        .error((error) => {
            addLog("error", `Failed to subscribe to ${channelName}:`, error);
        })
        .listen(".notification.sent", (e) => {
            addLog("success", "Received real-time notification:", e);
            lastMessage.value = e;
            messages.value.unshift(e);

            // Show browser notification
            showBrowserNotification(e);
        });
}

function showBrowserNotification(notification) {
    if ("Notification" in window && Notification.permission === "granted") {
        new Notification(notification.title, {
            body: notification.message,
            icon: "/images/ht-logo.png",
            tag: notification.id,
        });
    }
}

function requestNotificationPermission() {
    if ("Notification" in window) {
        Notification.requestPermission().then((permission) => {
            addLog("info", `Notification permission: ${permission}`);
        });
    } else {
        addLog("warning", "Browser notifications not supported");
    }
}

async function runTests() {
    isRunningTests.value = true;
    testResults.value = [];

    addLog("info", "Starting WebSocket connection tests...");

    // Test 1: Check Echo availability
    await runTest("Echo Availability", () => {
        if (!window.Echo) {
            throw new Error("Echo is not available");
        }
        return true;
    });

    // Test 2: Check configuration
    await runTest("Echo Configuration", () => {
        const required = ["broadcaster", "key", "wsHost", "wsPort"];
        const missing = required.filter((key) => !window.Echo.options[key]);
        if (missing.length > 0) {
            throw new Error(`Missing configuration: ${missing.join(", ")}`);
        }
        return true;
    });

    // Test 3: Check connection state
    await runTest("WebSocket Connection", () => {
        const connection = window.Echo.connector?.pusher?.connection;
        if (!connection) {
            throw new Error("Pusher connection not available");
        }
        return connection.state === "connected";
    });

    // Test 4: Test channel subscription
    await runTest("Channel Subscription", () => {
        if (!testNotificationChannel) {
            throw new Error("Notification channel not initialized");
        }
        return true;
    });

    // Test 5: Test notification permission
    await runTest("Notification Permission", () => {
        if (!("Notification" in window)) {
            throw new Error("Browser notifications not supported");
        }
        return Notification.permission !== "denied";
    });

    // Test 6: Test API connectivity
    await runTest("API Connectivity", async () => {
        try {
            const response = await fetch("/api/notifications", {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
            });
            return response.ok;
        } catch (error) {
            throw new Error(`API request failed: ${error.message}`);
        }
    });

    isRunningTests.value = false;
    addLog("info", "WebSocket connection tests completed");
}

async function runTest(testName, testFunction) {
    const startTime = Date.now();

    try {
        const result = await testFunction();
        const duration = Date.now() - startTime;

        testResults.value.push({
            name: testName,
            status: "passed",
            duration,
            result,
        });

        addLog("success", `✓ ${testName} (${duration}ms)`);
    } catch (error) {
        const duration = Date.now() - startTime;

        testResults.value.push({
            name: testName,
            status: "failed",
            duration,
            error: error.message,
        });

        addLog("error", `✗ ${testName} (${duration}ms): ${error.message}`);
    }
}

function sendTestNotification() {
    addLog("info", "Sending test notification...");

    fetch("/test/send-notification", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify({
            user_id: user.id,
            type: "test_notification",
            title: "Test Notification",
            message: "This is a test notification from WebSocket debugger",
            data: {
                test_id: Date.now(),
                timestamp: new Date().toISOString(),
                source: "websocket_debugger",
            },
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                addLog("success", "Test notification sent successfully");
            } else {
                addLog(
                    "error",
                    "Failed to send test notification:",
                    data.error
                );
            }
        })
        .catch((error) => {
            addLog("error", "Error sending test notification:", error);
        });
}

function clearLogs() {
    messages.value = [];
    lastMessage.value = null;
    testResults.value = [];
    addLog("info", "Logs cleared");
}

function addLog(level, message, data = null) {
    const logEntry = {
        timestamp: new Date().toISOString(),
        level,
        message,
        data,
    };

    messages.value.unshift(logEntry);

    // Also log to console for debugging
    const consoleMethod =
        level === "error" ? "error" : level === "warning" ? "warn" : "log";
    console[consoleMethod](`[WebSocket Debug] ${message}`, data || "");
}

function cleanup() {
    if (testNotificationChannel) {
        window.Echo.leave(testNotificationChannel.name);
        testNotificationChannel = null;
    }
}

function getConnectionStatusColor() {
    switch (connectionStatus.value) {
        case "connected":
            return "text-green-600";
        case "connecting":
            return "text-yellow-600";
        case "error":
            return "text-red-600";
        default:
            return "text-gray-600";
    }
}

function getTestStatusColor(status) {
    switch (status) {
        case "passed":
            return "text-green-600";
        case "failed":
            return "text-red-600";
        default:
            return "text-gray-600";
    }
}
</script>

<template>
    <Head title="WebSocket Connection Test" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                WebSocket Connection Test
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                Connection Status
                            </h3>
                            <div class="flex items-center space-x-2">
                                <div
                                    class="w-3 h-3 rounded-full"
                                    :class="{
                                        'bg-green-500':
                                            connectionStatus === 'connected',
                                        'bg-yellow-500':
                                            connectionStatus === 'connecting',
                                        'bg-red-500':
                                            connectionStatus === 'error',
                                        'bg-gray-500':
                                            connectionStatus === 'disconnected',
                                    }"
                                ></div>
                                <span
                                    :class="getConnectionStatusColor()"
                                    class="font-medium"
                                >
                                    {{
                                        connectionStatus
                                            .charAt(0)
                                            .toUpperCase() +
                                        connectionStatus.slice(1)
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                User Information
                            </h3>
                            <div class="text-sm text-gray-600">
                                <p>Name: {{ user.name }}</p>
                                <p>ID: {{ user.id }}</p>
                                <p>Role: {{ user.role }}</p>
                                <p>
                                    Notification Channel: notifications.{{
                                        user.id
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                Test Controls
                            </h3>
                            <div class="flex space-x-4">
                                <button
                                    @click="runTests"
                                    :disabled="isRunningTests"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                                >
                                    {{
                                        isRunningTests
                                            ? "Running Tests..."
                                            : "Run Connection Tests"
                                    }}
                                </button>
                                <button
                                    @click="sendTestNotification"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                                >
                                    Send Test Notification
                                </button>
                                <button
                                    @click="clearLogs"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                                >
                                    Clear Logs
                                </button>
                            </div>
                        </div>

                        <div v-if="testResults.length > 0" class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                Test Results
                            </h3>
                            <div class="space-y-2">
                                <div
                                    v-for="result in testResults"
                                    :key="result.name"
                                    class="flex items-center justify-between p-3 border rounded-md"
                                >
                                    <div class="flex items-center space-x-2">
                                        <span
                                            :class="
                                                getTestStatusColor(
                                                    result.status
                                                )
                                            "
                                        >
                                            {{
                                                result.status === "passed"
                                                    ? "✓"
                                                    : "✗"
                                            }}
                                        </span>
                                        <span class="font-medium">{{
                                            result.name
                                        }}</span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        {{ result.duration }}ms
                                    </div>
                                    <div
                                        v-if="result.error"
                                        class="text-sm text-red-600"
                                    >
                                        {{ result.error }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="lastMessage" class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                Last Received Notification
                            </h3>
                            <div
                                class="p-4 bg-green-50 border border-green-200 rounded-md"
                            >
                                <pre class="text-sm text-gray-800">{{
                                    JSON.stringify(lastMessage, null, 2)
                                }}</pre>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                Debug Logs
                            </h3>
                            <div
                                class="border rounded-md max-h-96 overflow-y-auto"
                            >
                                <div
                                    v-for="log in messages.slice(0, 50)"
                                    :key="log.timestamp"
                                    class="p-3 border-b last:border-b-0"
                                >
                                    <div class="flex items-start space-x-2">
                                        <span
                                            class="text-xs text-gray-500 whitespace-nowrap"
                                            >{{
                                                new Date(
                                                    log.timestamp
                                                ).toLocaleTimeString()
                                            }}</span
                                        >
                                        <span
                                            :class="{
                                                'text-red-600':
                                                    log.level === 'error',
                                                'text-yellow-600':
                                                    log.level === 'warning',
                                                'text-green-600':
                                                    log.level === 'success',
                                                'text-blue-600':
                                                    log.level === 'info',
                                            }"
                                            class="font-medium"
                                        >
                                            {{ log.level.toUpperCase() }}
                                        </span>
                                        <span class="text-sm text-gray-800">{{
                                            log.message
                                        }}</span>
                                    </div>
                                    <div v-if="log.data" class="mt-2 ml-8">
                                        <pre class="text-xs text-gray-600">{{
                                            JSON.stringify(log.data, null, 2)
                                        }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
