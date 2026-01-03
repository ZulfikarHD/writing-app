<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use App\Models\PromptInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromptInputController extends Controller
{
    /**
     * List all inputs for a prompt.
     */
    public function index(Request $request, Prompt $prompt): JsonResponse
    {
        $user = $request->user();

        if (! $prompt->is_system && $prompt->user_id !== $user->id) {
            abort(403, 'You do not have access to this prompt.');
        }

        $inputs = $prompt->inputs()->orderBy('sort_order')->get();

        return response()->json([
            'inputs' => $inputs->map(fn ($input) => $this->formatInput($input)),
        ]);
    }

    /**
     * Store a new input for a prompt.
     */
    public function store(Request $request, Prompt $prompt): JsonResponse
    {
        $user = $request->user();

        if (! $prompt->canBeEditedBy($user)) {
            return response()->json([
                'error' => 'You cannot edit this prompt.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z_][a-z0-9_]*$/i'],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:text,textarea,select,number,checkbox'],
            'options' => ['nullable', 'array'],
            'options.*.value' => ['required_with:options', 'string'],
            'options.*.label' => ['required_with:options', 'string'],
            'default_value' => ['nullable', 'string'],
            'placeholder' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_required' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $input = $prompt->inputs()->create($validated);

        return response()->json([
            'input' => $this->formatInput($input),
            'message' => 'Input created successfully.',
        ], 201);
    }

    /**
     * Update an existing input.
     */
    public function update(Request $request, Prompt $prompt, PromptInput $input): JsonResponse
    {
        $user = $request->user();

        if (! $prompt->canBeEditedBy($user)) {
            return response()->json([
                'error' => 'You cannot edit this prompt.',
            ], 403);
        }

        if ($input->prompt_id !== $prompt->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100', 'regex:/^[a-z_][a-z0-9_]*$/i'],
            'label' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:text,textarea,select,number,checkbox'],
            'options' => ['nullable', 'array'],
            'options.*.value' => ['required_with:options', 'string'],
            'options.*.label' => ['required_with:options', 'string'],
            'default_value' => ['nullable', 'string'],
            'placeholder' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_required' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $input->update($validated);

        return response()->json([
            'input' => $this->formatInput($input),
            'message' => 'Input updated successfully.',
        ]);
    }

    /**
     * Delete an input.
     */
    public function destroy(Request $request, Prompt $prompt, PromptInput $input): JsonResponse
    {
        $user = $request->user();

        if (! $prompt->canBeEditedBy($user)) {
            return response()->json([
                'error' => 'You cannot edit this prompt.',
            ], 403);
        }

        if ($input->prompt_id !== $prompt->id) {
            abort(404);
        }

        $input->delete();

        return response()->json([
            'success' => true,
            'message' => 'Input deleted successfully.',
        ]);
    }

    /**
     * Bulk update inputs (for reordering).
     */
    public function bulkUpdate(Request $request, Prompt $prompt): JsonResponse
    {
        $user = $request->user();

        if (! $prompt->canBeEditedBy($user)) {
            return response()->json([
                'error' => 'You cannot edit this prompt.',
            ], 403);
        }

        $validated = $request->validate([
            'inputs' => ['required', 'array'],
            'inputs.*.id' => ['required', 'integer', 'exists:prompt_inputs,id'],
            'inputs.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['inputs'] as $inputData) {
            PromptInput::where('id', $inputData['id'])
                ->where('prompt_id', $prompt->id)
                ->update(['sort_order' => $inputData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Inputs reordered successfully.',
        ]);
    }

    /**
     * Format input for JSON response.
     *
     * @return array<string, mixed>
     */
    protected function formatInput(PromptInput $input): array
    {
        return [
            'id' => $input->id,
            'prompt_id' => $input->prompt_id,
            'name' => $input->name,
            'label' => $input->label,
            'type' => $input->type,
            'options' => $input->options,
            'default_value' => $input->default_value,
            'placeholder' => $input->placeholder,
            'description' => $input->description,
            'is_required' => $input->is_required,
            'sort_order' => $input->sort_order,
            'created_at' => $input->created_at?->toISOString(),
            'updated_at' => $input->updated_at?->toISOString(),
        ];
    }
}
