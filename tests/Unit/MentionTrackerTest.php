<?php

namespace Tests\Unit;

use App\Models\Chapter;
use App\Models\CodexAlias;
use App\Models\CodexEntry;
use App\Models\CodexMention;
use App\Models\Novel;
use App\Models\Scene;
use App\Models\User;
use App\Services\Codex\MentionTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for MentionTracker service.
 *
 * Sprint 16: Added tests for summary scanning (F-12.1.4).
 */
class MentionTrackerTest extends TestCase
{
    use RefreshDatabase;

    private MentionTracker $tracker;

    private Novel $novel;

    private Chapter $chapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tracker = new MentionTracker;

        $user = User::factory()->create();
        $this->novel = Novel::factory()->create(['user_id' => $user->id]);
        $this->chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
    }

    // ==================== Basic Mention Detection ====================

    public function test_detects_mentions_in_content(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena',
            'is_tracking_enabled' => true,
        ]);

        $scene = Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'content' => $this->buildContent('Elena walked into the room.'),
        ]);

        $this->tracker->scanScene($scene);

        $this->assertDatabaseHas('codex_mentions', [
            'codex_entry_id' => $entry->id,
            'scene_id' => $scene->id,
            'mention_count' => 1,
        ]);
    }

    public function test_counts_multiple_mentions(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena',
            'is_tracking_enabled' => true,
        ]);

        $scene = Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'content' => $this->buildContent('Elena smiled. Elena waved. Elena left.'),
        ]);

        $this->tracker->scanScene($scene);

        $mention = CodexMention::where('scene_id', $scene->id)
            ->where('codex_entry_id', $entry->id)
            ->first();

        $this->assertNotNull($mention);
        $this->assertEquals(3, $mention->mention_count);
    }

    public function test_detects_mentions_by_alias(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena Blackwood',
            'is_tracking_enabled' => true,
        ]);

        CodexAlias::create([
            'codex_entry_id' => $entry->id,
            'alias' => 'The Shadow Mage',
        ]);

        $scene = Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'content' => $this->buildContent('The Shadow Mage appeared.'),
        ]);

        $this->tracker->scanScene($scene);

        $this->assertDatabaseHas('codex_mentions', [
            'codex_entry_id' => $entry->id,
            'scene_id' => $scene->id,
        ]);
    }

    public function test_ignores_entries_with_tracking_disabled(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena',
            'is_tracking_enabled' => false,
        ]);

        $scene = Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'content' => $this->buildContent('Elena is mentioned.'),
        ]);

        $this->tracker->scanScene($scene);

        $this->assertDatabaseMissing('codex_mentions', [
            'codex_entry_id' => $entry->id,
        ]);
    }

    public function test_clears_mentions_when_content_removed(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena',
            'is_tracking_enabled' => true,
        ]);

        $scene = Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'content' => $this->buildContent('Elena is here.'),
        ]);

        $this->tracker->scanScene($scene);

        $this->assertDatabaseHas('codex_mentions', [
            'codex_entry_id' => $entry->id,
            'scene_id' => $scene->id,
        ]);

        // Clear content
        $scene->content = null;
        $scene->save();

        $this->tracker->scanScene($scene);

        $this->assertDatabaseMissing('codex_mentions', [
            'codex_entry_id' => $entry->id,
            'scene_id' => $scene->id,
        ]);
    }

    // ==================== Sprint 16: Summary Scanning Tests ====================

    public function test_detects_mentions_in_summary(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena',
            'is_tracking_enabled' => true,
        ]);

        $scene = Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'content' => null,
            'summary' => 'Elena arrives at the castle.',
        ]);

        $this->tracker->scanScene($scene);

        $this->assertDatabaseHas('codex_mentions', [
            'codex_entry_id' => $entry->id,
            'scene_id' => $scene->id,
            'source' => 'summary',
        ]);
    }

    public function test_combines_mentions_from_content_and_summary(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena',
            'is_tracking_enabled' => true,
        ]);

        $scene = Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'content' => $this->buildContent('Elena walked in.'),
            'summary' => 'Elena meets Marcus.',
        ]);

        $this->tracker->scanScene($scene);

        $mention = CodexMention::where('scene_id', $scene->id)
            ->where('codex_entry_id', $entry->id)
            ->first();

        $this->assertNotNull($mention);
        $this->assertEquals(2, $mention->mention_count); // 1 from content + 1 from summary
        $this->assertEquals('both', $mention->source);
    }

    public function test_source_is_content_when_only_in_content(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena',
            'is_tracking_enabled' => true,
        ]);

        $scene = Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'content' => $this->buildContent('Elena smiled.'),
            'summary' => 'A scene without the character name.',
        ]);

        $this->tracker->scanScene($scene);

        $this->assertDatabaseHas('codex_mentions', [
            'codex_entry_id' => $entry->id,
            'source' => 'content',
        ]);
    }

    public function test_source_is_summary_when_only_in_summary(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena',
            'is_tracking_enabled' => true,
        ]);

        $scene = Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'content' => $this->buildContent('Someone walked in.'),
            'summary' => 'Elena arrives at the castle.',
        ]);

        $this->tracker->scanScene($scene);

        $this->assertDatabaseHas('codex_mentions', [
            'codex_entry_id' => $entry->id,
            'source' => 'summary',
        ]);
    }

    public function test_clears_mentions_when_both_content_and_summary_empty(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena',
            'is_tracking_enabled' => true,
        ]);

        $scene = Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'content' => $this->buildContent('Elena is here.'),
            'summary' => 'Elena arrives.',
        ]);

        $this->tracker->scanScene($scene);

        $this->assertDatabaseHas('codex_mentions', [
            'codex_entry_id' => $entry->id,
        ]);

        // Clear both
        $scene->content = null;
        $scene->summary = null;
        $scene->save();

        $this->tracker->scanScene($scene);

        $this->assertDatabaseMissing('codex_mentions', [
            'codex_entry_id' => $entry->id,
            'scene_id' => $scene->id,
        ]);
    }

    public function test_updates_source_when_mention_location_changes(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena',
            'is_tracking_enabled' => true,
        ]);

        $scene = Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'content' => $this->buildContent('Elena walked.'),
            'summary' => null,
        ]);

        $this->tracker->scanScene($scene);

        $this->assertDatabaseHas('codex_mentions', [
            'codex_entry_id' => $entry->id,
            'source' => 'content',
        ]);

        // Move mention to summary only
        $scene->content = $this->buildContent('Someone walked.');
        $scene->summary = 'Elena arrives.';
        $scene->save();

        $this->tracker->scanScene($scene);

        $this->assertDatabaseHas('codex_mentions', [
            'codex_entry_id' => $entry->id,
            'scene_id' => $scene->id,
            'source' => 'summary',
        ]);
    }

    // ==================== Case Sensitivity Tests ====================

    public function test_detection_is_case_insensitive(): void
    {
        $entry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Elena',
            'is_tracking_enabled' => true,
        ]);

        $scene = Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'content' => $this->buildContent('ELENA shouted. elena whispered.'),
        ]);

        $this->tracker->scanScene($scene);

        $mention = CodexMention::where('scene_id', $scene->id)
            ->where('codex_entry_id', $entry->id)
            ->first();

        $this->assertNotNull($mention);
        $this->assertEquals(2, $mention->mention_count);
    }

    // ==================== Helper Methods ====================

    /**
     * Build TipTap content structure from plain text.
     *
     * @return array<string, mixed>
     */
    private function buildContent(string $text): array
    {
        return [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => $text],
                    ],
                ],
            ],
        ];
    }
}
