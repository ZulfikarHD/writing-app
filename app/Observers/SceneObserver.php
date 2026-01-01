<?php

namespace App\Observers;

use App\Models\Scene;
use App\Services\Codex\MentionTracker;

/**
 * Observer for Scene model events.
 *
 * Handles automatic mention scanning when scenes are created/updated.
 * Runs synchronously - no queue workers needed.
 */
class SceneObserver
{
    public function __construct(
        private MentionTracker $mentionTracker
    ) {}

    /**
     * Handle the Scene "created" event.
     */
    public function created(Scene $scene): void
    {
        // Scan for mentions if scene has content
        if (! empty($scene->content)) {
            $this->mentionTracker->scanScene($scene);
        }
    }

    /**
     * Handle the Scene "updated" event.
     *
     * Note: The SceneController::updateContent already handles scanning
     * for the auto-save endpoint. This catches updates from other sources.
     */
    public function updated(Scene $scene): void
    {
        // Only scan if content was changed
        if ($scene->wasChanged('content')) {
            $this->mentionTracker->scanScene($scene);
        }
    }

    /**
     * Handle the Scene "deleted" event.
     */
    public function deleted(Scene $scene): void
    {
        // Mentions are cleaned up by database cascade (foreign key)
    }
}
