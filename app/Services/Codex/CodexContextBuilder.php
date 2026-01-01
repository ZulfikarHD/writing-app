<?php

namespace App\Services\Codex;

use App\Models\CodexDetail;
use App\Models\CodexEntry;
use Illuminate\Support\Collection;

/**
 * Builds AI context from codex entries, including related entries.
 *
 * US-12.8: When an entry is detected in text, related entries are also
 * pulled into the AI context automatically. This service handles:
 * - Configurable cascade depth (1 level, 2 levels, etc.)
 * - Circular reference prevention
 * - AI context mode filtering
 * - Performance optimization via caching
 *
 * Sprint 14 (US-12.6): Respects per-detail AI visibility settings:
 * - 'always': Always included in AI context
 * - 'never': Never included (private notes in details)
 * - 'nsfw_only': Only included when NSFW prompt is used
 *
 * @see https://www.novelcrafter.com/help/docs/codex/codex-relations
 * @see https://www.novelcrafter.com/help/docs/codex/codex-details
 */
class CodexContextBuilder
{
    /**
     * Default cascade depth for relation pulling.
     */
    public const DEFAULT_CASCADE_DEPTH = 1;

    /**
     * Maximum allowed cascade depth to prevent performance issues.
     */
    public const MAX_CASCADE_DEPTH = 3;

    /**
     * Whether to include NSFW-only content.
     */
    private bool $includeNsfw = false;

    /**
     * Build AI context for detected entries, including related entries.
     *
     * @param  array<int>  $detectedEntryIds  IDs of entries detected in text
     * @param  int  $cascadeDepth  How many levels of relations to include
     * @param  bool  $includeNsfw  Whether to include NSFW-only details
     * @return Collection<int, array<string, mixed>> Formatted context for AI
     */
    public function buildContext(
        array $detectedEntryIds,
        int $cascadeDepth = self::DEFAULT_CASCADE_DEPTH,
        bool $includeNsfw = false
    ): Collection {
        if (empty($detectedEntryIds)) {
            return collect();
        }

        $this->includeNsfw = $includeNsfw;

        // Clamp cascade depth to safe range
        $cascadeDepth = max(0, min($cascadeDepth, self::MAX_CASCADE_DEPTH));

        // Get all entry IDs including related ones
        $allEntryIds = $this->gatherRelatedEntryIds($detectedEntryIds, $cascadeDepth);

        // Load entries with their relations and details (with definitions for type info)
        $entries = CodexEntry::query()
            ->whereIn('id', $allEntryIds)
            ->where('ai_context_mode', '!=', CodexEntry::CONTEXT_NEVER)
            ->where('is_archived', false)
            ->with([
                'aliases',
                'details.definition', // Sprint 14: Include definition for visibility info
                'outgoingRelations.targetEntry',
                'incomingRelations.sourceEntry',
            ])
            ->get();

        return $this->formatForAI($entries, $detectedEntryIds);
    }

    /**
     * Gather all related entry IDs up to the specified cascade depth.
     * Uses breadth-first traversal to prevent circular references.
     *
     * @param  array<int>  $startIds  Initial entry IDs
     * @param  int  $depth  Cascade depth
     * @return array<int> All entry IDs to include
     */
    private function gatherRelatedEntryIds(array $startIds, int $depth): array
    {
        $visited = collect($startIds)->unique()->flip();
        $currentLevel = collect($startIds);

        for ($level = 0; $level < $depth && $currentLevel->isNotEmpty(); $level++) {
            // Get relations for current level entries
            $relations = \DB::table('codex_relations')
                ->where(function ($query) use ($currentLevel) {
                    $query->whereIn('source_entry_id', $currentLevel)
                        ->orWhereIn('target_entry_id', $currentLevel);
                })
                ->get();

            // Collect new entry IDs from relations
            $newIds = collect();
            foreach ($relations as $relation) {
                if (! $visited->has($relation->source_entry_id)) {
                    $newIds->push($relation->source_entry_id);
                    $visited->put($relation->source_entry_id, true);
                }
                if (! $visited->has($relation->target_entry_id)) {
                    $newIds->push($relation->target_entry_id);
                    $visited->put($relation->target_entry_id, true);
                }
            }

            $currentLevel = $newIds;
        }

        return $visited->keys()->all();
    }

    /**
     * Format entries for AI consumption.
     * Sprint 14: Filters details by AI visibility.
     *
     * @param  Collection<int, CodexEntry>  $entries
     * @param  array<int>  $detectedIds  IDs that were directly detected (not via relations)
     * @return Collection<int, array<string, mixed>>
     */
    private function formatForAI(Collection $entries, array $detectedIds): Collection
    {
        return $entries->map(function (CodexEntry $entry) use ($detectedIds) {
            $isDirectlyDetected = in_array($entry->id, $detectedIds);

            return [
                'id' => $entry->id,
                'type' => $entry->type,
                'name' => $entry->name,
                'aliases' => $entry->aliases->pluck('alias')->all(),
                'description' => $entry->description,
                // Sprint 14: Filter details by AI visibility
                'details' => $this->filterDetailsForAI($entry->details)->map(fn ($d) => [
                    'key' => $d->key_name,
                    'value' => $d->value,
                ])->values()->all(),
                'relations' => $this->formatRelations($entry),
                'is_directly_detected' => $isDirectlyDetected,
                'included_via_relation' => ! $isDirectlyDetected,
            ];
        });
    }

    /**
     * Filter details based on their AI visibility settings.
     * Sprint 14 (US-12.6): Per-detail AI visibility control.
     *
     * @param  Collection<int, CodexDetail>  $details
     * @return Collection<int, CodexDetail>
     */
    private function filterDetailsForAI(Collection $details): Collection
    {
        return $details->filter(function (CodexDetail $detail) {
            return $detail->shouldIncludeInAiContext($this->includeNsfw);
        });
    }

    /**
     * Format relations for an entry.
     *
     * @return array<array<string, mixed>>
     */
    private function formatRelations(CodexEntry $entry): array
    {
        $relations = [];

        foreach ($entry->outgoingRelations as $relation) {
            if ($relation->targetEntry && $relation->targetEntry->ai_context_mode !== CodexEntry::CONTEXT_NEVER) {
                $relations[] = [
                    'type' => $relation->relation_type,
                    'label' => $relation->label,
                    'target' => $relation->targetEntry->name,
                    'target_id' => $relation->targetEntry->id,
                    'direction' => 'outgoing',
                ];
            }
        }

        foreach ($entry->incomingRelations as $relation) {
            // Only include if bidirectional or explicitly showing reverse
            if ($relation->sourceEntry && $relation->sourceEntry->ai_context_mode !== CodexEntry::CONTEXT_NEVER) {
                $relations[] = [
                    'type' => $relation->relation_type,
                    'label' => $relation->label,
                    'source' => $relation->sourceEntry->name,
                    'source_id' => $relation->sourceEntry->id,
                    'direction' => 'incoming',
                    'is_bidirectional' => $relation->is_bidirectional,
                ];
            }
        }

        return $relations;
    }

    /**
     * Get related entry IDs for a specific entry (single level).
     * Useful for preview purposes.
     *
     * @return array<int>
     */
    public function getRelatedEntryIds(CodexEntry $entry): array
    {
        $ids = [];

        foreach ($entry->outgoingRelations as $relation) {
            $ids[] = $relation->target_entry_id;
        }

        foreach ($entry->incomingRelations as $relation) {
            $ids[] = $relation->source_entry_id;
        }

        return array_unique($ids);
    }

    /**
     * Build a compact context string for AI prompts.
     * Sprint 14: Now respects per-detail AI visibility.
     *
     * @param  array<int>  $detectedEntryIds
     * @param  int  $cascadeDepth
     * @param  bool  $includeNsfw  Whether to include NSFW-only content
     */
    public function buildContextString(
        array $detectedEntryIds,
        int $cascadeDepth = self::DEFAULT_CASCADE_DEPTH,
        bool $includeNsfw = false
    ): string {
        $context = $this->buildContext($detectedEntryIds, $cascadeDepth, $includeNsfw);

        if ($context->isEmpty()) {
            return '';
        }

        $output = "## Codex Context\n\n";

        foreach ($context as $entry) {
            $output .= "### {$entry['name']} ({$entry['type']})\n";

            if (! empty($entry['aliases'])) {
                $output .= 'Aliases: '.implode(', ', $entry['aliases'])."\n";
            }

            if (! empty($entry['description'])) {
                $output .= $entry['description']."\n";
            }

            if (! empty($entry['details'])) {
                foreach ($entry['details'] as $detail) {
                    $output .= "- {$detail['key']}: {$detail['value']}\n";
                }
            }

            if (! empty($entry['relations'])) {
                $output .= "Relations:\n";
                foreach ($entry['relations'] as $rel) {
                    $name = $rel['target'] ?? $rel['source'];
                    $output .= "- {$rel['type']}: {$name}";
                    if (! empty($rel['label'])) {
                        $output .= " ({$rel['label']})";
                    }
                    $output .= "\n";
                }
            }

            if ($entry['included_via_relation']) {
                $output .= "(Included via relation)\n";
            }

            $output .= "\n";
        }

        return $output;
    }

    /**
     * Set whether NSFW content should be included.
     * Fluent setter for method chaining.
     */
    public function withNsfw(bool $includeNsfw = true): self
    {
        $this->includeNsfw = $includeNsfw;

        return $this;
    }
}
