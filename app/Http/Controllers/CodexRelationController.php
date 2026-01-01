<?php

namespace App\Http\Controllers;

use App\Models\CodexEntry;
use App\Models\CodexRelation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CodexRelationController extends Controller
{
    /**
     * List all relations for an entry.
     */
    public function index(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $outgoing = $entry->outgoingRelations()->with('targetEntry')->get();
        $incoming = $entry->incomingRelations()->with('sourceEntry')->get();

        return response()->json([
            'outgoing' => $outgoing->map(fn (CodexRelation $rel) => [
                'id' => $rel->id,
                'relation_type' => $rel->relation_type,
                'label' => $rel->label,
                'is_bidirectional' => $rel->is_bidirectional,
                'target' => [
                    'id' => $rel->targetEntry->id,
                    'name' => $rel->targetEntry->name,
                    'type' => $rel->targetEntry->type,
                ],
            ]),
            'incoming' => $incoming->map(fn (CodexRelation $rel) => [
                'id' => $rel->id,
                'relation_type' => $rel->relation_type,
                'label' => $rel->label,
                'is_bidirectional' => $rel->is_bidirectional,
                'source' => [
                    'id' => $rel->sourceEntry->id,
                    'name' => $rel->sourceEntry->name,
                    'type' => $rel->sourceEntry->type,
                ],
            ]),
        ]);
    }

    /**
     * Add a new relation from an entry.
     */
    public function store(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'target_entry_id' => ['required', 'integer', 'exists:codex_entries,id'],
            'relation_type' => ['required', 'string', 'max:100'],
            'label' => ['nullable', 'string', 'max:255'],
            'is_bidirectional' => ['sometimes', 'boolean'],
        ]);

        // Ensure target entry belongs to same novel
        $targetEntry = CodexEntry::findOrFail($validated['target_entry_id']);
        if ($targetEntry->novel_id !== $entry->novel_id) {
            return response()->json([
                'message' => 'Target entry must belong to the same novel',
            ], 422);
        }

        // Prevent self-relation
        if ($targetEntry->id === $entry->id) {
            return response()->json([
                'message' => 'Cannot create a relation to itself',
            ], 422);
        }

        // Check for duplicate relation
        $exists = CodexRelation::where('source_entry_id', $entry->id)
            ->where('target_entry_id', $validated['target_entry_id'])
            ->where('relation_type', $validated['relation_type'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This relation already exists',
            ], 422);
        }

        $relation = $entry->outgoingRelations()->create([
            'target_entry_id' => $validated['target_entry_id'],
            'relation_type' => $validated['relation_type'],
            'label' => $validated['label'] ?? null,
            'is_bidirectional' => $validated['is_bidirectional'] ?? false,
        ]);

        $relation->load('targetEntry');

        return response()->json([
            'relation' => [
                'id' => $relation->id,
                'relation_type' => $relation->relation_type,
                'label' => $relation->label,
                'is_bidirectional' => $relation->is_bidirectional,
                'target' => [
                    'id' => $relation->targetEntry->id,
                    'name' => $relation->targetEntry->name,
                    'type' => $relation->targetEntry->type,
                ],
            ],
        ], 201);
    }

    /**
     * Update a relation.
     */
    public function update(Request $request, CodexRelation $relation): JsonResponse
    {
        if ($relation->sourceEntry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'relation_type' => ['sometimes', 'string', 'max:100'],
            'label' => ['nullable', 'string', 'max:255'],
            'is_bidirectional' => ['sometimes', 'boolean'],
        ]);

        $relation->update($validated);

        return response()->json([
            'relation' => [
                'id' => $relation->id,
                'relation_type' => $relation->relation_type,
                'label' => $relation->label,
                'is_bidirectional' => $relation->is_bidirectional,
            ],
        ]);
    }

    /**
     * Remove a relation.
     */
    public function destroy(Request $request, CodexRelation $relation): JsonResponse
    {
        if ($relation->sourceEntry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $relation->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get available relation types.
     */
    public function types(): JsonResponse
    {
        return response()->json([
            'character' => CodexRelation::getCharacterRelationTypes(),
            'location' => CodexRelation::getLocationRelationTypes(),
            'item' => CodexRelation::getItemRelationTypes(),
        ]);
    }
}
