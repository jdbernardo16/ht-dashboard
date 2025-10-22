<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestNotificationController extends Controller
{
    /**
     * Send a test notification for WebSocket debugging
     */
    public function sendTestNotification(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'type' => 'required|string',
                'title' => 'required|string',
                'message' => 'required|string',
                'data' => 'array',
            ]);

            $user = User::find($request->user_id);
            
            Log::info('Test notification requested', [
                'requested_by' => auth()->id(),
                'target_user' => $user->id,
                'type' => $request->type,
                'title' => $request->title,
                'timestamp' => now()->toISOString(),
            ]);

            $notification = NotificationService::createNotification(
                $user,
                $request->type,
                $request->title,
                $request->message,
                $request->data ?? []
            );

            Log::info('Test notification created successfully', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'type' => $request->type,
                'broadcast_channel' => 'private-notifications.' . $user->id,
            ]);

            return response()->json([
                'success' => true,
                'notification_id' => $notification->id,
                'message' => 'Test notification sent successfully',
                'broadcast_channel' => 'private-notifications.' . $user->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send test notification', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'requested_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}