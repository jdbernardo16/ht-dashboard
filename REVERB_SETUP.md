# Reverb Setup Guide

This guide explains how to run Reverb WebSocket server for your Laravel application.

## Configuration

Reverb is configured to run on:

-   Host: localhost
-   Port: 8080
-   App Key: xqfyetvrmz1afdfq1vvp

## Running Reverb

### Option 1: Using npm scripts

```bash
# Start only Reverb
npm run reverb

# Start Laravel server + Reverb + Vite
npm run dev-all
```

### Option 2: Using shell scripts

```bash
# Start only Reverb
./start-reverb.sh

# Start full development environment (Laravel + Reverb + Queue + Logs + Vite)
./start-dev.sh
```

### Option 3: Using composer script

```bash
# Start full development environment including Reverb
composer run dev
```

### Option 4: Manual commands

```bash
# Start only Reverb
php artisan reverb:start

# Start Laravel server in one terminal
php artisan serve

# Start Reverb in another terminal
php artisan reverb:start
```

## Running Reverb in the Background

If you want to run Reverb in the background:

### On macOS/Linux:

```bash
# Start Reverb in background
nohup php artisan reverb:start > reverb.log 2>&1 &

# To stop it
ps aux | grep "reverb:start"
kill [PID]

# Or using a screen/tmux session
screen -S reverb
php artisan reverb:start
# Press Ctrl+A then D to detach
# Reattach with: screen -r reverb
```

### On Windows:

```powershell
# Start in background PowerShell
Start-Process -WindowStyle Hidden php -ArgumentList "artisan reverb:start"
```

## Troubleshooting

1. **WebSocket connection error**: Make sure Reverb is running on port 8080
2. **Port already in use**: Check if another process is using port 8080
3. **Connection refused**: Verify your firewall settings allow localhost connections

## Integration with Frontend

Your frontend is already configured to connect to Reverb using the environment variables:

-   VITE_REVERB_APP_KEY
-   VITE_REVERB_HOST
-   VITE_REVERB_PORT
-   VITE_REVERB_SCHEME

These are automatically loaded from your .env file.
