<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChapterRequest;
use App\Models\Chapter;
use App\Models\Novel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function index(Request $request, Novel $novel): JsonResponse
    {
        // Ensure user owns this novel
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $chapters = $novel->chapters()
            ->with(['scenes' => fn ($q) => $q->active()->orderBy('position')])
            ->orderBy('position')
            ->get();

        return response()->json([
            'chapters' => $chapters->map(fn (Chapter $chapter) => [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'position' => $chapter->position,
                'word_count' => $chapter->word_count,
                'scenes' => $chapter->scenes->map(fn ($scene) => [
                    'id' => $scene->id,
                    'title' => $scene->title,
                    'position' => $scene->position,
                    'status' => $scene->status,
                    'word_count' => $scene->word_count,
                ]),
            ]),
        ]);
    }

    public function store(StoreChapterRequest $request, Novel $novel): JsonResponse
    {
        $validated = $request->validated();

        // Get max position if not provided
        if (! isset($validated['position'])) {
            $validated['position'] = $novel->chapters()->max('position') + 1;
        }

        $chapter = $novel->chapters()->create($validated);

        // Create a default empty scene
        $scene = $chapter->scenes()->create([
            'title' => 'Scene 1',
            'position' => 0,
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
            'chapter' => [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'position' => $chapter->position,
                'scenes' => [
                    [
                        'id' => $scene->id,
                        'title' => $scene->title,
                        'position' => $scene->position,
                        'status' => $scene->status,
                        'word_count' => $scene->word_count,
                    ],
                ],
            ],
        ], 201);
    }

    public function update(Request $request, Chapter $chapter): JsonResponse
    {
        // Ensure user owns this chapter's novel
        if ($chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ]);

        $chapter->update($validated);

        return response()->json([
            'chapter' => [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'position' => $chapter->position,
            ],
        ]);
    }

    public function destroy(Request $request, Chapter $chapter): JsonResponse
    {
        // Ensure user owns this chapter's novel
        if ($chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $chapter->delete();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Novel $novel): JsonResponse
    {
        // Ensure user owns this novel
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'chapters' => ['required', 'array'],
            'chapters.*.id' => ['required', 'integer', 'exists:chapters,id'],
            'chapters.*.position' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['chapters'] as $chapterData) {
            Chapter::where('id', $chapterData['id'])
                ->where('novel_id', $novel->id)
                ->update(['position' => $chapterData['position']]);
        }

        return response()->json(['success' => true]);
    }
}
