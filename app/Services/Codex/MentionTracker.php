<?php

namespace App\Services\Codex;

use App\Models\CodexEntry;
use App\Models\CodexMention;
use App\Models\Novel;
use App\Models\Scene;
use Illuminate\Support\Collection;

class MentionTracker
{
    /**
     * Scan a scene for codex entry mentions and update the mention records.
     */
    public function scanScene(Scene $scene): void
    {
        $novel = $scene->chapter->novel ?? null;
        if (! $novel) {
            return;
        }

        // Extract plain text from scene content
        $text = $this->extractText($scene->content);
        if (empty($text)) {
            // Clear all mentions for this scene if no content
            CodexMention::where('scene_id', $scene->id)->delete();

            return;
        }

        // Get all entries for this novel with their aliases
        $entries = $this->getEntriesWithAliases($novel);

        // Track mentions for each entry
        $mentionData = [];
        foreach ($entries as $entry) {
            $result = $this->findMentions($text, $entry);
            if ($result['count'] > 0) {
                $mentionData[$entry->id] = $result;
            }
        }

        // Update mention records
        $this->updateMentions($scene->id, $mentionData);
    }

    /**
     * Scan all scenes in a novel for mentions.
     */
    public function scanNovel(Novel $novel): void
    {
        $scenes = Scene::query()
            ->whereHas('chapter', function ($q) use ($novel) {
                $q->where('novel_id', $novel->id);
            })
            ->whereNull('archived_at')
            ->get();

        foreach ($scenes as $scene) {
            $this->scanScene($scene);
        }
    }

    /**
     * Get all codex entries for a novel with their aliases preloaded.
     * Only includes entries with tracking enabled (US-12.2).
     *
     * @return Collection<int, CodexEntry>
     */
    private function getEntriesWithAliases(Novel $novel): Collection
    {
        return CodexEntry::query()
            ->where('novel_id', $novel->id)
            ->where('is_archived', false)
            ->where('is_tracking_enabled', true) // US-12.2: Respect tracking toggle
            ->with('aliases')
            ->get();
    }

    /**
     * Find mentions of an entry in text.
     *
     * @return array{count: int, positions: array<int>}
     */
    private function findMentions(string $text, CodexEntry $entry): array
    {
        $searchTerms = collect([$entry->name])
            ->merge($entry->aliases->pluck('alias'))
            ->filter()
            ->map(fn ($term) => preg_quote($term, '/'))
            ->unique()
            ->values()
            ->all();

        if (empty($searchTerms)) {
            return ['count' => 0, 'positions' => []];
        }

        // Build regex pattern for word boundary matching
        // Use case-insensitive matching
        $pattern = '/\b('.implode('|', $searchTerms).')\b/iu';

        $matches = [];
        $positions = [];

        if (preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $positions[] = $match[1]; // Store character position
            }
        }

        return [
            'count' => count($positions),
            'positions' => $positions,
        ];
    }

    /**
     * Update mention records for a scene.
     *
     * @param  array<int, array{count: int, positions: array<int>}>  $mentionData
     */
    private function updateMentions(int $sceneId, array $mentionData): void
    {
        // Get existing mentions for this scene
        $existingMentions = CodexMention::where('scene_id', $sceneId)
            ->pluck('id', 'codex_entry_id')
            ->all();

        $entryIdsWithMentions = array_keys($mentionData);
        $existingEntryIds = array_keys($existingMentions);

        // Delete mentions for entries no longer mentioned
        $toDelete = array_diff($existingEntryIds, $entryIdsWithMentions);
        if (! empty($toDelete)) {
            CodexMention::where('scene_id', $sceneId)
                ->whereIn('codex_entry_id', $toDelete)
                ->delete();
        }

        // Update or create mentions
        foreach ($mentionData as $entryId => $data) {
            CodexMention::updateOrCreate(
                [
                    'codex_entry_id' => $entryId,
                    'scene_id' => $sceneId,
                ],
                [
                    'mention_count' => $data['count'],
                    'positions' => $data['positions'],
                ]
            );
        }
    }

    /**
     * Extract plain text from TipTap JSON content.
     *
     * @param  array<string, mixed>|null  $content
     */
    private function extractText(?array $content): string
    {
        if (empty($content)) {
            return '';
        }

        return $this->extractTextRecursive($content);
    }

    /**
     * Recursively extract text from content nodes.
     *
     * @param  array<string, mixed>  $node
     */
    private function extractTextRecursive(array $node): string
    {
        $text = '';

        if (isset($node['text'])) {
            $text .= $node['text'].' ';
        }

        if (isset($node['content']) && is_array($node['content'])) {
            foreach ($node['content'] as $child) {
                $text .= $this->extractTextRecursive($child);
            }
        }

        return $text;
    }

    /**
     * Get mention statistics for an entry across all scenes.
     *
     * @return array{total: int, by_scene: array<int, int>}
     */
    public function getEntryStats(CodexEntry $entry): array
    {
        $mentions = CodexMention::where('codex_entry_id', $entry->id)
            ->with('scene:id,title,chapter_id')
            ->get();

        return [
            'total' => $mentions->sum('mention_count'),
            'by_scene' => $mentions->pluck('mention_count', 'scene_id')->all(),
        ];
    }
}
