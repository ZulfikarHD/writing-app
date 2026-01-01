<?php

namespace App\Observers;

use App\Models\Scene;
use App\Services\Codex\MentionTracker;

/**
 * Observer for Scene model events.
 *
 * Handles automatic mention scanning when scenes are created/updated.
 * Runs synchronously - no queue workers needed.
 *
 * Sprint 16 (F-12.1.4): Now triggers on summary changes too.
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
        // Scan for mentions if scene has content or summary
        if (! empty($scene->content) || ! empty($scene->summary)) {
            $this->mentionTracker->scanScene($scene);
        }
    }

    /**
     * Handle the Scene "updated" event.
     *
     * Sprint 16: Now also triggers when summary changes.
     *
     * Note: The SceneController::updateContent already handles scanning
     * for the auto-save endpoint. This catches updates from other sources.
     */
    public function updated(Scene $scene): void
    {
        // Sprint 16: Scan if content OR summary was changed
        if ($scene->wasChanged('content') || $scene->wasChanged('summary')) {
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
