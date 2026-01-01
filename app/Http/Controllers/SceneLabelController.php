<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Scene;
use App\Models\SceneLabel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SceneLabelController extends Controller
{
    public function index(Request $request, Novel $novel): JsonResponse
    {
        // Ensure user owns this novel
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $labels = $novel->labels()
            ->orderBy('position')
            ->get(['id', 'name', 'color', 'position']);

        return response()->json([
            'labels' => $labels,
        ]);
    }

    public function store(Request $request, Novel $novel): JsonResponse
    {
        // Ensure user owns this novel
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['sometimes', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        // Set default color if not provided
        if (! isset($validated['color'])) {
            $validated['color'] = '#6B7280';
        }

        // Get max position if not provided
        if (! isset($validated['position'])) {
            $validated['position'] = ($novel->labels()->max('position') ?? -1) + 1;
        }

        $label = $novel->labels()->create($validated);

        return response()->json([
            'label' => [
                'id' => $label->id,
                'name' => $label->name,
                'color' => $label->color,
                'position' => $label->position,
            ],
        ], 201);
    }

    public function update(Request $request, SceneLabel $label): JsonResponse
    {
        // Ensure user owns this label's novel
        if ($label->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'color' => ['sometimes', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ]);

        $label->update($validated);

        return response()->json([
            'label' => [
                'id' => $label->id,
                'name' => $label->name,
                'color' => $label->color,
                'position' => $label->position,
            ],
        ]);
    }

    public function destroy(Request $request, SceneLabel $label): JsonResponse
    {
        // Ensure user owns this label's novel
        if ($label->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $label->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Assign labels to a scene.
     */
    public function assignToScene(Request $request, Scene $scene): JsonResponse
    {
        // Ensure user owns this scene's novel
        if ($scene->chapter->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'label_ids' => ['required', 'array'],
            'label_ids.*' => ['integer', 'exists:scene_labels,id'],
        ]);

        // Validate that all labels belong to the same novel
        $novelId = $scene->chapter->novel_id;
        $validLabelIds = SceneLabel::where('novel_id', $novelId)
            ->whereIn('id', $validated['label_ids'])
            ->pluck('id');

        $scene->labels()->sync($validLabelIds);

        return response()->json([
            'labels' => $scene->labels->map(fn ($label) => [
                'id' => $label->id,
                'name' => $label->name,
                'color' => $label->color,
            ]),
        ]);
    }
}
