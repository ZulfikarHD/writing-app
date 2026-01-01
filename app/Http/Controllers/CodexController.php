<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCodexEntryRequest;
use App\Http\Requests\UpdateCodexEntryRequest;
use App\Models\CodexDetail;
use App\Models\CodexDetailDefinition;
use App\Models\CodexEntry;
use App\Models\Novel;
use App\Models\SeriesCodexEntry;
use App\Services\Codex\BulkEntryCreator;
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

        $query = $novel->codexEntries()->active()->with(['aliases', 'categories', 'tags']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('categories', fn ($q) => $q->where('codex_categories.id', $request->category));
        }

        // Filter by tag (Sprint 14)
        if ($request->filled('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('codex_tags.id', $request->tag));
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
                // Sprint 14: Tags
                'tags' => $entry->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'color' => $tag->color,
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
                'tag' => $request->tag, // Sprint 14
                'search' => $request->search,
            ],
            // Sprint 14: All tags for filter dropdown
            'tags' => $novel->codexTags()->orderBy('name')->get()->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'color' => $tag->color,
                'entry_type' => $tag->entry_type,
            ]),
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
            'details.definition', // Sprint 14: Include definition for type info
            'outgoingRelations.targetEntry',
            'incomingRelations.sourceEntry',
            'progressions.scene.chapter',
            'progressions.detail',
            'categories',
            'tags', // Sprint 14: Tags for quick labels
            'mentions.scene.chapter',
            'externalLinks', // Sprint 13: F-12.2.2
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
                'research_notes' => $entry->research_notes, // Sprint 13: US-12.3
                'thumbnail_path' => $entry->thumbnail_path,
                'ai_context_mode' => $entry->ai_context_mode,
                'is_archived' => $entry->is_archived,
                'is_tracking_enabled' => $entry->is_tracking_enabled, // Sprint 13: US-12.2
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
                    // Sprint 14: Type and AI visibility
                    'definition_id' => $detail->definition_id,
                    'ai_visibility' => $detail->ai_visibility,
                    'type' => $detail->getEffectiveType(),
                    'definition' => $detail->definition ? [
                        'id' => $detail->definition->id,
                        'name' => $detail->definition->name,
                        'type' => $detail->definition->type,
                        'options' => $detail->definition->options,
                        'show_in_sidebar' => $detail->definition->show_in_sidebar,
                    ] : null,
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
                // Sprint 14: Tags
                'tags' => $entry->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'color' => $tag->color,
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
                // Sprint 13: F-12.2.2 - External links for research
                'external_links' => $entry->externalLinks->map(fn ($link) => [
                    'id' => $link->id,
                    'title' => $link->title,
                    'url' => $link->url,
                    'notes' => $link->notes,
                    'sort_order' => $link->sort_order,
                ]),
            ],
            'types' => CodexEntry::getTypes(),
            'contextModes' => CodexEntry::getContextModes(),
            // Sprint 14: Available tags for this novel (filtered by entry type)
            'availableTags' => $entry->novel->codexTags()
                ->forType($entry->type)
                ->orderBy('name')
                ->get()
                ->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'color' => $tag->color,
                    'is_predefined' => $tag->is_predefined,
                ]),
            // Sprint 14: Detail definitions and presets
            'detailDefinitions' => $entry->novel->codexDetailDefinitions()
                ->forEntryType($entry->type)
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($def) => [
                    'id' => $def->id,
                    'name' => $def->name,
                    'type' => $def->type,
                    'options' => $def->options,
                    'ai_visibility' => $def->ai_visibility,
                    'show_in_sidebar' => $def->show_in_sidebar,
                ]),
            'detailPresets' => collect(CodexDetailDefinition::SYSTEM_PRESETS)
                ->filter(fn ($preset) => $preset['entry_types'] === null ||
                    in_array($entry->type, $preset['entry_types'], true))
                ->values()
                ->map(fn ($preset, $index) => [
                    'index' => $index,
                    'name' => $preset['name'],
                    'type' => $preset['type'],
                    'options' => $preset['options'],
                    'ai_visibility' => $preset['ai_visibility'],
                ]),
            'aiVisibilityModes' => CodexDetail::getAiVisibilityModes(),
            'detailTypes' => CodexDetail::getTypes(),
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
     * API endpoint to get a single entry's full details.
     * Used by CodexEntryModal and for polling to detect changes.
     */
    public function apiShow(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Load all relations needed for the full entry view
        $entry->load([
            'aliases',
            'details.definition',
            'outgoingRelations.target',
            'incomingRelations.source',
            'progressions.scene.chapter',
            'progressions.detail',
            'categories',
            'tags',
            'mentions.scene.chapter',
            'externalLinks',
        ]);

        return response()->json([
            'entry' => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'description' => $entry->description,
                'research_notes' => $entry->research_notes,
                'thumbnail_path' => $entry->thumbnail_path,
                'ai_context_mode' => $entry->ai_context_mode,
                'is_archived' => $entry->is_archived,
                'is_tracking_enabled' => $entry->is_tracking_enabled,
                'aliases' => $entry->aliases->map(fn ($alias) => [
                    'id' => $alias->id,
                    'alias' => $alias->alias,
                ]),
                'details' => $entry->details->map(fn ($detail) => [
                    'id' => $detail->id,
                    'key_name' => $detail->key_name,
                    'value' => $detail->value,
                    'sort_order' => $detail->sort_order,
                    'definition_id' => $detail->definition_id,
                    'ai_visibility' => $detail->ai_visibility,
                    'type' => $detail->type,
                    'definition' => $detail->definition ? [
                        'id' => $detail->definition->id,
                        'name' => $detail->definition->name,
                        'type' => $detail->definition->type,
                        'options' => $detail->definition->options,
                        'show_in_sidebar' => $detail->definition->show_in_sidebar,
                    ] : null,
                ]),
                'outgoing_relations' => $entry->outgoingRelations->map(fn ($rel) => [
                    'id' => $rel->id,
                    'relation_type' => $rel->relation_type,
                    'label' => $rel->label,
                    'is_bidirectional' => $rel->is_bidirectional,
                    'target' => $rel->target ? [
                        'id' => $rel->target->id,
                        'name' => $rel->target->name,
                        'type' => $rel->target->type,
                    ] : null,
                ]),
                'incoming_relations' => $entry->incomingRelations->map(fn ($rel) => [
                    'id' => $rel->id,
                    'relation_type' => $rel->relation_type,
                    'label' => $rel->label,
                    'is_bidirectional' => $rel->is_bidirectional,
                    'source' => $rel->source ? [
                        'id' => $rel->source->id,
                        'name' => $rel->source->name,
                        'type' => $rel->source->type,
                    ] : null,
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
                'tags' => $entry->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'color' => $tag->color,
                ]),
                'external_links' => $entry->externalLinks->map(fn ($link) => [
                    'id' => $link->id,
                    'title' => $link->title,
                    'url' => $link->url,
                    'notes' => $link->notes,
                    'sort_order' => $link->sort_order,
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
                'created_at' => $entry->created_at,
                'updated_at' => $entry->updated_at,
            ],
        ]);
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

    /**
     * Duplicate an entry with all its details and aliases.
     *
     * Sprint 15 (F-12.7.2): Deep clones an entry including:
     * - Basic entry data (with " (Copy)" appended to name)
     * - All aliases
     * - All details
     * - Progressions (WITHOUT scene links to avoid confusion)
     *
     * Does NOT clone:
     * - Relations (would create complex bidirectional issues)
     * - Mentions (scene-specific)
     * - Categories (user should re-assign as needed)
     * - Tags (user should re-assign as needed)
     *
     * @see https://www.novelcrafter.com/help/docs/codex/the-codex
     */
    public function duplicate(Request $request, CodexEntry $entry): JsonResponse
    {
        if ($entry->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Load all data to clone
        $entry->load(['aliases', 'details', 'progressions']);

        // Create the duplicate entry with " (Copy)" suffix
        $newName = $entry->name.' (Copy)';

        // Ensure unique name by adding number if needed
        $existingCount = CodexEntry::where('novel_id', $entry->novel_id)
            ->where('name', 'like', $entry->name.' (Copy%')
            ->count();

        if ($existingCount > 0) {
            $newName = $entry->name.' (Copy '.($existingCount + 1).')';
        }

        $newEntry = $entry->novel->codexEntries()->create([
            'type' => $entry->type,
            'name' => $newName,
            'description' => $entry->description,
            'research_notes' => $entry->research_notes,
            'thumbnail_path' => null, // Don't clone thumbnail - user should upload new
            'ai_context_mode' => $entry->ai_context_mode,
            'sort_order' => $entry->novel->codexEntries()->max('sort_order') + 1,
            'is_archived' => false,
            'is_tracking_enabled' => $entry->is_tracking_enabled,
        ]);

        // Clone aliases
        foreach ($entry->aliases as $alias) {
            $newEntry->aliases()->create([
                'alias' => $alias->alias,
            ]);
        }

        // Clone details (preserve definition links and AI visibility)
        foreach ($entry->details as $index => $detail) {
            $newEntry->details()->create([
                'definition_id' => $detail->definition_id,
                'key_name' => $detail->key_name,
                'value' => $detail->value,
                'sort_order' => $index,
                'ai_visibility' => $detail->ai_visibility,
                'type' => $detail->type,
            ]);
        }

        // Clone progressions (WITHOUT scene links - they don't apply to the copy)
        foreach ($entry->progressions as $index => $progression) {
            $newEntry->progressions()->create([
                'scene_id' => null, // Don't link to scenes
                'codex_detail_id' => null, // Details have new IDs
                'story_timestamp' => $progression->story_timestamp,
                'note' => $progression->note,
                'new_value' => $progression->new_value,
                'mode' => $progression->mode,
                'sort_order' => $index,
            ]);
        }

        return response()->json([
            'success' => true,
            'entry' => [
                'id' => $newEntry->id,
                'type' => $newEntry->type,
                'name' => $newEntry->name,
                'description' => $newEntry->description,
            ],
            'redirect' => route('codex.show', $newEntry),
        ], 201);
    }

    /**
     * Bulk create entries from formatted text input.
     *
     * Sprint 15 (US-12.12): Enables rapid codex setup by parsing multi-line
     * input in the format: "Name | Type | Description" (one per line)
     *
     * Supports:
     * - Preview mode (parse and validate only)
     * - Create mode (actually create entries)
     * - Skip duplicates option
     *
     * @see https://www.novelcrafter.com/help/docs/codex/the-codex
     */
    public function bulkCreate(Request $request, Novel $novel, BulkEntryCreator $bulkCreator): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'input' => 'required|string|max:100000',
            'preview' => 'sometimes|boolean',
            'skip_duplicates' => 'sometimes|boolean',
        ]);

        $isPreview = $validated['preview'] ?? false;
        $skipDuplicates = $validated['skip_duplicates'] ?? true;

        // Parse the input
        $parseResult = $bulkCreator->parse($validated['input']);

        // If there are parse errors, return them
        if (! empty($parseResult['errors'])) {
            return response()->json([
                'success' => false,
                'parse_errors' => $parseResult['errors'],
                'valid_count' => count($parseResult['entries']),
            ], 422);
        }

        // Validate against existing data
        $validationResult = $bulkCreator->validate($novel, $parseResult['entries']);

        // Preview mode: return what would be created
        if ($isPreview) {
            return response()->json([
                'success' => true,
                'preview' => true,
                'entries' => $validationResult['valid'],
                'warnings' => $validationResult['warnings'],
                'total' => count($validationResult['valid']),
            ]);
        }

        // Create mode: actually create the entries
        $createResult = $bulkCreator->create(
            $novel,
            $validationResult['valid'],
            $skipDuplicates
        );

        return response()->json([
            'success' => true,
            'created' => $createResult['created']->map(fn (CodexEntry $entry) => [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
            ]),
            'created_count' => $createResult['created']->count(),
            'skipped' => $createResult['skipped'],
            'skipped_count' => count($createResult['skipped']),
            'warnings' => $validationResult['warnings'],
        ], 201);
    }
}
