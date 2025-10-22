#!/bin/bash

# Start Development Server with Reverb
# This script starts the Laravel server and Reverb WebSocket server together

echo "Starting Hidden Treasures Dashboard with Reverb..."
echo "============================================="
echo "Laravel Server: http://localhost:8000"
echo "Reverb Server: ws://localhost:8080"
echo "============================================="

# Check if composer is available
if ! command -v composer &> /dev/null; then
    echo "Error: composer is not installed or not in PATH"
    exit 1
fi

# Check if npm is available
if ! command -v npm &> /dev/null; then
    echo "Error: npm is not installed or not in PATH"
    exit 1
fi

# Start the development environment
composer run dev