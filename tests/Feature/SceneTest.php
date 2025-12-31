<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Scene;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SceneTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Novel $novel;

    private Chapter $chapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->novel = Novel::factory()->create(['user_id' => $this->user->id]);
        $this->chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
    }

    public function test_user_can_create_scene(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/chapters/'.$this->chapter->id.'/scenes', [
                'title' => 'Opening Scene',
            ]);

        $response->assertCreated()
            ->assertJsonPath('scene.title', 'Opening Scene')
            ->assertJsonStructure([
                'scene' => [
                    'id',
                    'chapter_id',
                    'title',
                    'position',
                    'status',
                    'word_count',
                    'content',
                ],
            ]);

        $this->assertDatabaseHas('scenes', [
            'chapter_id' => $this->chapter->id,
            'title' => 'Opening Scene',
        ]);
    }

    public function test_scene_has_default_tiptap_content(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/chapters/'.$this->chapter->id.'/scenes', [
                'title' => 'New Scene',
            ]);

        $response->assertCreated();

        $content = $response->json('scene.content');
        $this->assertEquals('doc', $content['type']);
        $this->assertArrayHasKey('content', $content);
    }

    public function test_user_cannot_create_scene_in_other_users_chapter(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $otherChapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/chapters/'.$otherChapter->id.'/scenes', [
                'title' => 'Hacked Scene',
            ]);

        $response->assertForbidden();
    }

    public function test_user_can_get_scene(): void
    {
        $scene = Scene::factory()->create(['chapter_id' => $this->chapter->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/scenes/'.$scene->id);

        $response->assertOk()
            ->assertJsonPath('scene.id', $scene->id)
            ->assertJsonStructure([
                'scene' => [
                    'id',
                    'chapter_id',
                    'title',
                    'content',
                    'summary',
                    'position',
                    'status',
                    'word_count',
                    'subtitle',
                    'notes',
                    'pov_character_id',
                    'exclude_from_ai',
                ],
            ]);
    }

    public function test_user_cannot_get_other_users_scene(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $otherChapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);
        $scene = Scene::factory()->create(['chapter_id' => $otherChapter->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/scenes/'.$scene->id);

        $response->assertForbidden();
    }

    public function test_user_can_update_scene_content_autosave(): void
    {
        $scene = Scene::factory()->empty()->create(['chapter_id' => $this->chapter->id]);

        $newContent = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Once upon a time in a land far away...'],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->patchJson('/api/scenes/'.$scene->id.'/content', [
                'content' => $newContent,
            ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['word_count', 'saved_at']);

        $this->assertDatabaseHas('scenes', [
            'id' => $scene->id,
        ]);

        $scene->refresh();
        $this->assertEquals($newContent, $scene->content);
        $this->assertGreaterThan(0, $scene->word_count);
    }

    public function test_autosave_updates_novel_last_edited_at(): void
    {
        $this->novel->update(['last_edited_at' => now()->subDay()]);
        $scene = Scene::factory()->empty()->create(['chapter_id' => $this->chapter->id]);

        $oldLastEdited = $this->novel->last_edited_at;

        $response = $this->actingAs($this->user)
            ->patchJson('/api/scenes/'.$scene->id.'/content', [
                'content' => [
                    'type' => 'doc',
                    'content' => [['type' => 'paragraph']],
                ],
            ]);

        $response->assertOk();

        $this->novel->refresh();
        $this->assertGreaterThan($oldLastEdited, $this->novel->last_edited_at);
    }

    public function test_user_cannot_update_other_users_scene_content(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $otherChapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);
        $scene = Scene::factory()->create(['chapter_id' => $otherChapter->id]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/scenes/'.$scene->id.'/content', [
                'content' => [
                    'type' => 'doc',
                    'content' => [['type' => 'paragraph']],
                ],
            ]);

        $response->assertForbidden();
    }

    public function test_user_can_update_scene_metadata(): void
    {
        $scene = Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'title' => 'Old Title',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/scenes/'.$scene->id, [
                'title' => 'New Title',
                'status' => 'in_progress',
                'summary' => 'This scene is about...',
            ]);

        $response->assertOk()
            ->assertJsonPath('scene.title', 'New Title')
            ->assertJsonPath('scene.status', 'in_progress');

        $this->assertDatabaseHas('scenes', [
            'id' => $scene->id,
            'title' => 'New Title',
            'status' => 'in_progress',
            'summary' => 'This scene is about...',
        ]);
    }

    public function test_user_can_delete_scene(): void
    {
        $scene = Scene::factory()->create(['chapter_id' => $this->chapter->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/scenes/'.$scene->id);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('scenes', ['id' => $scene->id]);
    }

    public function test_user_can_archive_scene(): void
    {
        $scene = Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'archived_at' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/scenes/'.$scene->id.'/archive');

        $response->assertOk()
            ->assertJson(['success' => true]);

        $scene->refresh();
        $this->assertNotNull($scene->archived_at);
    }

    public function test_user_can_restore_archived_scene(): void
    {
        $scene = Scene::factory()->archived()->create([
            'chapter_id' => $this->chapter->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/scenes/'.$scene->id.'/restore');

        $response->assertOk()
            ->assertJson(['success' => true]);

        $scene->refresh();
        $this->assertNull($scene->archived_at);
    }

    public function test_user_can_reorder_scenes(): void
    {
        $scenes = Scene::factory(3)
            ->sequence(
                ['position' => 0],
                ['position' => 1],
                ['position' => 2],
            )
            ->create(['chapter_id' => $this->chapter->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/chapters/'.$this->chapter->id.'/scenes/reorder', [
                'scenes' => [
                    ['id' => $scenes[2]->id, 'position' => 0],
                    ['id' => $scenes[0]->id, 'position' => 1],
                    ['id' => $scenes[1]->id, 'position' => 2],
                ],
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('scenes', ['id' => $scenes[2]->id, 'position' => 0]);
        $this->assertDatabaseHas('scenes', ['id' => $scenes[0]->id, 'position' => 1]);
        $this->assertDatabaseHas('scenes', ['id' => $scenes[1]->id, 'position' => 2]);
    }

    public function test_scene_position_is_auto_incremented(): void
    {
        Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'position' => 0,
        ]);

        Scene::factory()->create([
            'chapter_id' => $this->chapter->id,
            'position' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/chapters/'.$this->chapter->id.'/scenes', [
                'title' => 'Scene 3',
            ]);

        $response->assertCreated()
            ->assertJsonPath('scene.position', 2);
    }

    public function test_content_validation_requires_doc_type(): void
    {
        $scene = Scene::factory()->create(['chapter_id' => $this->chapter->id]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/scenes/'.$scene->id.'/content', [
                'content' => [
                    'type' => 'invalid',
                    'content' => [],
                ],
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['content.type']);
    }
}
