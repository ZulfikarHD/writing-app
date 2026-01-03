<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\CodexEntry;
use App\Models\CodexProgression;
use App\Models\Novel;
use App\Models\Scene;
use App\Models\SceneSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for FG-06.3: Writing Tools features
 * - Scene beats with completion tracking
 * - Subplot assignment via progressions
 * - Scene sections with is_completed field
 */
class WritingToolsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Novel $novel;

    private Chapter $chapter;

    private Scene $scene;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->novel = Novel::factory()->create(['user_id' => $this->user->id]);
        $this->chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        $this->scene = Scene::factory()->create(['chapter_id' => $this->chapter->id]);
    }

    // ==================== Beat Section Tests ====================

    public function test_can_create_beat_section(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/scenes/{$this->scene->id}/sections", [
                'type' => 'beat',
                'title' => 'Scene Beats',
                'content' => [
                    'type' => 'doc',
                    'content' => [
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => '- Character enters the room']]],
                    ],
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('section.type', 'beat')
            ->assertJsonPath('section.is_completed', false);

        $this->assertDatabaseHas('scene_sections', [
            'scene_id' => $this->scene->id,
            'type' => 'beat',
            'is_completed' => false,
        ]);
    }

    public function test_can_mark_beat_section_as_completed(): void
    {
        $section = SceneSection::factory()->create([
            'scene_id' => $this->scene->id,
            'type' => 'beat',
            'is_completed' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/sections/{$section->id}", [
                'is_completed' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('section.is_completed', true);

        $this->assertDatabaseHas('scene_sections', [
            'id' => $section->id,
            'is_completed' => true,
        ]);
    }

    public function test_beat_section_has_correct_default_ai_visibility(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/scenes/{$this->scene->id}/sections", [
                'type' => 'beat',
                'title' => 'Planning Beats',
            ]);

        $response->assertCreated();

        // Beat sections should be visible to AI by default (unlike notes)
        $this->assertDatabaseHas('scene_sections', [
            'scene_id' => $this->scene->id,
            'type' => 'beat',
            'exclude_from_ai' => false,
        ]);
    }

    // ==================== Subplot Assignment Tests ====================

    public function test_can_get_novel_subplots(): void
    {
        // Create subplot codex entries
        $subplot1 = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'type' => 'subplot',
            'name' => 'Romance Subplot',
        ]);
        $subplot2 = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'type' => 'subplot',
            'name' => 'Mystery Subplot',
        ]);
        // Create a non-subplot entry (should not appear)
        CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'type' => 'character',
            'name' => 'Main Character',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/codex/subplots");

        $response->assertOk()
            ->assertJsonCount(2, 'subplots')
            ->assertJsonFragment(['name' => 'Romance Subplot'])
            ->assertJsonFragment(['name' => 'Mystery Subplot'])
            ->assertJsonMissing(['name' => 'Main Character']);
    }

    public function test_can_assign_subplot_to_scene(): void
    {
        $subplot = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'type' => 'subplot',
            'name' => 'Romance Subplot',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/scenes/{$this->scene->id}/subplots", [
                'codex_entry_id' => $subplot->id,
                'note' => 'First meeting of the lovers',
            ]);

        $response->assertCreated()
            ->assertJsonPath('progression.codex_entry_id', $subplot->id)
            ->assertJsonPath('progression.scene_id', $this->scene->id);

        $this->assertDatabaseHas('codex_progressions', [
            'codex_entry_id' => $subplot->id,
            'scene_id' => $this->scene->id,
        ]);
    }

    public function test_cannot_assign_non_subplot_entry_to_scene(): void
    {
        $character = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'type' => 'character',
            'name' => 'Main Character',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/scenes/{$this->scene->id}/subplots", [
                'codex_entry_id' => $character->id,
            ]);

        $response->assertNotFound();
    }

    public function test_can_get_scene_subplots(): void
    {
        $subplot1 = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'type' => 'subplot',
            'name' => 'Romance Subplot',
        ]);
        $subplot2 = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'type' => 'subplot',
            'name' => 'Mystery Subplot',
        ]);

        // Assign subplot1 to scene
        CodexProgression::create([
            'codex_entry_id' => $subplot1->id,
            'scene_id' => $this->scene->id,
            'note' => 'Romance begins',
            'mode' => 'addition',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/scenes/{$this->scene->id}/subplots");

        $response->assertOk()
            ->assertJsonCount(1, 'subplots')
            ->assertJsonFragment(['name' => 'Romance Subplot'])
            ->assertJsonMissing(['name' => 'Mystery Subplot']);
    }

    public function test_can_remove_subplot_from_scene(): void
    {
        $subplot = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'type' => 'subplot',
            'name' => 'Romance Subplot',
        ]);

        $progression = CodexProgression::create([
            'codex_entry_id' => $subplot->id,
            'scene_id' => $this->scene->id,
            'note' => 'Romance begins',
            'mode' => 'addition',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/scenes/{$this->scene->id}/subplots/{$subplot->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('codex_progressions', [
            'id' => $progression->id,
        ]);
    }

    public function test_reassigning_subplot_updates_existing_progression(): void
    {
        $subplot = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'type' => 'subplot',
            'name' => 'Romance Subplot',
        ]);

        // First assignment
        $this->actingAs($this->user)
            ->postJson("/api/scenes/{$this->scene->id}/subplots", [
                'codex_entry_id' => $subplot->id,
                'note' => 'Original note',
            ]);

        // Re-assign with different note
        $response = $this->actingAs($this->user)
            ->postJson("/api/scenes/{$this->scene->id}/subplots", [
                'codex_entry_id' => $subplot->id,
                'note' => 'Updated note',
            ]);

        $response->assertOk();

        // Should only have one progression
        $this->assertEquals(1, CodexProgression::where('codex_entry_id', $subplot->id)
            ->where('scene_id', $this->scene->id)
            ->count());
    }

    public function test_cannot_assign_subplot_from_other_novel(): void
    {
        $otherNovel = Novel::factory()->create(['user_id' => $this->user->id]);
        $otherSubplot = CodexEntry::factory()->create([
            'novel_id' => $otherNovel->id,
            'type' => 'subplot',
            'name' => 'Other Novel Subplot',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/scenes/{$this->scene->id}/subplots", [
                'codex_entry_id' => $otherSubplot->id,
            ]);

        $response->assertNotFound();
    }

    // ==================== Authorization Tests ====================

    public function test_cannot_access_other_users_scene_subplots(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $otherChapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);
        $otherScene = Scene::factory()->create(['chapter_id' => $otherChapter->id]);

        $subplot = CodexEntry::factory()->create([
            'novel_id' => $otherNovel->id,
            'type' => 'subplot',
            'name' => 'Their Subplot',
        ]);

        // Try to get subplots
        $this->actingAs($this->user)
            ->getJson("/api/scenes/{$otherScene->id}/subplots")
            ->assertForbidden();

        // Try to assign subplot
        $this->actingAs($this->user)
            ->postJson("/api/scenes/{$otherScene->id}/subplots", [
                'codex_entry_id' => $subplot->id,
            ])
            ->assertForbidden();
    }

    public function test_cannot_access_other_users_novel_subplots(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user)
            ->getJson("/api/novels/{$otherNovel->id}/codex/subplots")
            ->assertForbidden();
    }
}
