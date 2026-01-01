<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Series;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeriesController extends Controller
{
    /**
     * Display list of all series for the current user.
     */
    public function index(Request $request): Response
    {
        $series = Series::where('user_id', $request->user()->id)
            ->withCount('novels')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return Inertia::render('Series/Index', [
            'series' => $series->map(fn (Series $s) => [
                'id' => $s->id,
                'title' => $s->title,
                'description' => $s->description,
                'cover_path' => $s->cover_path,
                'genre' => $s->genre,
                'novels_count' => $s->novels_count,
                'created_at' => $s->created_at->toISOString(),
            ]),
        ]);
    }

    /**
     * Show the form for creating a new series.
     */
    public function create(Request $request): Response
    {
        $novels = Novel::where('user_id', $request->user()->id)
            ->whereNull('series_id')
            ->orderBy('title')
            ->get(['id', 'title', 'cover_path']);

        return Inertia::render('Series/Create', [
            'availableNovels' => $novels,
        ]);
    }

    /**
     * Store a newly created series.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'genre' => 'nullable|string|max:100',
            'novel_ids' => 'nullable|array',
            'novel_ids.*' => 'exists:novels,id',
        ]);

        $series = Series::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'genre' => $validated['genre'] ?? null,
            'sort_order' => Series::where('user_id', $request->user()->id)->max('sort_order') + 1,
        ]);

        // Assign novels to the series
        if (! empty($validated['novel_ids'])) {
            $order = 1;
            foreach ($validated['novel_ids'] as $novelId) {
                Novel::where('id', $novelId)
                    ->where('user_id', $request->user()->id)
                    ->update([
                        'series_id' => $series->id,
                        'series_order' => $order++,
                    ]);
            }
        }

        return response()->json([
            'series' => [
                'id' => $series->id,
                'title' => $series->title,
            ],
            'redirect' => route('series.show', $series),
        ], 201);
    }

    /**
     * Display the specified series.
     */
    public function show(Request $request, Series $series): Response
    {
        if ($series->user_id !== $request->user()->id) {
            abort(403);
        }

        $series->load([
            'novels' => fn ($q) => $q->orderBy('series_order'),
            'codexEntries.aliases',
        ]);

        $availableNovels = Novel::where('user_id', $request->user()->id)
            ->whereNull('series_id')
            ->orderBy('title')
            ->get(['id', 'title', 'cover_path']);

        return Inertia::render('Series/Show', [
            'series' => [
                'id' => $series->id,
                'title' => $series->title,
                'description' => $series->description,
                'cover_path' => $series->cover_path,
                'genre' => $series->genre,
                'created_at' => $series->created_at->toISOString(),
                'updated_at' => $series->updated_at->toISOString(),
                'novels' => $series->novels->map(fn ($novel) => [
                    'id' => $novel->id,
                    'title' => $novel->title,
                    'cover_path' => $novel->cover_path,
                    'word_count' => $novel->word_count,
                    'series_order' => $novel->series_order,
                ]),
                'codex_entries' => $series->codexEntries->map(fn ($entry) => [
                    'id' => $entry->id,
                    'type' => $entry->type,
                    'name' => $entry->name,
                    'description' => $entry->description,
                    'aliases' => $entry->aliases->pluck('alias'),
                ]),
            ],
            'availableNovels' => $availableNovels,
        ]);
    }

    /**
     * Show the form for editing the specified series.
     */
    public function edit(Request $request, Series $series): Response
    {
        if ($series->user_id !== $request->user()->id) {
            abort(403);
        }

        return Inertia::render('Series/Edit', [
            'series' => [
                'id' => $series->id,
                'title' => $series->title,
                'description' => $series->description,
                'cover_path' => $series->cover_path,
                'genre' => $series->genre,
            ],
        ]);
    }

    /**
     * Update the specified series.
     */
    public function update(Request $request, Series $series): JsonResponse
    {
        if ($series->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'genre' => 'nullable|string|max:100',
        ]);

        $series->update($validated);

        return response()->json([
            'series' => [
                'id' => $series->id,
                'title' => $series->title,
                'description' => $series->description,
            ],
        ]);
    }

    /**
     * Remove the specified series.
     */
    public function destroy(Request $request, Series $series): JsonResponse
    {
        if ($series->user_id !== $request->user()->id) {
            abort(403);
        }

        // Remove novels from series first (don't delete them)
        $series->novels()->update(['series_id' => null, 'series_order' => null]);

        $series->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Add a novel to the series.
     */
    public function addNovel(Request $request, Series $series): JsonResponse
    {
        if ($series->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'novel_id' => 'required|exists:novels,id',
        ]);

        $novel = Novel::where('id', $validated['novel_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $novel) {
            return response()->json(['error' => 'Novel not found'], 404);
        }

        $maxOrder = $series->novels()->max('series_order') ?? 0;

        $novel->update([
            'series_id' => $series->id,
            'series_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'novel' => [
                'id' => $novel->id,
                'title' => $novel->title,
                'series_order' => $novel->series_order,
            ],
        ]);
    }

    /**
     * Remove a novel from the series.
     */
    public function removeNovel(Request $request, Series $series, Novel $novel): JsonResponse
    {
        if ($series->user_id !== $request->user()->id || $novel->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($novel->series_id !== $series->id) {
            return response()->json(['error' => 'Novel is not in this series'], 400);
        }

        $novel->update([
            'series_id' => null,
            'series_order' => null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Reorder novels within the series.
     */
    public function reorderNovels(Request $request, Series $series): JsonResponse
    {
        if ($series->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'novels' => 'required|array',
            'novels.*.id' => 'required|exists:novels,id',
            'novels.*.order' => 'required|integer|min:1',
        ]);

        foreach ($validated['novels'] as $novelData) {
            Novel::where('id', $novelData['id'])
                ->where('series_id', $series->id)
                ->update(['series_order' => $novelData['order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * API: List all series for current user.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $series = Series::where('user_id', $request->user()->id)
            ->withCount('novels')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return response()->json([
            'series' => $series->map(fn (Series $s) => [
                'id' => $s->id,
                'title' => $s->title,
                'novels_count' => $s->novels_count,
            ]),
        ]);
    }
}
