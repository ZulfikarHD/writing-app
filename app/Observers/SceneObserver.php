<?php

namespace App\Observers;

use App\Jobs\ScanSceneMentionsJob;
use App\Models\Scene;
use Illuminate\Support\Facades\Cache;

class SceneObserver
{
    /**
     * Debounce delay in seconds for mention scanning.
     */
    private const DEBOUNCE_DELAY = 5;

    /**
     * Handle the Scene "created" event.
     */
    public function created(Scene $scene): void
    {
        $this->dispatchMentionScan($scene);
    }

    /**
     * Handle the Scene "updated" event.
     */
    public function updated(Scene $scene): void
    {
        // Only scan if content was changed
        if ($scene->wasChanged('content')) {
            $this->dispatchMentionScan($scene);
        }
    }

    /**
     * Handle the Scene "deleted" event.
     */
    public function deleted(Scene $scene): void
    {
        // Mentions will be cleaned up by database cascade or job
    }

    /**
     * Handle the Scene "restored" event.
     */
    public function restored(Scene $scene): void
    {
        $this->dispatchMentionScan($scene);
    }

    /**
     * Handle the Scene "force deleted" event.
     */
    public function forceDeleted(Scene $scene): void
    {
        // Mentions should be deleted via database cascade
    }

    /**
     * Dispatch the mention scan job with debouncing.
     * Uses cache to prevent multiple jobs for the same scene within the debounce window.
     */
    private function dispatchMentionScan(Scene $scene): void
    {
        $cacheKey = "scene_mention_scan_{$scene->id}";

        // Check if a scan is already scheduled
        if (Cache::has($cacheKey)) {
            return;
        }

        // Mark that a scan is scheduled
        Cache::put($cacheKey, true, self::DEBOUNCE_DELAY + 5);

        // Dispatch the job with delay
        ScanSceneMentionsJob::dispatch($scene->id)
            ->delay(now()->addSeconds(self::DEBOUNCE_DELAY));
    }
}
