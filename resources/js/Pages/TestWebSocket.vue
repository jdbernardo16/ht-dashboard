<script setup>
import { onMounted, ref } from "vue";

const status = ref("Checking connection...");
const messages = ref([]);
const isConnected = ref(false);

function addMessage(message) {
    messages.value.push(`${new Date().toLocaleTimeString()}: ${message}`);
}

function testConnection() {
    if (window.Echo) {
        addMessage("Echo instance found");

        // Test basic connection
        try {
            // Try to connect to a test channel
            window.Echo.channel("test-channel")
                .listen(".TestEvent", (e) => {
                    addMessage(`Received test event: ${JSON.stringify(e)}`);
                })
                .subscribed(() => {
                    addMessage("Successfully subscribed to test-channel");
                    status.value = "Connected and subscribed";
                    isConnected.value = true;
                })
                .error((error) => {
                    addMessage(`Subscription error: ${error}`);
                    status.value = "Connection error";
                    isConnected.value = false;
                });

            // Test private channel
            window.Echo.private("test-private")
                .listen(".PrivateTestEvent", (e) => {
                    addMessage(
                        `Received private test event: ${JSON.stringify(e)}`
                    );
                })
                .subscribed(() => {
                    addMessage(
                        "Successfully subscribed to test-private channel"
                    );
                })
                .error((error) => {
                    addMessage(`Private channel error: ${error}`);
                });
        } catch (error) {
            addMessage(`Error setting up Echo: ${error.message}`);
            status.value = "Echo setup error";
            isConnected.value = false;
        }
    } else {
        addMessage("Echo instance not found");
        status.value = "Echo not available";
        isConnected.value = false;
    }
}

function disconnect() {
    if (window.Echo) {
        window.Echo.leave("test-channel");
        window.Echo.leave("test-private");
        addMessage("Disconnected from channels");
        status.value = "Disconnected";
        isConnected.value = false;
    }
}

onMounted(() => {
    addMessage("WebSocket test page loaded");
    testConnection();
});
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <div class="max-w-4xl mx-auto py-8 px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                Reverb WebSocket Connection Test
            </h1>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Connection Status</h2>
                <div class="flex items-center space-x-2">
                    <div
                        class="w-4 h-4 rounded-full"
                        :class="isConnected ? 'bg-green-500' : 'bg-red-500'"
                    ></div>
                    <span class="text-lg">{{ status }}</span>
                </div>

                <div class="mt-4 space-x-2">
                    <button
                        @click="testConnection"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                    >
                        Test Connection
                    </button>
                    <button
                        @click="disconnect"
                        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                    >
                        Disconnect
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Messages</h2>
                <div class="bg-gray-50 rounded p-4 h-64 overflow-y-auto">
                    <div v-if="messages.length === 0" class="text-gray-500">
                        No messages yet...
                    </div>
                    <div
                        v-for="(message, index) in messages"
                        :key="index"
                        class="text-sm font-mono mb-1"
                    >
                        {{ message }}
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg p-6 mt-6">
                <h3 class="text-lg font-semibold mb-2">
                    How to Test Server-Side Events
                </h3>
                <p class="text-gray-700 mb-2">
                    To test the WebSocket connection from the server side, you
                    can run:
                </p>
                <pre
                    class="bg-gray-800 text-white p-3 rounded text-sm overflow-x-auto"
                >
php artisan tinker
> broadcast(new \App\Events\TestEvent('Hello from server!'));
                </pre>
                <p class="text-gray-700 mt-2">
                    You should see the event appear in the messages above.
                </p>
            </div>
        </div>
    </div>
</template>
