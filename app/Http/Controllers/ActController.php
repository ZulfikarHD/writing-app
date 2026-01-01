<?php

namespace App\Http\Controllers;

use App\Models\Act;
use App\Models\Novel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActController extends Controller
{
    public function index(Request $request, Novel $novel): JsonResponse
    {
        // Ensure user owns this novel
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $acts = $novel->acts()
            ->orderBy('position')
            ->get(['id', 'title', 'position']);

        return response()->json([
            'acts' => $acts,
        ]);
    }

    public function store(Request $request, Novel $novel): JsonResponse
    {
        // Ensure user owns this novel
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        // Get max position if not provided
        if (! isset($validated['position'])) {
            $validated['position'] = ($novel->acts()->max('position') ?? -1) + 1;
        }

        $act = $novel->acts()->create($validated);

        return response()->json([
            'act' => [
                'id' => $act->id,
                'title' => $act->title,
                'position' => $act->position,
            ],
        ], 201);
    }

    public function update(Request $request, Act $act): JsonResponse
    {
        // Ensure user owns this act's novel
        if ($act->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'position' => ['sometimes', 'integer', 'min:0'],
        ]);

        $act->update($validated);

        return response()->json([
            'act' => [
                'id' => $act->id,
                'title' => $act->title,
                'position' => $act->position,
            ],
        ]);
    }

    public function destroy(Request $request, Act $act): JsonResponse
    {
        // Ensure user owns this act's novel
        if ($act->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Remove act from chapters (set act_id to null)
        $act->chapters()->update(['act_id' => null]);

        $act->delete();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Novel $novel): JsonResponse
    {
        // Ensure user owns this novel
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'acts' => ['required', 'array'],
            'acts.*.id' => ['required', 'integer', 'exists:acts,id'],
            'acts.*.position' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['acts'] as $actData) {
            Act::where('id', $actData['id'])
                ->where('novel_id', $novel->id)
                ->update(['position' => $actData['position']]);
        }

        return response()->json(['success' => true]);
    }
}
