<?php

namespace App\Services\Plan;

use App\Models\Novel;
use Illuminate\Support\Collection;

class MatrixDataBuilder
{
    /**
     * Build matrix data for the Plan Matrix view.
     *
     * @return array{
     *     scenes: array,
     *     entries: array,
     *     mentions: array,
     *     characters: array
     * }
     */
    public function build(Novel $novel, string $show = 'entries', ?string $entryType = null): array
    {
        // Get all active scenes with their chapter info
        $scenes = $this->getScenes($novel);

        // Get data based on the show mode
        $entries = [];
        $mentions = [];
        $characters = $this->getCharacters($novel);

        if ($show === 'entries') {
            $entries = $this->getEntries($novel, $entryType);
            $mentions = $this->getMentions($novel, $entries->pluck('id')->toArray());
        }

        return [
            'scenes' => $scenes->values()->toArray(),
            'entries' => $entries instanceof Collection ? $entries->values()->toArray() : [],
            'mentions' => $mentions instanceof Collection ? $mentions->values()->toArray() : [],
            'characters' => $characters->values()->toArray(),
        ];
    }

    /**
     * Get all active scenes with chapter info.
     */
    private function getScenes(Novel $novel): Collection
    {
        return $novel->chapters()
            ->with([
                'scenes' => fn ($q) => $q->active()->orderBy('position')->with('labels'),
            ])
            ->orderBy('position')
            ->get()
            ->flatMap(function ($chapter) {
                return $chapter->scenes->map(fn ($scene) => [
                    'id' => $scene->id,
                    'title' => $scene->title,
                    'chapter_id' => $chapter->id,
                    'chapter_title' => $chapter->title,
                    'chapter_position' => $chapter->position,
                    'position' => $scene->position,
                    'status' => $scene->status,
                    'pov_character_id' => $scene->pov_character_id,
                    'labels' => $scene->labels->map(fn ($l) => [
                        'id' => $l->id,
                        'name' => $l->name,
                        'color' => $l->color,
                    ])->toArray(),
                ]);
            });
    }

    /**
     * Get codex entries optionally filtered by type.
     */
    private function getEntries(Novel $novel, ?string $entryType): Collection
    {
        $query = $novel->codexEntries()->active();

        if ($entryType && $entryType !== 'all') {
            $query->where('type', $entryType);
        }

        return $query->orderBy('name')
            ->get()
            ->map(fn ($entry) => [
                'id' => $entry->id,
                'name' => $entry->name,
                'type' => $entry->type,
            ]);
    }

    /**
     * Get all mentions for the specified entry IDs.
     *
     * @param  array<int>  $entryIds
     */
    private function getMentions(Novel $novel, array $entryIds): Collection
    {
        if (empty($entryIds)) {
            return collect();
        }

        // Get scene IDs for this novel
        $sceneIds = $novel->scenes()->pluck('scenes.id')->toArray();

        if (empty($sceneIds)) {
            return collect();
        }

        return \App\Models\CodexMention::whereIn('codex_entry_id', $entryIds)
            ->whereIn('scene_id', $sceneIds)
            ->get()
            ->map(fn ($mention) => [
                'scene_id' => $mention->scene_id,
                'entry_id' => $mention->codex_entry_id,
                'mention_count' => $mention->mention_count,
                'source' => $mention->source,
            ]);
    }

    /**
     * Get all characters for POV display.
     */
    private function getCharacters(Novel $novel): Collection
    {
        return $novel->codexEntries()
            ->active()
            ->where('type', 'character')
            ->orderBy('name')
            ->get()
            ->map(fn ($entry) => [
                'id' => $entry->id,
                'name' => $entry->name,
            ]);
    }
}
