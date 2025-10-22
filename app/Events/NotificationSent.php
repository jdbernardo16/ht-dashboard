<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The notification instance.
     *
     * @var \App\Models\Notification
     */
    public $notification;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Notification $notification
     * @return void
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
        
        // Log when notification event is created
        Log::info('NotificationSent event created', [
            'notification_id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'user_id' => $notification->user_id,
            'broadcast_connection' => config('broadcasting.default'),
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channel = new PrivateChannel('notifications.' . $this->notification->user_id);
        
        // Log the broadcast channel
        Log::info('Notification broadcast channel created', [
            'channel_name' => $channel->name,
            'notification_id' => $this->notification->id,
            'broadcast_driver' => config('broadcasting.default'),
        ]);
        
        return $channel;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'notification.sent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'data' => $this->notification->data,
            'created_at' => $this->notification->created_at->toISOString(),
        ];
    }
}