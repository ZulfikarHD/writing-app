<?php

namespace App\Http\Controllers;

use App\Models\CodexEntry;
use App\Models\CodexProgression;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CodexProgressionController extends Controller
{
    /**
     * List all progressions for an entry.
     */
    public function index(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $progressions = $entry->progressions()
            ->with(['scene.chapter', 'detail'])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'progressions' => $progressions->map(fn (CodexProgression $prog) => [
                'id' => $prog->id,
                'story_timestamp' => $prog->story_timestamp,
                'note' => $prog->note,
                'new_value' => $prog->new_value,
                'mode' => $prog->mode,
                'sort_order' => $prog->sort_order,
                'scene' => $prog->scene ? [
                    'id' => $prog->scene->id,
                    'title' => $prog->scene->title,
                    'chapter' => $prog->scene->chapter ? [
                        'id' => $prog->scene->chapter->id,
                        'title' => $prog->scene->chapter->title,
                    ] : null,
                ] : null,
                'detail' => $prog->detail ? [
                    'id' => $prog->detail->id,
                    'key_name' => $prog->detail->key_name,
                ] : null,
            ]),
        ]);
    }

    /**
     * Add a new progression.
     */
    public function store(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'codex_detail_id' => ['nullable', 'integer', 'exists:codex_details,id'],
            'scene_id' => ['nullable', 'integer', 'exists:scenes,id'],
            'story_timestamp' => ['nullable', 'string', 'max:100'],
            'note' => ['required', 'string'],
            'new_value' => ['nullable', 'string'],
            'mode' => ['sometimes', 'string', 'in:addition,replace'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        // Set default sort order
        if (! isset($validated['sort_order'])) {
            $validated['sort_order'] = $entry->progressions()->max('sort_order') + 1;
        }

        $progression = $entry->progressions()->create($validated);
        $progression->load(['scene.chapter', 'detail']);

        return response()->json([
            'progression' => [
                'id' => $progression->id,
                'story_timestamp' => $progression->story_timestamp,
                'note' => $progression->note,
                'new_value' => $progression->new_value,
                'mode' => $progression->mode,
                'sort_order' => $progression->sort_order,
                'scene' => $progression->scene ? [
                    'id' => $progression->scene->id,
                    'title' => $progression->scene->title,
                    'chapter' => $progression->scene->chapter ? [
                        'id' => $progression->scene->chapter->id,
                        'title' => $progression->scene->chapter->title,
                    ] : null,
                ] : null,
                'detail' => $progression->detail ? [
                    'id' => $progression->detail->id,
                    'key_name' => $progression->detail->key_name,
                ] : null,
            ],
        ], 201);
    }

    /**
     * Update a progression.
     */
    public function update(Request $request, CodexProgression $progression): JsonResponse
    {
        if ($progression->entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'codex_detail_id' => ['nullable', 'integer', 'exists:codex_details,id'],
            'scene_id' => ['nullable', 'integer', 'exists:scenes,id'],
            'story_timestamp' => ['nullable', 'string', 'max:100'],
            'note' => ['sometimes', 'string'],
            'new_value' => ['nullable', 'string'],
            'mode' => ['sometimes', 'string', 'in:addition,replace'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $progression->update($validated);

        return response()->json([
            'progression' => [
                'id' => $progression->id,
                'story_timestamp' => $progression->story_timestamp,
                'note' => $progression->note,
                'new_value' => $progression->new_value,
                'mode' => $progression->mode,
                'sort_order' => $progression->sort_order,
            ],
        ]);
    }

    /**
     * Remove a progression.
     */
    public function destroy(Request $request, CodexProgression $progression): JsonResponse
    {
        if ($progression->entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $progression->delete();

        return response()->json(['success' => true]);
    }
}
