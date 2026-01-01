<?php

namespace App\Http\Controllers;

use App\Models\CodexEntry;
use App\Models\CodexTag;
use App\Models\Novel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CodexTagController - Sprint 14 (US-12.4, F-12.3.2, F-12.3.3, F-12.3.4)
 *
 * Handles tag management for the Codex system.
 * Tags are organizational labels that are NOT sent to AI.
 *
 * @see https://www.novelcrafter.com/help/docs/codex/codex-categories
 */
class CodexTagController extends Controller
{
    /**
     * List all tags for a novel.
     */
    public function index(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $query = CodexTag::where('novel_id', $novel->id);

        // Filter by entry type if specified
        if ($request->filled('entry_type')) {
            $query->forType($request->entry_type);
        }

        $tags = $query->orderBy('name')->get();

        return response()->json([
            'tags' => $tags->map(fn (CodexTag $tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'color' => $tag->color,
                'entry_type' => $tag->entry_type,
                'is_predefined' => $tag->is_predefined,
                'entries_count' => $tag->entries()->count(),
            ]),
        ]);
    }

    /**
     * Create a new tag for a novel.
     */
    public function store(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'entry_type' => 'nullable|string|in:'.implode(',', CodexEntry::getTypes()),
        ]);

        // Check for duplicate name in this novel
        $exists = CodexTag::where('novel_id', $novel->id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'A tag with this name already exists.',
            ], 422);
        }

        $tag = CodexTag::create([
            'novel_id' => $novel->id,
            'name' => $validated['name'],
            'color' => $validated['color'] ?? null,
            'entry_type' => $validated['entry_type'] ?? null,
            'is_predefined' => false,
        ]);

        return response()->json([
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
                'color' => $tag->color,
                'entry_type' => $tag->entry_type,
                'is_predefined' => $tag->is_predefined,
                'entries_count' => 0,
            ],
        ], 201);
    }

    /**
     * Update an existing tag.
     */
    public function update(Request $request, CodexTag $tag): JsonResponse
    {
        if ($tag->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Prevent editing predefined tags
        if ($tag->is_predefined) {
            return response()->json([
                'message' => 'Predefined tags cannot be modified.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:50',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'entry_type' => 'nullable|string|in:'.implode(',', CodexEntry::getTypes()),
        ]);

        // Check for duplicate name if name is being changed
        if (isset($validated['name']) && $validated['name'] !== $tag->name) {
            $exists = CodexTag::where('novel_id', $tag->novel_id)
                ->where('name', $validated['name'])
                ->where('id', '!=', $tag->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'A tag with this name already exists.',
                ], 422);
            }
        }

        $tag->update($validated);

        return response()->json([
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
                'color' => $tag->color,
                'entry_type' => $tag->entry_type,
                'is_predefined' => $tag->is_predefined,
                'entries_count' => $tag->entries()->count(),
            ],
        ]);
    }

    /**
     * Delete a tag.
     */
    public function destroy(Request $request, CodexTag $tag): JsonResponse
    {
        if ($tag->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Prevent deleting predefined tags
        if ($tag->is_predefined) {
            return response()->json([
                'message' => 'Predefined tags cannot be deleted.',
            ], 422);
        }

        $tag->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Assign a tag to a codex entry.
     */
    public function assignToEntry(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'tag_id' => 'required|exists:codex_tags,id',
        ]);

        $tag = CodexTag::find($validated['tag_id']);

        // Ensure tag belongs to the same novel
        if ($tag->novel_id !== $entry->novel_id) {
            return response()->json([
                'message' => 'Tag does not belong to this novel.',
            ], 422);
        }

        // Check if entry type matches tag's entry_type filter
        if ($tag->entry_type !== null && $tag->entry_type !== $entry->type) {
            return response()->json([
                'message' => "This tag is only available for {$tag->entry_type} entries.",
            ], 422);
        }

        // Attach (ignores if already attached)
        $entry->tags()->syncWithoutDetaching([$tag->id]);

        return response()->json([
            'success' => true,
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
                'color' => $tag->color,
            ],
        ]);
    }

    /**
     * Remove a tag from a codex entry.
     */
    public function removeFromEntry(Request $request, CodexEntry $entry, CodexTag $tag): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $entry->tags()->detach($tag->id);

        return response()->json(['success' => true]);
    }

    /**
     * Initialize predefined tags for a novel.
     * Called when first accessing tags for a novel.
     */
    public function initializePredefined(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Check if predefined tags already exist
        $hasPredefined = CodexTag::where('novel_id', $novel->id)
            ->where('is_predefined', true)
            ->exists();

        if ($hasPredefined) {
            return response()->json([
                'message' => 'Predefined tags already initialized.',
                'initialized' => false,
            ]);
        }

        // Create predefined tags from constants
        $created = [];
        foreach (CodexTag::PREDEFINED_TAGS as $entryType => $tags) {
            foreach ($tags as $tagData) {
                $tag = CodexTag::create([
                    'novel_id' => $novel->id,
                    'name' => $tagData['name'],
                    'color' => $tagData['color'],
                    'entry_type' => $entryType,
                    'is_predefined' => true,
                ]);
                $created[] = $tag;
            }
        }

        return response()->json([
            'message' => 'Predefined tags initialized successfully.',
            'initialized' => true,
            'count' => count($created),
        ]);
    }
}
