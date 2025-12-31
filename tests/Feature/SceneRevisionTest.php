<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Scene;
use App\Models\SceneRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SceneRevisionTest extends TestCase
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

    public function test_user_can_list_scene_revisions(): void
    {
        $revisions = SceneRevision::factory(3)->create([
            'scene_id' => $this->scene->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/scenes/'.$this->scene->id.'/revisions');

        $response->assertOk()
            ->assertJsonCount(3, 'revisions')
            ->assertJsonStructure([
                'revisions' => [
                    '*' => ['id', 'word_count', 'created_at'],
                ],
            ]);
    }

    public function test_revisions_are_returned_in_descending_order(): void
    {
        // Create revisions with specific timestamps
        $oldRevision = SceneRevision::factory()->create([
            'scene_id' => $this->scene->id,
            'created_at' => now()->subHours(2),
        ]);

        $newRevision = SceneRevision::factory()->create([
            'scene_id' => $this->scene->id,
            'created_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/scenes/'.$this->scene->id.'/revisions');

        $response->assertOk();
        $revisions = $response->json('revisions');

        // Newer revision should come first
        $this->assertEquals($newRevision->id, $revisions[0]['id']);
        $this->assertEquals($oldRevision->id, $revisions[1]['id']);
    }

    public function test_user_cannot_list_revisions_for_other_users_scene(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $otherChapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);
        $otherScene = Scene::factory()->create(['chapter_id' => $otherChapter->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/scenes/'.$otherScene->id.'/revisions');

        $response->assertForbidden();
    }

    public function test_user_can_create_manual_revision(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/scenes/'.$this->scene->id.'/revisions');

        $response->assertCreated()
            ->assertJsonStructure([
                'revision' => [
                    'id',
                    'word_count',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('scene_revisions', [
            'scene_id' => $this->scene->id,
        ]);
    }

    public function test_revision_captures_current_content(): void
    {
        $content = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Specific test content'],
                    ],
                ],
            ],
        ];

        $this->scene->update([
            'content' => $content,
            'word_count' => 3,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/scenes/'.$this->scene->id.'/revisions');

        $response->assertCreated();

        $revision = SceneRevision::find($response->json('revision.id'));
        $this->assertEquals($content, $revision->content);
        $this->assertEquals(3, $revision->word_count);
    }

    public function test_user_cannot_create_revision_for_other_users_scene(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $otherChapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);
        $otherScene = Scene::factory()->create(['chapter_id' => $otherChapter->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/scenes/'.$otherScene->id.'/revisions');

        $response->assertForbidden();
    }

    public function test_user_can_restore_revision(): void
    {
        $oldContent = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Old content to restore'],
                    ],
                ],
            ],
        ];

        $revision = SceneRevision::factory()->create([
            'scene_id' => $this->scene->id,
            'content' => $oldContent,
            'word_count' => 4,
        ]);

        // Update scene to have different content
        $this->scene->update([
            'content' => [
                'type' => 'doc',
                'content' => [
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'New different content']]],
                ],
            ],
            'word_count' => 3,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/scenes/'.$this->scene->id.'/revisions/'.$revision->id.'/restore');

        $response->assertOk()
            ->assertJsonPath('scene.word_count', 4);

        $this->scene->refresh();
        $this->assertEquals($oldContent, $this->scene->content);
        $this->assertEquals(4, $this->scene->word_count);
    }

    public function test_restoring_revision_creates_backup_of_current_content(): void
    {
        $currentContent = $this->scene->content;
        $revisionCount = $this->scene->revisions()->count();

        $revision = SceneRevision::factory()->create([
            'scene_id' => $this->scene->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/scenes/'.$this->scene->id.'/revisions/'.$revision->id.'/restore');

        $response->assertOk();

        // A new revision should have been created as backup
        $this->assertEquals($revisionCount + 2, $this->scene->revisions()->count());

        // The latest revision (backup) should have the previous content
        $backupRevision = $this->scene->revisions()->latest('created_at')->first();
        $this->assertEquals($currentContent, $backupRevision->content);
    }

    public function test_user_cannot_restore_other_users_revision(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $otherChapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);
        $otherScene = Scene::factory()->create(['chapter_id' => $otherChapter->id]);
        $otherRevision = SceneRevision::factory()->create(['scene_id' => $otherScene->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/scenes/'.$otherScene->id.'/revisions/'.$otherRevision->id.'/restore');

        $response->assertForbidden();
    }

    public function test_revisions_limit_is_enforced(): void
    {
        // Create 60 revisions
        SceneRevision::factory(60)->create(['scene_id' => $this->scene->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/scenes/'.$this->scene->id.'/revisions');

        $response->assertOk();
        // Should be limited to 50
        $this->assertLessThanOrEqual(50, count($response->json('revisions')));
    }

    public function test_scene_model_can_create_revision(): void
    {
        $content = [
            'type' => 'doc',
            'content' => [
                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Test content']]],
            ],
        ];

        $this->scene->update([
            'content' => $content,
            'word_count' => 2,
        ]);

        $revision = $this->scene->createRevision();

        $this->assertInstanceOf(SceneRevision::class, $revision);
        $this->assertEquals($this->scene->id, $revision->scene_id);
        $this->assertEquals($content, $revision->content);
        $this->assertEquals(2, $revision->word_count);
    }

    public function test_deleting_scene_deletes_revisions(): void
    {
        $revisions = SceneRevision::factory(3)->create(['scene_id' => $this->scene->id]);
        $revisionIds = $revisions->pluck('id')->toArray();

        $this->scene->delete();

        foreach ($revisionIds as $id) {
            $this->assertDatabaseMissing('scene_revisions', ['id' => $id]);
        }
    }
}
