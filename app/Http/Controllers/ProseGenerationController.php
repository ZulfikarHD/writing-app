<?php

namespace App\Http\Controllers;

use App\Models\Scene;
use App\Services\Editor\ProseGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProseGenerationController extends Controller
{
    public function __construct(
        protected ProseGenerationService $proseService
    ) {}

    /**
     * Generate prose for a scene with streaming response.
     */
    public function generate(Request $request, Scene $scene): StreamedResponse
    {
        // Authorize access to the scene's novel
        $novel = $scene->chapter?->act?->novel;
        Gate::authorize('update', $novel);

        $validated = $request->validate([
            'mode' => ['sometimes', 'string', 'in:scene_beat,continue,custom'],
            'beat' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'instructions' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'content_before' => ['sometimes', 'nullable', 'string', 'max:50000'],
            'prompt_id' => ['sometimes', 'nullable', 'integer', 'exists:prompts,id'],
            'connection_id' => ['sometimes', 'nullable', 'integer', 'exists:ai_connections,id'],
            'model' => ['sometimes', 'nullable', 'string', 'max:100'],
            'temperature' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['sometimes', 'nullable', 'integer', 'min:100', 'max:8000'],
        ]);

        return response()->stream(function () use ($scene, $validated) {
            $user = auth()->user();

            foreach ($this->proseService->generate($scene, $user, $validated) as $chunk) {
                echo 'data: '.json_encode($chunk)."\n\n";

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Get available generation modes and prompts.
     */
    public function options(Request $request)
    {
        $user = auth()->user();

        // Get prose prompts accessible by user
        $prompts = \App\Models\Prompt::query()
            ->accessibleBy($user->id)
            ->active()
            ->ofType(\App\Models\Prompt::TYPE_PROSE)
            ->orderBy('is_system', 'desc')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'description', 'is_system']);

        // Get active AI connections
        $connections = \App\Models\AIConnection::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get(['id', 'name', 'provider', 'is_default']);

        return response()->json([
            'modes' => [
                ProseGenerationService::MODE_SCENE_BEAT => [
                    'name' => 'Scene Beat',
                    'description' => 'Write prose from a beat/outline',
                ],
                ProseGenerationService::MODE_CONTINUE => [
                    'name' => 'Continue Writing',
                    'description' => 'Continue the story naturally',
                ],
                ProseGenerationService::MODE_CUSTOM => [
                    'name' => 'Custom',
                    'description' => 'Use custom instructions',
                ],
            ],
            'prompts' => $prompts,
            'connections' => $connections,
        ]);
    }
}
