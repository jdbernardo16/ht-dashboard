<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->notifications()->with('user');

        // Filter by read status
        if ($request->has('read')) {
            $read = filter_var($request->get('read'), FILTER_VALIDATE_BOOLEAN);
            if ($read) {
                $query->read();
            } else {
                $query->unread();
            }
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Auth::user()->notifications()->unread()->count()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Notifications are typically created by the system, not users
        return response()->json(['message' => 'Method not allowed'], 405);
    }

    /**
     * Display the specified notification.
     */
    public function show(string $id)
    {
        $notification = Auth::user()->notifications()->with('user')->findOrFail($id);

        return response()->json([
            'notification' => $notification
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function update(Request $request, string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        $validated = $request->validate([
            'read' => 'sometimes|boolean'
        ]);

        if (isset($validated['read']) && $validated['read']) {
            $notification->markAsRead();
        } elseif (isset($validated['read']) && !$validated['read']) {
            $notification->markAsUnread();
        }

        return response()->json([
            'notification' => $notification->fresh(),
            'unread_count' => Auth::user()->notifications()->unread()->count()
        ]);
    }

    /**
     * Remove the specified notification from storage.
     */
    public function destroy(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted successfully',
            'unread_count' => Auth::user()->notifications()->unread()->count()
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->notifications()->unread()->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read',
            'unread_count' => 0
        ]);
    }

    /**
     * Get count of unread notifications.
     */
    public function getUnreadCount()
    {
        $count = Auth::user()->notifications()->unread()->count();

        return response()->json([
            'unread_count' => $count
        ]);
    }

    /**
     * Display the notifications page.
     */
    public function indexPage()
    {
        return Inertia::render('Notifications/Index');
    }

    /**
     * Send a test notification (for development/demo purposes).
     */
    public function sendTestNotification(Request $request)
    {
        $user = Auth::user();
        $type = $request->input('type', 'system_alert');
        
        $notificationService = new \App\Services\NotificationService();
        
        switch ($type) {
            case 'task_update':
                $notificationService::sendTaskUpdate($user, 'Test Task', 'completed', ['task_id' => 999]);
                break;
            case 'expense_approved':
                $notificationService::sendExpenseApproval($user, 100.00, 'Software', ['expense_id' => 999]);
                break;
            case 'goal_achieved':
                $notificationService::sendGoalAchievement($user, 'Test Goal', ['goal_id' => 999]);
                break;
            case 'sale_completed':
                $notificationService::sendSaleCompletion($user, 500.00, 'Test Client', ['sale_id' => 999]);
                break;
            case 'content_published':
                $notificationService::sendContentPublication($user, 'Test Content', 'Facebook', ['content_id' => 999]);
                break;
            default:
                $notificationService::sendSystemAlert($user, 'This is a test notification from the system.', ['test' => true]);
                break;
        }

        return response()->json([
            'message' => 'Test notification sent successfully',
            'type' => $type
        ]);
    }
}
