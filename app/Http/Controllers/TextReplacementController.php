<?php

namespace App\Http\Controllers;

use App\Services\Editor\TextReplacementService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TextReplacementController extends Controller
{
    public function __construct(
        protected TextReplacementService $replacementService
    ) {}

    /**
     * Transform selected text with streaming response.
     */
    public function replace(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'selected_text' => ['required', 'string', 'min:1', 'max:50000'],
            'type' => ['required', 'string', 'in:expand,rephrase,shorten,custom'],
            'scene_id' => ['sometimes', 'nullable', 'integer', 'exists:scenes,id'],
            'prompt_id' => ['sometimes', 'nullable', 'integer', 'exists:prompts,id'],
            'connection_id' => ['sometimes', 'nullable', 'integer', 'exists:ai_connections,id'],
            'model' => ['sometimes', 'nullable', 'string', 'max:100'],

            // Expand options
            'expand_amount' => ['sometimes', 'nullable', 'string', 'in:slightly,double,triple'],
            'expand_method' => ['sometimes', 'nullable', 'string', 'in:sensory_details,inner_thoughts,description,dialogue'],

            // Rephrase options
            'rephrase_options' => ['sometimes', 'nullable', 'array'],
            'rephrase_options.*' => ['string', 'in:add_inner_thoughts,convert_to_dialogue,passive_to_active,different_words,show_dont_tell,change_pov,change_tense'],
            'target_pov' => ['sometimes', 'nullable', 'string', 'max:50'],
            'target_tense' => ['sometimes', 'nullable', 'string', 'max:50'],

            // Shorten options
            'shorten_amount' => ['sometimes', 'nullable', 'string', 'in:half,quarter,single_paragraph'],

            // Custom options
            'instructions' => ['sometimes', 'nullable', 'string', 'max:2000'],

            // Model settings
            'temperature' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['sometimes', 'nullable', 'integer', 'min:100', 'max:8000'],
        ]);

        return response()->stream(function () use ($validated) {
            $user = auth()->user();

            foreach ($this->replacementService->transform($validated['selected_text'], $user, $validated) as $chunk) {
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
     * Get available replacement options and prompts.
     */
    public function options(Request $request)
    {
        $user = auth()->user();

        // Get replacement prompts accessible by user
        $prompts = \App\Models\Prompt::query()
            ->accessibleBy($user->id)
            ->active()
            ->ofType(\App\Models\Prompt::TYPE_REPLACEMENT)
            ->orderBy('is_system', 'desc')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'description', 'is_system']);

        // Get active AI connections
        $connections = \App\Models\AIConnection::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get(['id', 'name', 'provider', 'is_default']);

        return response()->json([
            'types' => TextReplacementService::getTransformationTypes(),
            'expand_amounts' => TextReplacementService::getExpandAmounts(),
            'shorten_amounts' => TextReplacementService::getShortenAmounts(),
            'rephrase_options' => TextReplacementService::REPHRASE_OPTIONS,
            'prompts' => $prompts,
            'connections' => $connections,
        ]);
    }
}
