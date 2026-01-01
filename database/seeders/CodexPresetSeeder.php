<?php

namespace Database\Seeders;

use App\Models\CodexDetailDefinition;
use App\Models\CodexTag;
use App\Models\Novel;
use Illuminate\Database\Seeder;

/**
 * CodexPresetSeeder - Sprint 14
 *
 * Seeds predefined tags and detail definitions for a novel.
 * This seeder can be run per-novel to initialize the Sprint 14 presets.
 *
 * Usage:
 *   php artisan db:seed --class=CodexPresetSeeder
 *
 * Or programmatically for a specific novel:
 *   (new CodexPresetSeeder)->seedForNovel($novel);
 */
class CodexPresetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed presets for all novels
        Novel::all()->each(function (Novel $novel) {
            $this->seedForNovel($novel);
        });
    }

    /**
     * Seed presets for a specific novel.
     */
    public function seedForNovel(Novel $novel): void
    {
        $this->seedTagsForNovel($novel);
        $this->seedDetailDefinitionsForNovel($novel);
    }

    /**
     * Seed predefined tags for a novel.
     */
    public function seedTagsForNovel(Novel $novel): void
    {
        // Check if predefined tags already exist
        $hasPredefined = CodexTag::where('novel_id', $novel->id)
            ->where('is_predefined', true)
            ->exists();

        if ($hasPredefined) {
            return; // Already seeded
        }

        foreach (CodexTag::PREDEFINED_TAGS as $entryType => $tags) {
            foreach ($tags as $tagData) {
                CodexTag::create([
                    'novel_id' => $novel->id,
                    'name' => $tagData['name'],
                    'color' => $tagData['color'],
                    'entry_type' => $entryType,
                    'is_predefined' => true,
                ]);
            }
        }

        $this->command?->info("Seeded predefined tags for novel: {$novel->title}");
    }

    /**
     * Seed system detail definitions for a novel.
     * Note: Detail definitions are stored in the model constants and don't need DB seeding.
     * This method is here for future extensibility if we want to store them in DB.
     */
    public function seedDetailDefinitionsForNovel(Novel $novel): void
    {
        // System presets are defined in CodexDetailDefinition::SYSTEM_PRESETS
        // and are served directly without DB storage.
        // This allows updates to presets without migrations.

        // If you want to store custom definitions per-novel, do it here:
        // CodexDetailDefinition::firstOrCreate([...])

        $this->command?->info("Detail presets available for novel: {$novel->title}");
    }
}
