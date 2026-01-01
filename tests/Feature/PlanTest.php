<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Scene;
use App\Models\SceneLabel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Novel $novel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->novel = Novel::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_user_can_access_plan_page(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory(3)->create(['chapter_id' => $chapter->id]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/plan');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Plan/Index')
                ->has('novel', fn (Assert $novel) => $novel
                    ->has('id')
                    ->has('title')
                    ->has('word_count')
                )
                ->has('chapters', 1)
                ->has('chapters.0.scenes', 3)
                ->has('acts')
                ->has('labels')
            );
    }

    public function test_user_cannot_access_other_users_plan_page(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$otherNovel->id.'/plan');

        $response->assertForbidden();
    }

    public function test_plan_page_shows_scenes_with_labels(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        $scene = Scene::factory()->create(['chapter_id' => $chapter->id]);
        $label = SceneLabel::factory()->create(['novel_id' => $this->novel->id, 'name' => 'Important']);
        $scene->labels()->attach($label->id);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/plan');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Plan/Index')
                ->has('chapters.0.scenes.0.labels', 1)
                ->where('chapters.0.scenes.0.labels.0.name', 'Important')
            );
    }

    public function test_user_can_search_scenes(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'title' => 'The Dragon Fight',
        ]);
        Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Castle Scene',
        ]);
        Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'title' => 'The Dragon Escapes',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/novels/'.$this->novel->id.'/scenes/search?q=dragon');

        $response->assertOk()
            ->assertJsonCount(2, 'scenes');
    }

    public function test_user_can_filter_scenes_by_status(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'status' => 'draft',
        ]);
        Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'status' => 'completed',
        ]);
        Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/novels/'.$this->novel->id.'/scenes/search?status=completed');

        $response->assertOk()
            ->assertJsonCount(2, 'scenes');
    }

    public function test_user_can_filter_scenes_by_label(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);

        $actionLabel = SceneLabel::factory()->create(['novel_id' => $this->novel->id, 'name' => 'Action']);
        $romanceLabel = SceneLabel::factory()->create(['novel_id' => $this->novel->id, 'name' => 'Romance']);

        $actionScene = Scene::factory()->create(['chapter_id' => $chapter->id]);
        $actionScene->labels()->attach($actionLabel->id);

        $romanceScene = Scene::factory()->create(['chapter_id' => $chapter->id]);
        $romanceScene->labels()->attach($romanceLabel->id);

        $noLabelScene = Scene::factory()->create(['chapter_id' => $chapter->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/novels/'.$this->novel->id.'/scenes/search?label_ids[]='.$actionLabel->id);

        $response->assertOk()
            ->assertJsonCount(1, 'scenes')
            ->assertJsonPath('scenes.0.id', $actionScene->id);
    }

    public function test_user_cannot_search_other_users_scenes(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/novels/'.$otherNovel->id.'/scenes/search?q=test');

        $response->assertForbidden();
    }

    public function test_user_can_duplicate_scene(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Original Scene',
            'content' => ['type' => 'doc', 'content' => []],
            'word_count' => 100,
        ]);

        $label = SceneLabel::factory()->create(['novel_id' => $this->novel->id]);
        $scene->labels()->attach($label->id);

        $response = $this->actingAs($this->user)
            ->postJson('/api/scenes/'.$scene->id.'/duplicate');

        $response->assertCreated()
            ->assertJsonPath('scene.title', 'Original Scene (Copy)');

        $this->assertDatabaseCount('scenes', 2);

        $duplicateId = $response->json('scene.id');
        $this->assertDatabaseHas('scene_label', [
            'scene_id' => $duplicateId,
            'scene_label_id' => $label->id,
        ]);
    }

    public function test_user_cannot_duplicate_other_users_scene(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $chapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);
        $scene = Scene::factory()->create(['chapter_id' => $chapter->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/scenes/'.$scene->id.'/duplicate');

        $response->assertForbidden();
    }

    public function test_archived_scenes_are_not_shown_in_plan(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create(['chapter_id' => $chapter->id, 'title' => 'Active Scene']);
        Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Archived Scene',
            'archived_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/plan');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('chapters.0.scenes', 1)
                ->where('chapters.0.scenes.0.title', 'Active Scene')
            );
    }

    public function test_archived_scenes_are_not_shown_in_search(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Dragon Active',
        ]);
        Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Dragon Archived',
            'archived_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/novels/'.$this->novel->id.'/scenes/search?q=dragon');

        $response->assertOk()
            ->assertJsonCount(1, 'scenes')
            ->assertJsonPath('scenes.0.title', 'Dragon Active');
    }
}
