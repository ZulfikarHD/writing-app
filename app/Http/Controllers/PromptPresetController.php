<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use App\Models\PromptPreset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromptPresetController extends Controller
{
    /**
     * List all presets for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $presets = PromptPreset::where('user_id', $user->id)
            ->with('prompt:id,name,type')
            ->orderBy('name')
            ->get();

        return response()->json([
            'presets' => $presets->map(fn (PromptPreset $p) => $this->formatPreset($p)),
        ]);
    }

    /**
     * List presets for a specific prompt.
     */
    public function forPrompt(Request $request, Prompt $prompt): JsonResponse
    {
        $user = $request->user();

        // Check if user can access this prompt
        if (! $prompt->is_system && $prompt->user_id !== $user->id) {
            abort(403, 'You do not have access to this prompt.');
        }

        $presets = PromptPreset::where('user_id', $user->id)
            ->where('prompt_id', $prompt->id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'presets' => $presets->map(fn (PromptPreset $p) => $this->formatPreset($p)),
        ]);
    }

    /**
     * Store a newly created preset for a prompt.
     */
    public function store(Request $request, Prompt $prompt): JsonResponse
    {
        $user = $request->user();

        // Check if user can access this prompt
        if (! $prompt->is_system && $prompt->user_id !== $user->id) {
            abort(403, 'You do not have access to this prompt.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['nullable', 'integer', 'min:1'],
            'top_p' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'frequency_penalty' => ['nullable', 'numeric', 'min:-2', 'max:2'],
            'presence_penalty' => ['nullable', 'numeric', 'min:-2', 'max:2'],
            'stop_sequences' => ['nullable', 'array'],
            'stop_sequences.*' => ['string'],
            'input_values' => ['nullable', 'array'],
            'is_default' => ['boolean'],
        ]);

        $preset = PromptPreset::create([
            'user_id' => $user->id,
            'prompt_id' => $prompt->id,
            'name' => $validated['name'],
            'model' => $validated['model'] ?? null,
            'temperature' => $validated['temperature'] ?? 0.7,
            'max_tokens' => $validated['max_tokens'] ?? null,
            'top_p' => $validated['top_p'] ?? null,
            'frequency_penalty' => $validated['frequency_penalty'] ?? null,
            'presence_penalty' => $validated['presence_penalty'] ?? null,
            'stop_sequences' => $validated['stop_sequences'] ?? null,
            'input_values' => $validated['input_values'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        // If this is set as default, unset other defaults
        if ($preset->is_default) {
            $preset->setAsDefault();
        }

        return response()->json([
            'preset' => $this->formatPreset($preset->load('prompt:id,name,type')),
            'message' => 'Preset created successfully.',
        ], 201);
    }

    /**
     * Display the specified preset.
     */
    public function show(Request $request, PromptPreset $promptPreset): JsonResponse
    {
        $user = $request->user();

        if ($promptPreset->user_id !== $user->id) {
            abort(403, 'You do not have access to this preset.');
        }

        $promptPreset->load('prompt:id,name,type');

        return response()->json([
            'preset' => $this->formatPreset($promptPreset),
        ]);
    }

    /**
     * Update the specified preset.
     */
    public function update(Request $request, PromptPreset $promptPreset): JsonResponse
    {
        $user = $request->user();

        if ($promptPreset->user_id !== $user->id) {
            abort(403, 'You do not have access to this preset.');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['nullable', 'integer', 'min:1'],
            'top_p' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'frequency_penalty' => ['nullable', 'numeric', 'min:-2', 'max:2'],
            'presence_penalty' => ['nullable', 'numeric', 'min:-2', 'max:2'],
            'stop_sequences' => ['nullable', 'array'],
            'stop_sequences.*' => ['string'],
            'input_values' => ['nullable', 'array'],
            'is_default' => ['boolean'],
        ]);

        $promptPreset->update($validated);

        // If this is set as default, unset other defaults
        if ($promptPreset->is_default) {
            $promptPreset->setAsDefault();
        }

        return response()->json([
            'preset' => $this->formatPreset($promptPreset->fresh()->load('prompt:id,name,type')),
            'message' => 'Preset updated successfully.',
        ]);
    }

    /**
     * Set the specified preset as default for its prompt.
     */
    public function setDefault(Request $request, PromptPreset $promptPreset): JsonResponse
    {
        $user = $request->user();

        if ($promptPreset->user_id !== $user->id) {
            abort(403, 'You do not have access to this preset.');
        }

        $promptPreset->setAsDefault();

        return response()->json([
            'preset' => $this->formatPreset($promptPreset->fresh()->load('prompt:id,name,type')),
            'message' => 'Preset set as default successfully.',
        ]);
    }

    /**
     * Remove the specified preset.
     */
    public function destroy(Request $request, PromptPreset $promptPreset): JsonResponse
    {
        $user = $request->user();

        if ($promptPreset->user_id !== $user->id) {
            abort(403, 'You do not have access to this preset.');
        }

        $promptPreset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Preset deleted successfully.',
        ]);
    }

    /**
     * Format a preset for JSON response.
     *
     * @return array<string, mixed>
     */
    protected function formatPreset(PromptPreset $preset): array
    {
        return [
            'id' => $preset->id,
            'user_id' => $preset->user_id,
            'prompt_id' => $preset->prompt_id,
            'name' => $preset->name,
            'model' => $preset->model,
            'temperature' => $preset->temperature,
            'max_tokens' => $preset->max_tokens,
            'top_p' => $preset->top_p,
            'frequency_penalty' => $preset->frequency_penalty,
            'presence_penalty' => $preset->presence_penalty,
            'stop_sequences' => $preset->stop_sequences,
            'input_values' => $preset->input_values,
            'is_default' => $preset->is_default,
            'prompt' => $preset->relationLoaded('prompt') && $preset->prompt ? [
                'id' => $preset->prompt->id,
                'name' => $preset->prompt->name,
                'type' => $preset->prompt->type,
            ] : null,
            'created_at' => $preset->created_at?->toISOString(),
            'updated_at' => $preset->updated_at?->toISOString(),
        ];
    }
}
