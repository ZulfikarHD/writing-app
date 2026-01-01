<?php

namespace App\Services\Codex;

use App\Models\CodexEntry;
use App\Models\Novel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ChatMentionTracker Service
 *
 * Sprint 16 (F-12.1.5): Handles codex mention detection in chat messages.
 *
 * Runs synchronously - auto-scan on save (no queue workers).
 * Uses shared detection logic from MentionTracker.
 *
 * Note: This service is ready to use once chat_messages table is implemented.
 */
class ChatMentionTracker
{
    public function __construct(
        private MentionTracker $mentionTracker
    ) {}

    /**
     * Scan a chat message for codex mentions.
     * Runs synchronously - auto-scan on save.
     *
     * @param  Model  $message  The chat message model (any model with content and novel relationship)
     */
    public function scanMessage(Model $message): void
    {
        // Check if chat_message_mentions table exists
        if (! Schema::hasTable('chat_message_mentions')) {
            return;
        }

        // Get novel from the message (chat should belong to a novel)
        $novel = $this->getNovelFromMessage($message);
        if (! $novel) {
            return;
        }

        // Extract content from message
        $content = $message->content ?? '';
        if (empty($content)) {
            // Clear mentions if no content
            $this->clearMentions($message->id);

            return;
        }

        // Get all trackable entries for this novel
        $entries = $this->getTrackableEntries($novel);

        // Detect mentions
        $mentionData = $this->detectMentions($content, $entries);

        // Update mention records
        $this->updateMentions($message->id, $mentionData);
    }

    /**
     * Get the novel associated with a chat message.
     */
    private function getNovelFromMessage(Model $message): ?Novel
    {
        // Try different relationships - adjust based on your chat implementation
        if (method_exists($message, 'novel')) {
            return $message->novel;
        }

        if (method_exists($message, 'chat') && $message->chat && method_exists($message->chat, 'novel')) {
            return $message->chat->novel;
        }

        return null;
    }

    /**
     * Get all codex entries with tracking enabled.
     *
     * @return Collection<int, CodexEntry>
     */
    private function getTrackableEntries(Novel $novel): Collection
    {
        return CodexEntry::query()
            ->where('novel_id', $novel->id)
            ->where('is_archived', false)
            ->where('is_tracking_enabled', true)
            ->with('aliases')
            ->get();
    }

    /**
     * Detect mentions in text content.
     *
     * @param  Collection<int, CodexEntry>  $entries
     * @return array<int, array{count: int}>
     */
    private function detectMentions(string $content, Collection $entries): array
    {
        $mentionData = [];

        foreach ($entries as $entry) {
            $count = $this->countMentions($content, $entry);
            if ($count > 0) {
                $mentionData[$entry->id] = ['count' => $count];
            }
        }

        return $mentionData;
    }

    /**
     * Count mentions of an entry in text.
     */
    private function countMentions(string $text, CodexEntry $entry): int
    {
        $searchTerms = collect([$entry->name])
            ->merge($entry->aliases->pluck('alias'))
            ->filter()
            ->map(fn ($term) => preg_quote($term, '/'))
            ->unique()
            ->values()
            ->all();

        if (empty($searchTerms)) {
            return 0;
        }

        // Build regex pattern for word boundary matching (case-insensitive)
        $pattern = '/\b('.implode('|', $searchTerms).')\b/iu';

        $matches = [];
        if (preg_match_all($pattern, $text, $matches)) {
            return count($matches[0]);
        }

        return 0;
    }

    /**
     * Clear all mentions for a chat message.
     */
    private function clearMentions(int $messageId): void
    {
        DB::table('chat_message_mentions')
            ->where('chat_message_id', $messageId)
            ->delete();
    }

    /**
     * Update mention records for a chat message.
     *
     * @param  array<int, array{count: int}>  $mentionData
     */
    private function updateMentions(int $messageId, array $mentionData): void
    {
        // Get existing mentions
        $existingEntryIds = DB::table('chat_message_mentions')
            ->where('chat_message_id', $messageId)
            ->pluck('codex_entry_id')
            ->all();

        $newEntryIds = array_keys($mentionData);

        // Delete removed mentions
        $toDelete = array_diff($existingEntryIds, $newEntryIds);
        if (! empty($toDelete)) {
            DB::table('chat_message_mentions')
                ->where('chat_message_id', $messageId)
                ->whereIn('codex_entry_id', $toDelete)
                ->delete();
        }

        // Update or create mentions
        foreach ($mentionData as $entryId => $data) {
            DB::table('chat_message_mentions')
                ->updateOrInsert(
                    [
                        'chat_message_id' => $messageId,
                        'codex_entry_id' => $entryId,
                    ],
                    [
                        'mention_count' => $data['count'],
                        'updated_at' => now(),
                    ]
                );
        }
    }
}
