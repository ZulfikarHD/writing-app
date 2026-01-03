<?php

namespace App\Http\Controllers;

use App\Models\PromptComponent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromptComponentController extends Controller
{
    /**
     * List all components accessible by the user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $components = PromptComponent::accessibleBy($user->id)
            ->orderBy('is_system', 'desc')
            ->orderBy('label')
            ->get();

        return response()->json([
            'components' => $components->map(fn ($comp) => $this->formatComponent($comp)),
        ]);
    }

    /**
     * Store a new component.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z_][a-z0-9_]*$/i'],
            'label' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        // Check if name already exists for this user
        $exists = PromptComponent::where('user_id', $user->id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'A component with this name already exists.',
                'errors' => ['name' => ['A component with this name already exists.']],
            ], 422);
        }

        $component = PromptComponent::create([
            'user_id' => $user->id,
            ...$validated,
        ]);

        return response()->json([
            'component' => $this->formatComponent($component),
            'message' => 'Component created successfully.',
        ], 201);
    }

    /**
     * Display the specified component.
     */
    public function show(Request $request, PromptComponent $component): JsonResponse
    {
        $user = $request->user();

        if (! $component->is_system && $component->user_id !== $user->id) {
            abort(403, 'You do not have access to this component.');
        }

        return response()->json([
            'component' => $this->formatComponent($component),
        ]);
    }

    /**
     * Update the specified component.
     */
    public function update(Request $request, PromptComponent $component): JsonResponse
    {
        $user = $request->user();

        if (! $component->canBeEditedBy($user)) {
            return response()->json([
                'error' => 'You cannot edit this component.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100', 'regex:/^[a-z_][a-z0-9_]*$/i'],
            'label' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        // Check if name already exists for this user (if name is being changed)
        if (isset($validated['name']) && $validated['name'] !== $component->name) {
            $exists = PromptComponent::where('user_id', $user->id)
                ->where('name', $validated['name'])
                ->where('id', '!=', $component->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'error' => 'A component with this name already exists.',
                    'errors' => ['name' => ['A component with this name already exists.']],
                ], 422);
            }
        }

        $component->update($validated);

        return response()->json([
            'component' => $this->formatComponent($component),
            'message' => 'Component updated successfully.',
        ]);
    }

    /**
     * Remove the specified component.
     */
    public function destroy(Request $request, PromptComponent $component): JsonResponse
    {
        $user = $request->user();

        if (! $component->canBeDeletedBy($user)) {
            return response()->json([
                'error' => 'You cannot delete this component.',
            ], 403);
        }

        $component->delete();

        return response()->json([
            'success' => true,
            'message' => 'Component deleted successfully.',
        ]);
    }

    /**
     * Clone an existing component.
     */
    public function clone(Request $request, PromptComponent $component): JsonResponse
    {
        $user = $request->user();

        if (! $component->is_system && $component->user_id !== $user->id) {
            abort(403, 'You do not have access to this component.');
        }

        // Generate unique name
        $baseName = $component->name . '_copy';
        $name = $baseName;
        $counter = 1;

        while (PromptComponent::where('user_id', $user->id)->where('name', $name)->exists()) {
            $name = $baseName . '_' . $counter;
            $counter++;
        }

        $newComponent = PromptComponent::create([
            'user_id' => $user->id,
            'name' => $name,
            'label' => $component->label . ' (Copy)',
            'content' => $component->content,
            'description' => $component->description,
            'is_system' => false,
        ]);

        return response()->json([
            'component' => $this->formatComponent($newComponent),
            'message' => 'Component cloned successfully.',
        ], 201);
    }

    /**
     * Format component for JSON response.
     *
     * @return array<string, mixed>
     */
    protected function formatComponent(PromptComponent $component): array
    {
        return [
            'id' => $component->id,
            'user_id' => $component->user_id,
            'name' => $component->name,
            'label' => $component->label,
            'content' => $component->content,
            'description' => $component->description,
            'is_system' => $component->is_system,
            'created_at' => $component->created_at?->toISOString(),
            'updated_at' => $component->updated_at?->toISOString(),
        ];
    }
}
