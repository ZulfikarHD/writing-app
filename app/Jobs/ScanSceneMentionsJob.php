<?php

namespace App\Jobs;

use App\Models\Scene;
use App\Services\Codex\MentionTracker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Cache;

class ScanSceneMentionsJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $sceneId
    ) {}

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        // Prevent overlapping jobs for the same scene
        return [new WithoutOverlapping($this->sceneId)->dontRelease()];
    }

    /**
     * Execute the job.
     */
    public function handle(MentionTracker $tracker): void
    {
        // Clear the debounce cache key to allow new scans
        Cache::forget("scene_mention_scan_{$this->sceneId}");

        $scene = Scene::find($this->sceneId);

        if (! $scene) {
            return;
        }

        $tracker->scanScene($scene);
    }
}
