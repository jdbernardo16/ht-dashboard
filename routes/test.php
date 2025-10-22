<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestNotificationController;

/*
|--------------------------------------------------------------------------
| Test Routes
|--------------------------------------------------------------------------
|
| These routes are for testing purposes only and should be removed in production.
|
*/

Route::middleware(['auth', 'web'])->group(function () {
    // WebSocket connection test page
    Route::get('/test/websocket', function () {
        return inertia('TestWebSocketConnection');
    })->name('test.websocket');
    
    // Test notification endpoint
    Route::post('/test/send-notification', [TestNotificationController::class, 'sendTestNotification'])
        ->name('test.send-notification');
});