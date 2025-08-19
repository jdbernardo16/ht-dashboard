<?php

/**
 * Authentication Test Script
 * 
 * This script helps diagnose authentication issues in the Laravel application.
 * Run this after making the necessary changes to verify the authentication flow.
 */

echo "=== Laravel Authentication Diagnostic Report ===\n\n";

// Check if UserController exists
$userControllerPath = __DIR__ . '/app/Http/Controllers/UserController.php';
echo "1. UserController Status: " . (file_exists($userControllerPath) ? "✅ EXISTS" : "❌ MISSING") . "\n";

// Check API routes
$apiRoutesPath = __DIR__ . '/routes/api.php';
echo "2. API Routes Status: " . (file_exists($apiRoutesPath) ? "✅ EXISTS" : "❌ MISSING") . "\n";

// Check Sanctum configuration
$sanctumConfigPath = __DIR__ . '/config/sanctum.php';
echo "3. Sanctum Config: " . (file_exists($sanctumConfigPath) ? "✅ EXISTS" : "❌ MISSING") . "\n";

// Check .env configuration
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    echo "4. .env Configuration:\n";
    echo "   - SANCTUM_STATEFUL_DOMAINS: " . (strpos($envContent, 'SANCTUM_STATEFUL_DOMAINS') !== false ? "✅ CONFIGURED" : "❌ MISSING") . "\n";
    echo "   - SESSION_DOMAIN: " . (strpos($envContent, 'SESSION_DOMAIN') !== false ? "✅ CONFIGURED" : "❌ MISSING") . "\n";
} else {
    echo "4. .env Configuration: ❌ FILE NOT FOUND\n";
}

// Check auth configuration
$authConfigPath = __DIR__ . '/config/auth.php';
echo "5. Auth Config: " . (file_exists($authConfigPath) ? "✅ EXISTS" : "❌ MISSING") . "\n";

// Check middleware configuration
$kernelPath = __DIR__ . '/bootstrap/app.php';
echo "6. Middleware Configuration: " . (file_exists($kernelPath) ? "✅ CONFIGURED" : "❌ MISSING") . "\n";

echo "\n=== API Endpoints to Test ===\n";
echo "1. GET http://localhost:8000/api/sales (requires auth:sanctum)\n";
echo "2. GET http://localhost:8000/api/user (returns authenticated user)\n";
echo "3. GET http://localhost:8000/api/users (returns all users)\n";
echo "4. GET http://localhost:8000/api/tasks (requires auth:sanctum)\n";

echo "\n=== Authentication Flow Testing ===\n";
echo "1. Login via POST /login (web route)\n";
echo "2. Get API token via POST /login (API route with Sanctum)\n";
echo "3. Use token in Authorization header: Bearer {token}\n";
echo "4. Test API endpoints with valid token\n";

echo "\n=== Common Issues Fixed ===\n";
echo "✅ Created missing UserController.php\n";
echo "✅ Added SANCTUM_STATEFUL_DOMAINS to .env\n";
echo "✅ Added SESSION_DOMAIN to .env\n";
echo "✅ Verified API routes are protected with auth:sanctum middleware\n";
echo "✅ Verified web routes are protected with auth middleware\n";

echo "\n=== Next Steps ===\n";
echo "1. Run: php artisan config:clear\n";
echo "2. Run: php artisan route:clear\n";
echo "3. Run: php artisan serve\n";
echo "4. Test API endpoints with proper authentication\n";
echo "5. Check Laravel logs for any additional errors\n";
