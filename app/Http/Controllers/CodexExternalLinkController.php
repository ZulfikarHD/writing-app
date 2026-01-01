<?php

namespace App\Http\Controllers;

use App\Models\CodexEntry;
use App\Models\CodexExternalLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manages external links for codex entries (Research tab).
 *
 * Part of Sprint 13 - F-12.2.2: External Links Storage
 *
 * @see https://www.novelcrafter.com/help/docs/codex/anatomy-codex-entry
 */
class CodexExternalLinkController extends Controller
{
    /**
     * List external links for an entry.
     */
    public function index(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json([
            'links' => $entry->externalLinks->map(fn (CodexExternalLink $link) => [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->url,
                'notes' => $link->notes,
                'sort_order' => $link->sort_order,
            ]),
        ]);
    }

    /**
     * Add a new external link.
     */
    public function store(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['sort_order'] = $entry->externalLinks()->max('sort_order') + 1;

        $link = $entry->externalLinks()->create($validated);

        return response()->json([
            'link' => [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->url,
                'notes' => $link->notes,
                'sort_order' => $link->sort_order,
            ],
        ], 201);
    }

    /**
     * Update an external link.
     */
    public function update(Request $request, CodexExternalLink $link): JsonResponse
    {
        if ($link->codexEntry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'url' => 'sometimes|required|url|max:2048',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        $link->update($validated);

        return response()->json([
            'link' => [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->url,
                'notes' => $link->notes,
                'sort_order' => $link->sort_order,
            ],
        ]);
    }

    /**
     * Delete an external link.
     */
    public function destroy(Request $request, CodexExternalLink $link): JsonResponse
    {
        if ($link->codexEntry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $link->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Reorder external links.
     */
    public function reorder(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'links' => 'required|array',
            'links.*.id' => 'required|integer|exists:codex_external_links,id',
            'links.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['links'] as $linkData) {
            CodexExternalLink::where('id', $linkData['id'])
                ->where('codex_entry_id', $entry->id)
                ->update(['sort_order' => $linkData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
