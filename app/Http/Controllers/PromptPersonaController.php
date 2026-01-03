<?php

namespace App\Http\Controllers;

use App\Models\PromptPersona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromptPersonaController extends Controller
{
    /**
     * List all personas for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $includeArchived = $request->boolean('include_archived', false);

        $query = PromptPersona::where('user_id', $user->id)
            ->orderBy('name');

        if (! $includeArchived) {
            $query->active();
        }

        $personas = $query->get();

        return response()->json([
            'personas' => $personas->map(fn (PromptPersona $p) => $this->formatPersona($p)),
        ]);
    }

    /**
     * Get personas applicable for a specific interaction type and project.
     */
    public function forContext(Request $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->query('type');
        $projectId = $request->query('project_id');

        $query = PromptPersona::where('user_id', $user->id)
            ->active();

        if ($type) {
            $query->forInteractionType($type);
        }

        if ($projectId) {
            $query->forProject((int) $projectId);
        }

        $personas = $query->orderBy('name')->get();

        return response()->json([
            'personas' => $personas->map(fn (PromptPersona $p) => $this->formatPersona($p)),
        ]);
    }

    /**
     * Store a newly created persona.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'system_message' => ['required', 'string'],
            'interaction_types' => ['nullable', 'array'],
            'interaction_types.*' => ['string', Rule::in(PromptPersona::getInteractionTypes())],
            'project_ids' => ['nullable', 'array'],
            'project_ids.*' => ['integer'],
            'is_default' => ['boolean'],
        ]);

        $persona = PromptPersona::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'system_message' => $validated['system_message'],
            'interaction_types' => $validated['interaction_types'] ?? null,
            'project_ids' => $validated['project_ids'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return response()->json([
            'persona' => $this->formatPersona($persona),
            'message' => 'Persona created successfully.',
        ], 201);
    }

    /**
     * Display the specified persona.
     */
    public function show(Request $request, PromptPersona $promptPersona): JsonResponse
    {
        $user = $request->user();

        if ($promptPersona->user_id !== $user->id) {
            abort(403, 'You do not have access to this persona.');
        }

        return response()->json([
            'persona' => $this->formatPersona($promptPersona),
        ]);
    }

    /**
     * Update the specified persona.
     */
    public function update(Request $request, PromptPersona $promptPersona): JsonResponse
    {
        $user = $request->user();

        if ($promptPersona->user_id !== $user->id) {
            abort(403, 'You do not have access to this persona.');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'system_message' => ['sometimes', 'required', 'string'],
            'interaction_types' => ['nullable', 'array'],
            'interaction_types.*' => ['string', Rule::in(PromptPersona::getInteractionTypes())],
            'project_ids' => ['nullable', 'array'],
            'project_ids.*' => ['integer'],
            'is_default' => ['boolean'],
            'is_archived' => ['boolean'],
        ]);

        $promptPersona->update($validated);

        return response()->json([
            'persona' => $this->formatPersona($promptPersona->fresh()),
            'message' => 'Persona updated successfully.',
        ]);
    }

    /**
     * Archive the specified persona.
     */
    public function archive(Request $request, PromptPersona $promptPersona): JsonResponse
    {
        $user = $request->user();

        if ($promptPersona->user_id !== $user->id) {
            abort(403, 'You do not have access to this persona.');
        }

        $promptPersona->update(['is_archived' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Persona archived successfully.',
        ]);
    }

    /**
     * Restore an archived persona.
     */
    public function restore(Request $request, PromptPersona $promptPersona): JsonResponse
    {
        $user = $request->user();

        if ($promptPersona->user_id !== $user->id) {
            abort(403, 'You do not have access to this persona.');
        }

        $promptPersona->update(['is_archived' => false]);

        return response()->json([
            'persona' => $this->formatPersona($promptPersona->fresh()),
            'message' => 'Persona restored successfully.',
        ]);
    }

    /**
     * Remove the specified persona.
     */
    public function destroy(Request $request, PromptPersona $promptPersona): JsonResponse
    {
        $user = $request->user();

        if ($promptPersona->user_id !== $user->id) {
            abort(403, 'You do not have access to this persona.');
        }

        $promptPersona->delete();

        return response()->json([
            'success' => true,
            'message' => 'Persona deleted successfully.',
        ]);
    }

    /**
     * Get interaction types with labels.
     */
    public function interactionTypes(): JsonResponse
    {
        return response()->json([
            'types' => PromptPersona::getInteractionTypeLabels(),
        ]);
    }

    /**
     * Format a persona for JSON response.
     *
     * @return array<string, mixed>
     */
    protected function formatPersona(PromptPersona $persona): array
    {
        return [
            'id' => $persona->id,
            'user_id' => $persona->user_id,
            'name' => $persona->name,
            'description' => $persona->description,
            'system_message' => $persona->system_message,
            'interaction_types' => $persona->interaction_types,
            'project_ids' => $persona->project_ids,
            'is_default' => $persona->is_default,
            'is_archived' => $persona->is_archived,
            'created_at' => $persona->created_at?->toISOString(),
            'updated_at' => $persona->updated_at?->toISOString(),
        ];
    }
}
