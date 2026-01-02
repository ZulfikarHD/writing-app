<?php

namespace App\Events;

use App\Models\ChatThread;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast event for chat thread updates.
 *
 * Sprint 21 (F4): Real-time Chat Updates with Laravel Reverb
 */
class ChatThreadUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public ChatThread $thread,
        public string $updateType = 'updated'
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // Broadcast to the thread channel
            new PrivateChannel('chat.thread.'.$this->thread->id),
            // Also broadcast to the novel channel for thread list updates
            new PrivateChannel('chat.novel.'.$this->thread->novel_id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'thread' => [
                'id' => $this->thread->id,
                'novel_id' => $this->thread->novel_id,
                'title' => $this->thread->title,
                'model' => $this->thread->model,
                'is_pinned' => $this->thread->is_pinned,
                'archived_at' => $this->thread->archived_at?->toISOString(),
                'updated_at' => $this->thread->updated_at->toISOString(),
            ],
            'update_type' => $this->updateType,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'thread.updated';
    }
}
