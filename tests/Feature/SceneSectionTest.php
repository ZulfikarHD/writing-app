<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Scene;
use App\Models\SceneSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SceneSectionTest extends TestCase
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

    // ==================== INDEX TESTS ====================

    public function test_user_can_list_scene_sections(): void
    {
        SceneSection::factory()->count(3)->create(['scene_id' => $this->scene->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/scenes/{$this->scene->id}/sections");

        $response->assertOk()
            ->assertJsonCount(3, 'sections')
            ->assertJsonStructure([
                'sections' => [
                    '*' => [
                        'id',
                        'scene_id',
                        'type',
                        'title',
                        'content',
                        'color',
                        'is_collapsed',
                        'exclude_from_ai',
                        'sort_order',
                        'word_count',
                    ],
                ],
            ]);
    }

    public function test_sections_are_ordered_by_sort_order(): void
    {
        SceneSection::factory()->create(['scene_id' => $this->scene->id, 'sort_order' => 2, 'title' => 'Second']);
        SceneSection::factory()->create(['scene_id' => $this->scene->id, 'sort_order' => 0, 'title' => 'First']);
        SceneSection::factory()->create(['scene_id' => $this->scene->id, 'sort_order' => 1, 'title' => 'Middle']);

        $response = $this->actingAs($this->user)
            ->getJson("/api/scenes/{$this->scene->id}/sections");

        $response->assertOk();
        $titles = collect($response->json('sections'))->pluck('title')->toArray();
        $this->assertEquals(['First', 'Middle', 'Second'], $titles);
    }

    // ==================== CREATE TESTS ====================

    public function test_user_can_create_section(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/scenes/{$this->scene->id}/sections", [
                'type' => 'content',
                'title' => 'Opening paragraph',
            ]);

        $response->assertCreated()
            ->assertJsonPath('section.type', 'content')
            ->assertJsonPath('section.title', 'Opening paragraph')
            ->assertJsonPath('section.scene_id', $this->scene->id);

        $this->assertDatabaseHas('scene_sections', [
            'scene_id' => $this->scene->id,
            'type' => 'content',
            'title' => 'Opening paragraph',
        ]);
    }

    public function test_section_gets_default_color_based_on_type(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/scenes/{$this->scene->id}/sections", [
                'type' => 'note',
            ]);

        $response->assertCreated()
            ->assertJsonPath('section.color', SceneSection::TYPE_COLORS['note']);
    }

    public function test_note_section_is_excluded_from_ai_by_default(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/scenes/{$this->scene->id}/sections", [
                'type' => 'note',
            ]);

        $response->assertCreated()
            ->assertJsonPath('section.exclude_from_ai', true);
    }

    public function test_content_section_is_included_in_ai_by_default(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/scenes/{$this->scene->id}/sections", [
                'type' => 'content',
            ]);

        $response->assertCreated()
            ->assertJsonPath('section.exclude_from_ai', false);
    }

    public function test_section_gets_auto_incremented_sort_order(): void
    {
        SceneSection::factory()->create(['scene_id' => $this->scene->id, 'sort_order' => 0]);
        SceneSection::factory()->create(['scene_id' => $this->scene->id, 'sort_order' => 1]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/scenes/{$this->scene->id}/sections", [
                'type' => 'content',
            ]);

        $response->assertCreated()
            ->assertJsonPath('section.sort_order', 2);
    }

    // ==================== UPDATE TESTS ====================

    public function test_user_can_update_section(): void
    {
        $section = SceneSection::factory()->create(['scene_id' => $this->scene->id]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/sections/{$section->id}", [
                'title' => 'Updated title',
                'type' => 'note',
            ]);

        $response->assertOk()
            ->assertJsonPath('section.title', 'Updated title')
            ->assertJsonPath('section.type', 'note');

        $this->assertDatabaseHas('scene_sections', [
            'id' => $section->id,
            'title' => 'Updated title',
            'type' => 'note',
        ]);
    }

    public function test_user_can_update_section_content(): void
    {
        $section = SceneSection::factory()->create(['scene_id' => $this->scene->id]);

        $newContent = [
            'type' => 'doc',
            'content' => [
                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'New content']]],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->patchJson("/api/sections/{$section->id}", [
                'content' => $newContent,
            ]);

        $response->assertOk();
        $section->refresh();
        $this->assertEquals($newContent, $section->content);
    }

    // ==================== DELETE TESTS ====================

    public function test_user_can_delete_section(): void
    {
        $section = SceneSection::factory()->create(['scene_id' => $this->scene->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/sections/{$section->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('scene_sections', ['id' => $section->id]);
    }

    // ==================== REORDER TESTS ====================

    public function test_user_can_reorder_sections(): void
    {
        $section1 = SceneSection::factory()->create(['scene_id' => $this->scene->id, 'sort_order' => 0]);
        $section2 = SceneSection::factory()->create(['scene_id' => $this->scene->id, 'sort_order' => 1]);
        $section3 = SceneSection::factory()->create(['scene_id' => $this->scene->id, 'sort_order' => 2]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/scenes/{$this->scene->id}/sections/reorder", [
                'sections' => [
                    ['id' => $section1->id, 'sort_order' => 2],
                    ['id' => $section2->id, 'sort_order' => 0],
                    ['id' => $section3->id, 'sort_order' => 1],
                ],
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals(2, $section1->fresh()->sort_order);
        $this->assertEquals(0, $section2->fresh()->sort_order);
        $this->assertEquals(1, $section3->fresh()->sort_order);
    }

    // ==================== TOGGLE TESTS ====================

    public function test_user_can_toggle_section_collapse(): void
    {
        $section = SceneSection::factory()->create([
            'scene_id' => $this->scene->id,
            'is_collapsed' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/sections/{$section->id}/toggle-collapse");

        $response->assertOk()
            ->assertJsonPath('section.is_collapsed', true);

        // Toggle back
        $response = $this->actingAs($this->user)
            ->postJson("/api/sections/{$section->id}/toggle-collapse");

        $response->assertOk()
            ->assertJsonPath('section.is_collapsed', false);
    }

    public function test_user_can_toggle_section_ai_visibility(): void
    {
        $section = SceneSection::factory()->create([
            'scene_id' => $this->scene->id,
            'exclude_from_ai' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/sections/{$section->id}/toggle-ai-visibility");

        $response->assertOk()
            ->assertJsonPath('section.exclude_from_ai', true);
    }

    // ==================== DISSOLVE TESTS ====================

    public function test_user_can_dissolve_section(): void
    {
        $content = [
            'type' => 'doc',
            'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Dissolved content']]]],
        ];

        $section = SceneSection::factory()->create([
            'scene_id' => $this->scene->id,
            'content' => $content,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/sections/{$section->id}/dissolve");

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('dissolved_content', $content);

        $this->assertDatabaseMissing('scene_sections', ['id' => $section->id]);
    }

    // ==================== DUPLICATE TESTS ====================

    public function test_user_can_duplicate_section(): void
    {
        $section = SceneSection::factory()->create([
            'scene_id' => $this->scene->id,
            'title' => 'Original',
            'type' => 'content',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/sections/{$section->id}/duplicate");

        $response->assertCreated()
            ->assertJsonPath('section.title', 'Original (Copy)')
            ->assertJsonPath('section.type', 'content');

        $this->assertDatabaseCount('scene_sections', 2);
    }

    // ==================== AUTHORIZATION TESTS ====================

    public function test_user_cannot_access_sections_from_other_users_scene(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $otherChapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);
        $otherScene = Scene::factory()->create(['chapter_id' => $otherChapter->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/scenes/{$otherScene->id}/sections");

        $response->assertForbidden();
    }

    public function test_user_cannot_create_section_in_other_users_scene(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $otherChapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);
        $otherScene = Scene::factory()->create(['chapter_id' => $otherChapter->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/scenes/{$otherScene->id}/sections", [
                'type' => 'content',
            ]);

        $response->assertForbidden();
    }

    public function test_user_cannot_update_other_users_section(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $otherChapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);
        $otherScene = Scene::factory()->create(['chapter_id' => $otherChapter->id]);
        $otherSection = SceneSection::factory()->create(['scene_id' => $otherScene->id]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/sections/{$otherSection->id}", [
                'title' => 'Hacked',
            ]);

        $response->assertForbidden();
    }

    public function test_user_cannot_delete_other_users_section(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $otherChapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);
        $otherScene = Scene::factory()->create(['chapter_id' => $otherChapter->id]);
        $otherSection = SceneSection::factory()->create(['scene_id' => $otherScene->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/sections/{$otherSection->id}");

        $response->assertForbidden();
    }

    // ==================== WORD COUNT TESTS ====================

    public function test_section_word_count_is_calculated(): void
    {
        $content = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'This is a test sentence with exactly ten words here.'],
                    ],
                ],
            ],
        ];

        $section = SceneSection::factory()->create([
            'scene_id' => $this->scene->id,
            'content' => $content,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/sections/{$section->id}");

        $response->assertOk()
            ->assertJsonPath('section.word_count', 10);
    }

    // ==================== MODEL TESTS ====================

    public function test_scene_has_sections_relationship(): void
    {
        SceneSection::factory()->count(3)->create(['scene_id' => $this->scene->id]);

        $this->assertCount(3, $this->scene->sections);
    }

    public function test_ai_visible_sections_filters_excluded_sections(): void
    {
        SceneSection::factory()->create(['scene_id' => $this->scene->id, 'exclude_from_ai' => false]);
        SceneSection::factory()->create(['scene_id' => $this->scene->id, 'exclude_from_ai' => false]);
        SceneSection::factory()->create(['scene_id' => $this->scene->id, 'exclude_from_ai' => true]);

        $this->assertCount(3, $this->scene->sections);
        $this->assertCount(2, $this->scene->aiVisibleSections);
    }

    public function test_exportable_sections_filters_by_type(): void
    {
        SceneSection::factory()->content()->create(['scene_id' => $this->scene->id]);
        SceneSection::factory()->content()->create(['scene_id' => $this->scene->id]);
        SceneSection::factory()->note()->create(['scene_id' => $this->scene->id]);
        SceneSection::factory()->alternative()->create(['scene_id' => $this->scene->id]);

        $this->assertCount(4, $this->scene->sections);
        $this->assertCount(2, $this->scene->exportableSections);
    }
}
