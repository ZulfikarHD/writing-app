<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast event for new chat messages.
 *
 * Sprint 21 (F4): Real-time Chat Updates with Laravel Reverb
 */
class ChatMessageCreated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public ChatMessage $message
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.thread.'.$this->message->thread_id),
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
            'message' => [
                'id' => $this->message->id,
                'thread_id' => $this->message->thread_id,
                'role' => $this->message->role,
                'content' => $this->message->content,
                'model_used' => $this->message->model_used,
                'tokens_input' => $this->message->tokens_input,
                'tokens_output' => $this->message->tokens_output,
                'created_at' => $this->message->created_at->toISOString(),
            ],
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.created';
    }
}
