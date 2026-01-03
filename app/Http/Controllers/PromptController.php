<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePromptRequest;
use App\Http\Requests\UpdatePromptRequest;
use App\Models\Prompt;
use App\Services\Prompts\PromptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PromptController extends Controller
{
    public function __construct(
        protected PromptService $promptService
    ) {}

    /**
     * Display the prompt library page.
     */
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();
        $type = $request->query('type');
        $search = $request->query('search');

        $prompts = $this->promptService->getAccessiblePrompts($user, $type, $search);
        $types = $this->promptService->getPromptTypes();
        $statistics = $this->promptService->getStatistics($user);

        return Inertia::render('Prompts/Index', [
            'prompts' => $prompts->map(fn (Prompt $p) => $this->formatPrompt($p)),
            'types' => $types,
            'statistics' => $statistics,
            'filters' => [
                'type' => $type,
                'search' => $search,
            ],
        ]);
    }

    /**
     * List all prompts as JSON (API).
     */
    public function list(Request $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->query('type');
        $search = $request->query('search');

        $prompts = $this->promptService->getAccessiblePrompts($user, $type, $search);

        return response()->json([
            'prompts' => $prompts->map(fn (Prompt $p) => $this->formatPrompt($p)),
        ]);
    }

    /**
     * Get prompts by type (for prompt selector dropdowns).
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        $user = $request->user();

        if (! in_array($type, Prompt::getTypes())) {
            return response()->json(['error' => 'Invalid prompt type.'], 400);
        }

        $prompts = $this->promptService->getPromptsByType($user, $type);

        return response()->json([
            'prompts' => $prompts->map(fn (Prompt $p) => $this->formatPrompt($p)),
        ]);
    }

    /**
     * Store a newly created prompt.
     */
    public function store(StorePromptRequest $request): JsonResponse
    {
        $prompt = $this->promptService->createPrompt(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'prompt' => $this->formatPrompt($prompt),
            'message' => 'Prompt created successfully.',
        ], 201);
    }

    /**
     * Display the specified prompt.
     */
    public function show(Request $request, Prompt $prompt): JsonResponse
    {
        $user = $request->user();

        // Check if user can access this prompt (system or owned by user)
        if (! $prompt->is_system && $prompt->user_id !== $user->id) {
            abort(403, 'You do not have access to this prompt.');
        }

        return response()->json([
            'prompt' => $this->formatPrompt($prompt),
        ]);
    }

    /**
     * Update the specified prompt.
     */
    public function update(UpdatePromptRequest $request, Prompt $prompt): JsonResponse
    {
        $updatedPrompt = $this->promptService->updatePrompt(
            $prompt,
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'prompt' => $this->formatPrompt($updatedPrompt),
            'message' => 'Prompt updated successfully.',
        ]);
    }

    /**
     * Remove the specified prompt.
     */
    public function destroy(Request $request, Prompt $prompt): JsonResponse
    {
        $user = $request->user();

        if (! $prompt->canBeDeletedBy($user)) {
            return response()->json([
                'error' => 'You cannot delete this prompt.',
            ], 403);
        }

        $this->promptService->deletePrompt($prompt, $user);

        return response()->json([
            'success' => true,
            'message' => 'Prompt deleted successfully.',
        ]);
    }

    /**
     * Clone an existing prompt.
     */
    public function clone(Request $request, Prompt $prompt): JsonResponse
    {
        $user = $request->user();

        // Check if user can access this prompt
        if (! $prompt->is_system && $prompt->user_id !== $user->id) {
            abort(403, 'You do not have access to this prompt.');
        }

        $newName = $request->input('name');
        $clonedPrompt = $this->promptService->clonePrompt($prompt, $user, $newName);

        return response()->json([
            'prompt' => $this->formatPrompt($clonedPrompt),
            'message' => 'Prompt cloned successfully.',
        ], 201);
    }

    /**
     * Record prompt usage.
     */
    public function recordUsage(Request $request, Prompt $prompt): JsonResponse
    {
        $user = $request->user();

        // Check if user can access this prompt
        if (! $prompt->is_system && $prompt->user_id !== $user->id) {
            abort(403, 'You do not have access to this prompt.');
        }

        $this->promptService->recordUsage($prompt);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Reorder prompts.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer'],
        ]);

        $this->promptService->reorderPrompts(
            $request->user(),
            $request->input('order')
        );

        return response()->json([
            'success' => true,
            'message' => 'Prompts reordered successfully.',
        ]);
    }

    /**
     * Get prompt types with labels.
     */
    public function types(): JsonResponse
    {
        return response()->json([
            'types' => $this->promptService->getPromptTypes(),
        ]);
    }

    /**
     * Format a prompt for JSON response.
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
