<?php

namespace App\Http\Controllers;

use App\Models\Series;
use App\Models\SeriesCodexEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeriesCodexController extends Controller
{
    /**
     * Display list of codex entries for a series.
     */
    public function index(Request $request, Series $series): Response
    {
        if ($series->user_id !== $request->user()->id) {
            abort(403);
        }

        $query = $series->codexEntries()->active()->with('aliases');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('aliases', fn ($aq) => $aq->where('alias', 'like', "%{$search}%"));
            });
        }

        $entries = $query->orderBy('sort_order')->orderBy('name')->get();

        return Inertia::render('Series/Codex/Index', [
            'series' => [
                'id' => $series->id,
                'title' => $series->title,
            ],
            'entries' => $entries->map(fn (SeriesCodexEntry $entry) => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'description' => $entry->description,
                'thumbnail_path' => $entry->thumbnail_path,
                'ai_context_mode' => $entry->ai_context_mode,
                'aliases' => $entry->aliases->pluck('alias'),
            ]),
            'types' => SeriesCodexEntry::getTypes(),
            'filters' => [
                'type' => $request->type,
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Store a newly created series codex entry.
     */
    public function store(Request $request, Series $series): JsonResponse
    {
        if ($series->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|string|in:'.implode(',', SeriesCodexEntry::getTypes()),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'ai_context_mode' => 'nullable|string|in:always,detected,manual,never',
        ]);

        $entry = $series->codexEntries()->create([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'ai_context_mode' => $validated['ai_context_mode'] ?? 'detected',
            'sort_order' => $series->codexEntries()->max('sort_order') + 1,
        ]);

        return response()->json([
            'entry' => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'description' => $entry->description,
            ],
        ], 201);
    }

    /**
     * Display the specified series codex entry.
     */
    public function show(Request $request, SeriesCodexEntry $entry): Response
    {
        if ($entry->series->user_id !== $request->user()->id) {
            abort(403);
        }

        $entry->load(['aliases', 'details']);

        return Inertia::render('Series/Codex/Show', [
            'series' => [
                'id' => $entry->series->id,
                'title' => $entry->series->title,
            ],
            'entry' => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'description' => $entry->description,
                'thumbnail_path' => $entry->thumbnail_path,
                'ai_context_mode' => $entry->ai_context_mode,
                'is_archived' => $entry->is_archived,
                'created_at' => $entry->created_at->toISOString(),
                'updated_at' => $entry->updated_at->toISOString(),
                'aliases' => $entry->aliases->map(fn ($alias) => [
                    'id' => $alias->id,
                    'alias' => $alias->alias,
                ]),
                'details' => $entry->details->map(fn ($detail) => [
                    'id' => $detail->id,
                    'label' => $detail->label,
                    'value' => $detail->value,
                    'sort_order' => $detail->sort_order,
                ]),
            ],
            'types' => SeriesCodexEntry::getTypes(),
        ]);
    }

    /**
     * Update the specified series codex entry.
     */
    public function update(Request $request, SeriesCodexEntry $entry): JsonResponse
    {
        if ($entry->series->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'sometimes|string|in:'.implode(',', SeriesCodexEntry::getTypes()),
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:10000',
            'ai_context_mode' => 'nullable|string|in:always,detected,manual,never',
        ]);

        $entry->update($validated);

        return response()->json([
            'entry' => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'description' => $entry->description,
            ],
        ]);
    }

    /**
     * Remove the specified series codex entry.
     */
    public function destroy(Request $request, SeriesCodexEntry $entry): JsonResponse
    {
        if ($entry->series->user_id !== $request->user()->id) {
            abort(403);
        }

        $entry->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Add alias to series codex entry.
     */
    public function addAlias(Request $request, SeriesCodexEntry $entry): JsonResponse
    {
        if ($entry->series->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'alias' => 'required|string|max:255',
        ]);

        $alias = $entry->aliases()->create($validated);

        return response()->json([
            'alias' => [
                'id' => $alias->id,
                'alias' => $alias->alias,
            ],
        ], 201);
    }

    /**
     * Remove alias from series codex entry.
     */
    public function removeAlias(Request $request, SeriesCodexEntry $entry, int $aliasId): JsonResponse
    {
        if ($entry->series->user_id !== $request->user()->id) {
            abort(403);
        }

        $entry->aliases()->where('id', $aliasId)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Add detail to series codex entry.
     */
    public function addDetail(Request $request, SeriesCodexEntry $entry): JsonResponse
    {
        if ($entry->series->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:10000',
        ]);

        $detail = $entry->details()->create([
            'label' => $validated['label'],
            'value' => $validated['value'],
            'sort_order' => $entry->details()->max('sort_order') + 1,
        ]);

        return response()->json([
            'detail' => [
                'id' => $detail->id,
                'label' => $detail->label,
                'value' => $detail->value,
                'sort_order' => $detail->sort_order,
            ],
        ], 201);
    }

    /**
     * Update detail of series codex entry.
     */
    public function updateDetail(Request $request, SeriesCodexEntry $entry, int $detailId): JsonResponse
    {
        if ($entry->series->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'label' => 'sometimes|string|max:255',
            'value' => 'sometimes|string|max:10000',
        ]);

        $entry->details()->where('id', $detailId)->update($validated);

        return response()->json(['success' => true]);
    }

    /**
     * Remove detail from series codex entry.
     */
    public function removeDetail(Request $request, SeriesCodexEntry $entry, int $detailId): JsonResponse
    {
        if ($entry->series->user_id !== $request->user()->id) {
            abort(403);
        }

        $entry->details()->where('id', $detailId)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * API: List all series codex entries.
     */
    public function apiIndex(Request $request, Series $series): JsonResponse
    {
        if ($series->user_id !== $request->user()->id) {
            abort(403);
        }

        $entries = $series->codexEntries()
            ->active()
            ->with('aliases')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'entries' => $entries->map(fn (SeriesCodexEntry $entry) => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'description' => $entry->description,
                'aliases' => $entry->aliases->pluck('alias'),
            ]),
        ]);
    }
}
