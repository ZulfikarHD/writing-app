<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use App\Services\Prompts\PromptSharingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PromptSharingController extends Controller
{
    public function __construct(
        protected PromptSharingService $sharingService
    ) {}

    /**
     * Export a prompt to a shareable string.
     */
    public function export(Request $request, Prompt $prompt): JsonResponse
    {
        $user = $request->user();

        // Check if user can access this prompt
        if (! $prompt->is_system && $prompt->user_id !== $user->id) {
            abort(403, 'You do not have access to this prompt.');
        }

        $encoded = $this->sharingService->exportPrompt($prompt);

        return response()->json([
            'encoded' => $encoded,
            'message' => 'Prompt exported successfully.',
        ]);
    }

    /**
     * Preview an imported prompt without creating it.
     */
    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'encoded' => ['required', 'string'],
        ]);

        try {
            $preview = $this->sharingService->previewImport($request->input('encoded'));

            return response()->json([
                'preview' => $preview,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Import a prompt from an encoded string.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'encoded' => ['required', 'string'],
        ]);

        try {
            $prompt = $this->sharingService->importPrompt(
                $request->input('encoded'),
                $request->user()
            );

            return response()->json([
                'prompt' => $this->formatPrompt($prompt),
                'message' => 'Prompt imported successfully.',
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Invalid prompt data.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Format prompt for response.
     *
     * @return array<string, mixed>
     */
    protected function formatPrompt(Prompt $prompt): array
    {
        return [
            'id' => $prompt->id,
            'user_id' => $prompt->user_id,
            'folder_id' => $prompt->folder_id,
            'name' => $prompt->name,
            'description' => $prompt->description,
            'type' => $prompt->type,
            'type_label' => Prompt::getTypeLabels()[$prompt->type] ?? $prompt->type,
            'system_message' => $prompt->system_message,
            'user_message' => $prompt->user_message,
            'messages' => $prompt->messages,
            'model_settings' => $prompt->model_settings,
            'is_system' => $prompt->is_system,
            'is_active' => $prompt->is_active,
            'sort_order' => $prompt->sort_order,
            'usage_count' => $prompt->usage_count,
            'inputs' => $prompt->relationLoaded('inputs')
                ? $prompt->inputs->map(fn ($input) => [
                    'id' => $input->id,
                    'name' => $input->name,
                    'label' => $input->label,
                    'type' => $input->type,
                    'options' => $input->options,
                    'default_value' => $input->default_value,
                    'placeholder' => $input->placeholder,
                    'description' => $input->description,
                    'is_required' => $input->is_required,
                    'sort_order' => $input->sort_order,
                ])
                : [],
            'created_at' => $prompt->created_at?->toISOString(),
            'updated_at' => $prompt->updated_at?->toISOString(),
        ];
    }
}
