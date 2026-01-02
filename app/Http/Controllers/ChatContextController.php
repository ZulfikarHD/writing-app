<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddContextRequest;
use App\Models\ChatContextItem;
use App\Models\ChatThread;
use App\Models\CodexEntry;
use App\Models\Novel;
use App\Models\Scene;
use App\Services\Chat\TokenCounterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatContextController extends Controller
{
    public function __construct(
        protected TokenCounterService $tokenCounter
    ) {}

    /**
     * List context items for a thread.
     */
    public function index(Request $request, ChatThread $thread): JsonResponse
    {
        $this->authorizeThread($request, $thread);

        $contextItems = $thread->contextItems()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($item) => $this->formatContextItem($item));

        $tokenInfo = $this->tokenCounter->countThreadContextTokens($thread);
        $limitInfo = $this->tokenCounter->checkContextLimit($thread);

        return response()->json([
            'items' => $contextItems,
            'tokens' => $tokenInfo,
            'limit' => $limitInfo,
        ]);
    }

    /**
     * Add a context item to a thread.
     */
    public function store(AddContextRequest $request, ChatThread $thread): JsonResponse
    {
        $this->authorizeThread($request, $thread);

        $validated = $request->validated();

        // Validate reference exists and belongs to the novel
        if (in_array($validated['context_type'], ['scene', 'codex']) && $validated['reference_id']) {
            $this->validateReference(
                $thread->novel_id,
                $validated['context_type'],
                $validated['reference_id']
            );
        }

        // Check if this context item already exists
        $existing = $thread->contextItems()
            ->where('context_type', $validated['context_type'])
            ->where('reference_id', $validated['reference_id'] ?? null)
            ->first();

        if ($existing) {
            // Reactivate if it was inactive
            $existing->update(['is_active' => true]);
            $item = $existing;
        } else {
            $item = $thread->contextItems()->create([
                'context_type' => $validated['context_type'],
                'reference_id' => $validated['reference_id'] ?? null,
                'custom_content' => $validated['custom_content'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);
        }

        $tokenInfo = $this->tokenCounter->countThreadContextTokens($thread);
        $limitInfo = $this->tokenCounter->checkContextLimit($thread);

        return response()->json([
            'item' => $this->formatContextItem($item),
            'tokens' => $tokenInfo,
            'limit' => $limitInfo,
        ], 201);
    }

    /**
     * Update a context item (toggle active, update content).
     */
    public function update(Request $request, ChatContextItem $item): JsonResponse
    {
        $thread = $item->thread;
        $this->authorizeThread($request, $thread);

        $validated = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'custom_content' => ['nullable', 'string', 'max:100000'],
        ]);

        $item->update($validated);

        $tokenInfo = $this->tokenCounter->countThreadContextTokens($thread);
        $limitInfo = $this->tokenCounter->checkContextLimit($thread);

        return response()->json([
            'item' => $this->formatContextItem($item->fresh()),
            'tokens' => $tokenInfo,
            'limit' => $limitInfo,
        ]);
    }

    /**
     * Remove a context item.
     */
    public function destroy(Request $request, ChatContextItem $item): JsonResponse
    {
        $thread = $item->thread;
        $this->authorizeThread($request, $thread);

        $item->delete();

        $tokenInfo = $this->tokenCounter->countThreadContextTokens($thread);
        $limitInfo = $this->tokenCounter->checkContextLimit($thread);

        return response()->json([
            'message' => 'Context item removed.',
            'tokens' => $tokenInfo,
            'limit' => $limitInfo,
        ]);
    }

    /**
     * Get full context preview with text.
     */
    public function preview(Request $request, ChatThread $thread): JsonResponse
    {
        $this->authorizeThread($request, $thread);

        $preview = $this->tokenCounter->buildContextPreview($thread);
        $limitInfo = $this->tokenCounter->checkContextLimit($thread);

        return response()->json([
            'preview' => $preview,
            'limit' => $limitInfo,
        ]);
    }

    /**
     * List available context sources for a novel.
     */
    public function sources(Request $request, Novel $novel): JsonResponse
    {
        if ($novel->user_id !== $request->user()->id) {
            abort(403);
        }

        // Get scenes grouped by chapter
        $chapters = $novel->chapters()
            ->with(['scenes:id,chapter_id,title,position,word_count'])
            ->orderBy('position')
            ->get()
            ->map(fn ($chapter) => [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'scenes' => $chapter->scenes->map(fn ($scene) => [
                    'id' => $scene->id,
                    'title' => $scene->title ?? 'Untitled Scene',
                    'word_count' => $scene->word_count,
                    'tokens' => $this->tokenCounter->estimateTokens(
                        ($scene->title ?? '').' '.($scene->summary ?? '')
                    ),
                ]),
            ]);

        // Get codex entries grouped by type
        $codexEntries = $novel->codexEntries()
            ->where('is_archived', false)
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->groupBy('type')
            ->map(fn ($entries, $type) => [
                'type' => $type,
                'entries' => $entries->map(fn ($entry) => [
                    'id' => $entry->id,
                    'name' => $entry->name,
                    'type' => $entry->type,
                    'tokens' => $this->tokenCounter->countCodexTokens($entry),
                ]),
            ])
            ->values();

        return response()->json([
            'chapters' => $chapters,
            'codex' => $codexEntries,
            'has_summary' => ! empty($novel->summary),
            'has_outline' => $novel->chapters()->count() > 0,
        ]);
    }

    /**
     * Bulk add context items.
     */
    public function bulkAdd(Request $request, ChatThread $thread): JsonResponse
    {
        $this->authorizeThread($request, $thread);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.context_type' => ['required', 'string', 'in:scene,codex,summary,outline,custom'],
            'items.*.reference_id' => ['nullable', 'integer'],
            'items.*.custom_content' => ['nullable', 'string', 'max:100000'],
        ]);

        $addedItems = [];

        foreach ($validated['items'] as $itemData) {
            // Validate reference if needed
            if (in_array($itemData['context_type'], ['scene', 'codex']) && ! empty($itemData['reference_id'])) {
                try {
                    $this->validateReference(
                        $thread->novel_id,
                        $itemData['context_type'],
                        $itemData['reference_id']
                    );
                } catch (\Exception $e) {
                    continue; // Skip invalid references
                }
            }

            // Check if already exists
            $existing = $thread->contextItems()
                ->where('context_type', $itemData['context_type'])
                ->where('reference_id', $itemData['reference_id'] ?? null)
                ->first();

            if ($existing) {
                $existing->update(['is_active' => true]);
                $addedItems[] = $existing;
            } else {
                $item = $thread->contextItems()->create([
                    'context_type' => $itemData['context_type'],
                    'reference_id' => $itemData['reference_id'] ?? null,
                    'custom_content' => $itemData['custom_content'] ?? null,
                    'is_active' => true,
                ]);
                $addedItems[] = $item;
            }
        }

        $tokenInfo = $this->tokenCounter->countThreadContextTokens($thread);
        $limitInfo = $this->tokenCounter->checkContextLimit($thread);

        return response()->json([
            'items' => collect($addedItems)->map(fn ($item) => $this->formatContextItem($item)),
            'tokens' => $tokenInfo,
            'limit' => $limitInfo,
        ], 201);
    }

    /**
     * Clear all context items from a thread.
     */
    public function clear(Request $request, ChatThread $thread): JsonResponse
    {
        $this->authorizeThread($request, $thread);

        $thread->contextItems()->delete();

        return response()->json([
            'message' => 'All context items cleared.',
            'tokens' => ['total' => 0, 'items' => []],
            'limit' => $this->tokenCounter->checkContextLimit($thread),
        ]);
    }

    /**
     * Authorize that the user owns the thread.
     */
    protected function authorizeThread(Request $request, ChatThread $thread): void
    {
        if ($thread->novel->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($thread->user_id !== $request->user()->id) {
            abort(403, 'You do not have access to this thread.');
        }
    }

    /**
     * Validate that a reference exists and belongs to the novel.
     */
    protected function validateReference(int $novelId, string $type, int $referenceId): void
    {
        $exists = match ($type) {
            'scene' => Scene::whereHas('chapter', fn ($q) => $q->where('novel_id', $novelId))
                ->where('id', $referenceId)
                ->exists(),
            'codex' => CodexEntry::where('novel_id', $novelId)
                ->where('id', $referenceId)
                ->exists(),
            default => true,
        };

        if (! $exists) {
            abort(404, "The specified {$type} was not found.");
        }
    }

    /**
     * Format a context item for API response.
     *
     * @return array<string, mixed>
     */
    protected function formatContextItem(ChatContextItem $item): array
    {
        $tokens = $this->tokenCounter->countContextItemTokens($item);
        $preview = $this->tokenCounter->getContextPreview($item, 200);

        $data = [
            'id' => $item->id,
            'thread_id' => $item->thread_id,
            'context_type' => $item->context_type,
            'reference_id' => $item->reference_id,
            'is_active' => $item->is_active,
            'tokens' => $tokens,
            'preview' => $preview,
            'created_at' => $item->created_at?->toISOString(),
        ];

        // Add reference details
        if ($item->context_type === 'scene' && $item->scene) {
            $data['reference'] = [
                'id' => $item->scene->id,
                'title' => $item->scene->title ?? 'Untitled Scene',
                'word_count' => $item->scene->word_count,
            ];
        } elseif ($item->context_type === 'codex' && $item->codexEntry) {
            $data['reference'] = [
                'id' => $item->codexEntry->id,
                'name' => $item->codexEntry->name,
                'type' => $item->codexEntry->type,
            ];
        } elseif ($item->context_type === 'custom') {
            $data['custom_content'] = $item->custom_content;
        }

        return $data;
    }
}
