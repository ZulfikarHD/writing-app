<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSceneContentRequest;
use App\Models\Chapter;
use App\Models\CodexEntry;
use App\Models\CodexProgression;
use App\Models\Scene;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SceneController extends Controller
{
    public function store(Request $request, Chapter $chapter): JsonResponse
    {
        // Ensure user owns this chapter's novel
        if ($chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        // Get max position if not provided
        if (! isset($validated['position'])) {
            $validated['position'] = $chapter->scenes()->max('position') + 1;
        }

        $scene = $chapter->scenes()->create([
            'title' => $validated['title'] ?? 'New Scene',
            'position' => $validated['position'],
            'content' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                    ],
                ],
            ],
        ]);

        return response()->json([
            'scene' => [
                'id' => $scene->id,
                'chapter_id' => $scene->chapter_id,
                'title' => $scene->title,
                'position' => $scene->position,
                'status' => $scene->status,
                'word_count' => $scene->word_count,
                'content' => $scene->content,
            ],
        ], 201);
    }

    public function show(Request $request, Scene $scene): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json([
            'scene' => [
                'id' => $scene->id,
                'chapter_id' => $scene->chapter_id,
                'title' => $scene->title,
                'content' => $scene->content,
                'summary' => $scene->summary,
                'position' => $scene->position,
                'status' => $scene->status,
                'word_count' => $scene->word_count,
                'subtitle' => $scene->subtitle,
                'notes' => $scene->notes,
                'pov_character_id' => $scene->pov_character_id,
                'exclude_from_ai' => $scene->exclude_from_ai,
            ],
        ]);
    }

    /**
     * Auto-save endpoint - updates scene content (typing = saving).
     */
    public function updateContent(UpdateSceneContentRequest $request, Scene $scene): JsonResponse
    {
        $content = $request->validated()['content'];

        // Calculate word count from content
        $wordCount = $scene->calculateWordCount();
        $scene->fill(['content' => $content]);
        $wordCount = $scene->calculateWordCount();

        $scene->update([
            'content' => $content,
            'word_count' => $wordCount,
        ]);

        // Update novel's last edited timestamp
        $scene->chapter->novel->update(['last_edited_at' => now()]);

        // Note: Mention scanning happens automatically via SceneObserver
        // when content changes - no queue needed, runs synchronously

        return response()->json([
            'success' => true,
            'word_count' => $wordCount,
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    public function update(Request $request, Scene $scene): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'summary' => ['sometimes', 'nullable', 'string'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'in:draft,in_progress,completed,needs_revision'],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'pov_character_id' => ['sometimes', 'nullable', 'integer'],
            'exclude_from_ai' => ['sometimes', 'boolean'],
            'chapter_id' => ['sometimes', 'integer', 'exists:chapters,id'],
        ]);

        // If moving to a different chapter, verify ownership
        if (isset($validated['chapter_id']) && $validated['chapter_id'] !== $scene->chapter_id) {
            $targetChapter = Chapter::find($validated['chapter_id']);
            if (! $targetChapter || $targetChapter->novel->user_id !== $request->user()->id) {
                abort(403, 'Cannot move scene to this chapter');
            }
        }

        $scene->update($validated);

        return response()->json([
            'scene' => [
                'id' => $scene->id,
                'chapter_id' => $scene->chapter_id,
                'title' => $scene->title,
                'position' => $scene->position,
                'status' => $scene->status,
            ],
        ]);
    }

    public function destroy(Request $request, Scene $scene): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $scene->delete();

        return response()->json(['success' => true]);
    }

    public function archive(Request $request, Scene $scene): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $scene->update(['archived_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, Scene $scene): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $scene->update(['archived_at' => null]);

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Chapter $chapter): JsonResponse
    {
        // Ensure user owns this chapter's novel
        if ($chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'scenes' => ['required', 'array'],
            'scenes.*.id' => ['required', 'integer', 'exists:scenes,id'],
            'scenes.*.position' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['scenes'] as $sceneData) {
            Scene::where('id', $sceneData['id'])
                ->where('chapter_id', $chapter->id)
                ->update(['position' => $sceneData['position']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get revision history for a scene.
     */
    public function revisions(Request $request, Scene $scene): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $revisions = $scene->revisions()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['id', 'word_count', 'created_at']);

        return response()->json([
            'revisions' => $revisions,
        ]);
    }

    /**
     * Create a manual revision snapshot.
     */
    public function createRevision(Request $request, Scene $scene): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $revision = $scene->createRevision();

        return response()->json([
            'revision' => [
                'id' => $revision->id,
                'word_count' => $revision->word_count,
                'created_at' => $revision->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Restore scene content from a revision.
     */
    public function restoreRevision(Request $request, Scene $scene, int $revisionId): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $revision = $scene->revisions()->findOrFail($revisionId);

        // Create a backup of current content before restoring
        $scene->createRevision();

        // Restore the content
        $scene->update([
            'content' => $revision->content,
            'word_count' => $revision->word_count,
        ]);

        return response()->json([
            'scene' => [
                'id' => $scene->id,
                'content' => $scene->content,
                'word_count' => $scene->word_count,
            ],
        ]);
    }

    /**
     * Duplicate a scene.
     */
    public function duplicate(Request $request, Scene $scene): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Get the next position
        $nextPosition = $scene->chapter->scenes()->max('position') + 1;

        // Create the duplicate
        $duplicate = $scene->replicate(['archived_at']);
        $duplicate->title = ($scene->title ?? 'Untitled').' (Copy)';
        $duplicate->position = $nextPosition;
        $duplicate->save();

        // Copy labels
        $duplicate->labels()->attach($scene->labels->pluck('id'));

        return response()->json([
            'scene' => [
                'id' => $duplicate->id,
                'chapter_id' => $duplicate->chapter_id,
                'title' => $duplicate->title,
                'position' => $duplicate->position,
                'status' => $duplicate->status,
                'word_count' => $duplicate->word_count,
            ],
        ], 201);
    }

    /**
     * Get subplots assigned to a scene via progressions.
     */
    public function subplots(Request $request, Scene $scene): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Get subplot progressions for this scene
        $subplotProgressions = CodexProgression::where('scene_id', $scene->id)
            ->whereHas('entry', fn ($q) => $q->where('type', CodexEntry::TYPE_SUBPLOT))
            ->with('entry')
            ->get();

        return response()->json([
            'subplots' => $subplotProgressions->map(fn ($prog) => [
                'progression_id' => $prog->id,
                'id' => $prog->entry->id,
                'name' => $prog->entry->name,
                'note' => $prog->note,
            ]),
        ]);
    }

    /**
     * Assign a subplot to a scene by creating a progression.
     */
    public function assignSubplot(Request $request, Scene $scene): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'codex_entry_id' => 'required|integer|exists:codex_entries,id',
            'note' => 'nullable|string|max:1000',
        ]);

        // Verify the codex entry is a subplot belonging to the same novel
        $codexEntry = CodexEntry::where('id', $validated['codex_entry_id'])
            ->where('novel_id', $scene->chapter->novel_id)
            ->where('type', CodexEntry::TYPE_SUBPLOT)
            ->firstOrFail();

        // Check if progression already exists
        $existingProgression = CodexProgression::where('codex_entry_id', $codexEntry->id)
            ->where('scene_id', $scene->id)
            ->first();

        if ($existingProgression) {
            // Update the existing progression
            $existingProgression->update([
                'note' => $validated['note'] ?? $existingProgression->note,
            ]);

            return response()->json([
                'progression' => [
                    'id' => $existingProgression->id,
                    'codex_entry_id' => $codexEntry->id,
                    'scene_id' => $scene->id,
                    'note' => $existingProgression->note,
                ],
            ]);
        }

        // Create new progression
        $progression = CodexProgression::create([
            'codex_entry_id' => $codexEntry->id,
            'scene_id' => $scene->id,
            'note' => $validated['note'] ?? "Scene: {$scene->title}",
            'mode' => CodexProgression::MODE_ADDITION,
            'sort_order' => CodexProgression::where('codex_entry_id', $codexEntry->id)->max('sort_order') + 1,
        ]);

        return response()->json([
            'progression' => [
                'id' => $progression->id,
                'codex_entry_id' => $codexEntry->id,
                'scene_id' => $scene->id,
                'note' => $progression->note,
            ],
        ], 201);
    }

    /**
     * Remove a subplot from a scene by deleting the progression.
     */
    public function removeSubplot(Request $request, Scene $scene, CodexEntry $codexEntry): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Find and delete the progression
        $deleted = CodexProgression::where('codex_entry_id', $codexEntry->id)
            ->where('scene_id', $scene->id)
            ->delete();

        return response()->json([
            'success' => $deleted > 0,
            'deleted' => $deleted,
        ]);
    }
}
