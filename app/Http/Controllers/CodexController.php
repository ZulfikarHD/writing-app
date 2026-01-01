<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCodexEntryRequest;
use App\Http\Requests\UpdateCodexEntryRequest;
use App\Models\CodexEntry;
use App\Models\Novel;
use App\Models\SeriesCodexEntry;
use App\Services\Codex\BulkExportService;
use App\Services\Codex\BulkImportService;
use App\Services\Codex\MentionTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CodexController extends Controller
{
    /**
     * Display list of codex entries for a novel.
     */
    public function index(Request $request, Novel $novel): Response
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $query = $novel->codexEntries()->active()->with(['aliases', 'categories']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('categories', fn ($q) => $q->where('codex_categories.id', $request->category));
        }

        // Search by name/description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('aliases', fn ($aq) => $aq->where('alias', 'like', "%{$search}%"));
            });
        }

        $entries = $query->orderBy('sort_order')->orderBy('name')->get();
        $categories = $novel->codexCategories()->orderBy('sort_order')->get();

        // Get series entries if the novel is part of a series
        $seriesEntries = collect();
        $series = null;
        if ($novel->series_id) {
            $series = $novel->series;
            $seriesQuery = $series->codexEntries()->active()->with('aliases');

            if ($request->filled('type')) {
                $seriesQuery->where('type', $request->type);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $seriesQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('aliases', fn ($aq) => $aq->where('alias', 'like', "%{$search}%"));
                });
            }

            $seriesEntries = $seriesQuery->orderBy('sort_order')->orderBy('name')->get();
        }

        return Inertia::render('Codex/Index', [
            'novel' => [
                'id' => $novel->id,
                'title' => $novel->title,
                'series_id' => $novel->series_id,
            ],
            'series' => $series ? [
                'id' => $series->id,
                'title' => $series->title,
            ] : null,
            'entries' => $entries->map(fn (CodexEntry $entry) => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'description' => $entry->description,
                'thumbnail_path' => $entry->thumbnail_path,
                'ai_context_mode' => $entry->ai_context_mode,
                'aliases' => $entry->aliases->pluck('alias'),
                'categories' => $entry->categories->map(fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'color' => $cat->color,
                ]),
                'is_series_entry' => false,
            ]),
            'seriesEntries' => $seriesEntries->map(fn (SeriesCodexEntry $entry) => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'description' => $entry->description,
                'thumbnail_path' => $entry->thumbnail_path,
                'ai_context_mode' => $entry->ai_context_mode,
                'aliases' => $entry->aliases->pluck('alias'),
                'categories' => [],
                'is_series_entry' => true,
            ]),
            'categories' => $categories->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'color' => $cat->color,
            ]),
            'types' => CodexEntry::getTypes(),
            'filters' => [
                'type' => $request->type,
                'category' => $request->category,
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new entry.
     */
    public function create(Request $request, Novel $novel): Response
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        return Inertia::render('Codex/Create', [
            'novel' => [
                'id' => $novel->id,
                'title' => $novel->title,
            ],
            'types' => CodexEntry::getTypes(),
            'contextModes' => CodexEntry::getContextModes(),
        ]);
    }

    /**
     * Store a newly created entry.
     */
    public function store(StoreCodexEntryRequest $request, Novel $novel): JsonResponse
    {
        $validated = $request->validated();

        // Set default sort order
        if (! isset($validated['sort_order'])) {
            $validated['sort_order'] = $novel->codexEntries()->max('sort_order') + 1;
        }

        $entry = $novel->codexEntries()->create($validated);

        return response()->json([
            'entry' => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'description' => $entry->description,
                'ai_context_mode' => $entry->ai_context_mode,
            ],
            'redirect' => route('codex.show', $entry),
        ], 201);
    }

    /**
     * Display the specified entry.
     */
    public function show(Request $request, CodexEntry $entry): Response
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $entry->load([
            'aliases',
            'details',
            'outgoingRelations.targetEntry',
            'incomingRelations.sourceEntry',
            'progressions.scene.chapter',
            'progressions.detail',
            'categories',
            'mentions.scene.chapter',
        ]);

        // Get all scenes for the scene picker in ProgressionManager
        $scenes = $entry->novel->chapters()
            ->with('scenes:id,title,chapter_id')
            ->orderBy('position')
            ->get()
            ->flatMap(fn ($chapter) => $chapter->scenes->map(fn ($scene) => [
                'id' => $scene->id,
                'title' => $scene->title,
                'chapter' => [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                ],
            ]));

        return Inertia::render('Codex/Show', [
            'novel' => [
                'id' => $entry->novel->id,
                'title' => $entry->novel->title,
            ],
            'scenes' => $scenes,
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
                    'key_name' => $detail->key_name,
                    'value' => $detail->value,
                    'sort_order' => $detail->sort_order,
                ]),
                'outgoing_relations' => $entry->outgoingRelations->map(fn ($rel) => [
                    'id' => $rel->id,
                    'relation_type' => $rel->relation_type,
                    'label' => $rel->label,
                    'is_bidirectional' => $rel->is_bidirectional,
                    'target' => [
                        'id' => $rel->targetEntry->id,
                        'name' => $rel->targetEntry->name,
                        'type' => $rel->targetEntry->type,
                    ],
                ]),
                'incoming_relations' => $entry->incomingRelations->map(fn ($rel) => [
                    'id' => $rel->id,
                    'relation_type' => $rel->relation_type,
                    'label' => $rel->label,
                    'is_bidirectional' => $rel->is_bidirectional,
                    'source' => [
                        'id' => $rel->sourceEntry->id,
                        'name' => $rel->sourceEntry->name,
                        'type' => $rel->sourceEntry->type,
                    ],
                ]),
                'progressions' => $entry->progressions->map(fn ($prog) => [
                    'id' => $prog->id,
                    'story_timestamp' => $prog->story_timestamp,
                    'note' => $prog->note,
                    'new_value' => $prog->new_value,
                    'mode' => $prog->mode,
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
                'categories' => $entry->categories->map(fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'color' => $cat->color,
                ]),
                'mentions' => $entry->mentions->map(fn ($mention) => [
                    'id' => $mention->id,
                    'mention_count' => $mention->mention_count,
                    'scene' => [
                        'id' => $mention->scene->id,
                        'title' => $mention->scene->title,
                        'chapter' => $mention->scene->chapter ? [
                            'id' => $mention->scene->chapter->id,
                            'title' => $mention->scene->chapter->title,
                        ] : null,
                    ],
                ]),
            ],
            'types' => CodexEntry::getTypes(),
            'contextModes' => CodexEntry::getContextModes(),
        ]);
    }

    /**
     * Show the form for editing the specified entry.
     */
    public function edit(Request $request, CodexEntry $entry): Response
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        return Inertia::render('Codex/Edit', [
            'novel' => [
                'id' => $entry->novel->id,
                'title' => $entry->novel->title,
            ],
            'entry' => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'description' => $entry->description,
                'thumbnail_path' => $entry->thumbnail_path,
                'ai_context_mode' => $entry->ai_context_mode,
            ],
            'types' => CodexEntry::getTypes(),
            'contextModes' => CodexEntry::getContextModes(),
        ]);
    }

    /**
     * Update the specified entry.
     */
    public function update(UpdateCodexEntryRequest $request, CodexEntry $entry): JsonResponse
    {
        $validated = $request->validated();
        $entry->update($validated);

        return response()->json([
            'entry' => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'description' => $entry->description,
                'ai_context_mode' => $entry->ai_context_mode,
                'is_archived' => $entry->is_archived,
            ],
        ]);
    }

    /**
     * Archive the specified entry.
     */
    public function archive(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $entry->update(['is_archived' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Restore the specified entry from archive.
     */
    public function restore(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $entry->update(['is_archived' => false]);

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified entry (soft delete).
     */
    public function destroy(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $entry->delete();

        return response()->json(['success' => true]);
    }

    /**
     * API endpoint to list entries for a novel.
     */
    public function apiIndex(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $query = $novel->codexEntries()->active()->with('aliases');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('aliases', fn ($aq) => $aq->where('alias', 'like', "%{$search}%"));
            });
        }

        $entries = $query->orderBy('name')->get();

        return response()->json([
            'entries' => $entries->map(fn (CodexEntry $entry) => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'aliases' => $entry->aliases->pluck('alias'),
            ]),
        ]);
    }

    /**
     * Scan a novel for codex mentions.
     */
    public function scanMentions(Request $request, Novel $novel, MentionTracker $tracker): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $tracker->scanNovel($novel);

        return response()->json(['success' => true, 'message' => 'Mention scan completed']);
    }

    /**
     * Get all entries with full details for editor sidebar.
     */
    public function apiEntriesForEditor(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $entries = $novel->codexEntries()
            ->active()
            ->with(['aliases', 'details'])
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return response()->json([
            'entries' => $entries->map(fn (CodexEntry $entry) => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'description' => $entry->description,
                'ai_context_mode' => $entry->ai_context_mode,
                'aliases' => $entry->aliases->pluck('alias'),
                'details' => $entry->details->map(fn ($d) => [
                    'key' => $d->key_name,
                    'value' => $d->value,
                ]),
            ]),
        ]);
    }

    /**
     * Quick create a codex entry with minimal data (for editor integration).
     */
    public function quickCreate(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:'.implode(',', CodexEntry::getTypes()),
            'description' => 'nullable|string|max:10000',
            'add_alias' => 'nullable|string|max:255',
        ]);

        // Create the entry
        $entry = $novel->codexEntries()->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'ai_context_mode' => 'detected',
            'sort_order' => $novel->codexEntries()->max('sort_order') + 1,
        ]);

        // Add alias if different from name
        if (! empty($validated['add_alias']) && $validated['add_alias'] !== $validated['name']) {
            $entry->aliases()->create(['alias' => $validated['add_alias']]);
        }

        return response()->json([
            'entry' => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'description' => $entry->description,
                'ai_context_mode' => $entry->ai_context_mode,
                'aliases' => $entry->aliases->pluck('alias'),
            ],
        ], 201);
    }

    /**
     * Rescan mentions for a specific entry.
     */
    public function rescanMentions(Request $request, CodexEntry $entry, MentionTracker $tracker): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Scan all scenes for this novel to update mentions
        $tracker->scanNovel($entry->novel);

        // Return updated mentions
        $entry->load('mentions.scene.chapter');

        return response()->json([
            'success' => true,
            'mentions' => $entry->mentions->map(fn ($mention) => [
                'id' => $mention->id,
                'mention_count' => $mention->mention_count,
                'scene' => [
                    'id' => $mention->scene->id,
                    'title' => $mention->scene->title,
                    'chapter' => $mention->scene->chapter ? [
                        'id' => $mention->scene->chapter->id,
                        'title' => $mention->scene->chapter->title,
                    ] : null,
                ],
            ]),
        ]);
    }

    /**
     * Export all codex entries as JSON.
     */
    public function exportJson(Request $request, Novel $novel, BulkExportService $exportService): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $data = $exportService->exportAsJson($novel);

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="codex-export.json"');
    }

    /**
     * Export all codex entries as CSV.
     */
    public function exportCsv(Request $request, Novel $novel, BulkExportService $exportService): StreamedResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $filename = 'codex-'.str($novel->title)->slug().'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($novel, $exportService) {
            echo $exportService->exportAsCsv($novel);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Preview import from JSON file.
     */
    public function previewImport(Request $request, Novel $novel, BulkImportService $importService): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'file' => 'required|file|mimes:json|max:5120',
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'error' => 'Invalid JSON file: '.json_last_error_msg(),
            ], 422);
        }

        $preview = $importService->previewImport($novel, $data);

        return response()->json($preview);
    }

    /**
     * Import entries from JSON file.
     */
    public function importJson(Request $request, Novel $novel, BulkImportService $importService): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'file' => 'required|file|mimes:json|max:5120',
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'error' => 'Invalid JSON file: '.json_last_error_msg(),
            ], 422);
        }

        $result = $importService->importFromJson($novel, $data);

        return response()->json([
            'success' => true,
            'imported' => $result['imported'],
            'skipped' => $result['skipped'],
            'errors' => $result['errors'],
        ]);
    }
}
