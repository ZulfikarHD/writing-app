<?php

namespace App\Http\Controllers;

use App\Models\CodexDetail;
use App\Models\CodexEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CodexDetailController extends Controller
{
    /**
     * List all details for an entry.
     */
    public function index(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json([
            'details' => $entry->details->map(fn (CodexDetail $detail) => [
                'id' => $detail->id,
                'key_name' => $detail->key_name,
                'value' => $detail->value,
                'sort_order' => $detail->sort_order,
            ]),
        ]);
    }

    /**
     * Add a new detail to an entry.
     */
    public function store(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'key_name' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        // Set default sort order if not provided
        if (! isset($validated['sort_order'])) {
            $validated['sort_order'] = $entry->details()->max('sort_order') + 1;
        }

        $detail = $entry->details()->create($validated);

        return response()->json([
            'detail' => [
                'id' => $detail->id,
                'key_name' => $detail->key_name,
                'value' => $detail->value,
                'sort_order' => $detail->sort_order,
            ],
        ], 201);
    }

    /**
     * Update a detail.
     */
    public function update(Request $request, CodexDetail $detail): JsonResponse
    {
        if ($detail->entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'key_name' => ['sometimes', 'string', 'max:255'],
            'value' => ['sometimes', 'string'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $detail->update($validated);

        return response()->json([
            'detail' => [
                'id' => $detail->id,
                'key_name' => $detail->key_name,
                'value' => $detail->value,
                'sort_order' => $detail->sort_order,
            ],
        ]);
    }

    /**
     * Remove a detail.
     */
    public function destroy(Request $request, CodexDetail $detail): JsonResponse
    {
        if ($detail->entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $detail->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Reorder details for an entry.
     */
    public function reorder(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'details' => ['required', 'array'],
            'details.*.id' => ['required', 'integer', 'exists:codex_details,id'],
            'details.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['details'] as $detailData) {
            CodexDetail::where('id', $detailData['id'])
                ->where('codex_entry_id', $entry->id)
                ->update(['sort_order' => $detailData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
