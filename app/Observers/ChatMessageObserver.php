<?php

namespace App\Observers;

use App\Services\Codex\ChatMentionTracker;
use Illuminate\Database\Eloquent\Model;

/**
 * Observer for ChatMessage model events.
 *
 * Sprint 16 (F-12.1.5): Handles automatic mention scanning for chat messages.
 * Runs synchronously - no queue workers needed.
 *
 * Note: This observer is ready to use once the ChatMessage model is implemented.
 * Register in AppServiceProvider when chat is added:
 *   ChatMessage::observe(ChatMessageObserver::class);
 */
class ChatMessageObserver
{
    public function __construct(
        private ChatMentionTracker $chatMentionTracker
    ) {}

    /**
     * Handle the ChatMessage "created" event.
     */
    public function created(Model $message): void
    {
        $this->chatMentionTracker->scanMessage($message);
    }

    /**
     * Handle the ChatMessage "updated" event.
     */
    public function updated(Model $message): void
    {
        // Only scan if content was changed
        if ($message->wasChanged('content')) {
            $this->chatMentionTracker->scanMessage($message);
        }
    }

    /**
     * Handle the ChatMessage "deleted" event.
     */
    public function deleted(Model $message): void
    {
        // Mentions are cleaned up by database cascade (foreign key)
    }
}
