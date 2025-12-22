<?php

namespace App\Events;

use App\Models\User;
use App\Models\EventReminder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventReminderSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public EventReminder $reminder,
        public string $title,
        public string $message
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->user->id}"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'event.reminder.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'type' => 'event_reminder',
            'title' => $this->title,
            'message' => $this->message,
            'data' => [
                'reminder_id' => $this->reminder->id,
                'event_type' => $this->reminder->event_type,
                'event_id' => $this->reminder->event_id,
            ],
            'timestamp' => now()->toIso8601String(),
            'icon' => 'fe fe-bell',
            'color' => 'warning',
        ];
    }
}
