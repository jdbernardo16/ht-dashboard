#!/bin/bash

# Start Reverb Server
# This script starts only the Reverb WebSocket server

echo "Starting Reverb WebSocket Server..."
echo "Reverb Server: ws://localhost:8080"
echo "Press Ctrl+C to stop the server"
echo "================================="

# Start Reverb
php artisan reverb:start