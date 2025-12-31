<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Scene;
use App\Models\User;
use App\Models\UserOnboardingState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class EditorTest extends TestCase
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

    public function test_user_can_access_editor(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        $scene = Scene::factory()->create(['chapter_id' => $chapter->id]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/write');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Editor/Index')
                ->has('novel')
                ->has('chapters')
                ->has('activeScene')
            );
    }

    public function test_editor_loads_novel_data(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create(['chapter_id' => $chapter->id]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/write');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('novel.id', $this->novel->id)
                ->where('novel.title', $this->novel->title)
            );
    }

    public function test_editor_loads_chapters_with_scenes(): void
    {
        $chapter = Chapter::factory()->create([
            'novel_id' => $this->novel->id,
            'title' => 'Test Chapter',
        ]);

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Test Scene',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/write');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('chapters', 1)
                ->where('chapters.0.title', 'Test Chapter')
                ->where('chapters.0.scenes.0.title', 'Test Scene')
            );
    }

    public function test_editor_creates_default_chapter_and_scene_if_none_exist(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/write');

        $response->assertOk();

        $this->assertDatabaseHas('chapters', [
            'novel_id' => $this->novel->id,
            'title' => 'Chapter 1',
        ]);

        $chapter = $this->novel->chapters()->first();
        $this->assertDatabaseHas('scenes', [
            'chapter_id' => $chapter->id,
            'title' => 'Scene 1',
        ]);
    }

    public function test_user_can_access_specific_scene(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Specific Scene',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/write/'.$scene->id);

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('activeScene.id', $scene->id)
                ->where('activeScene.title', 'Specific Scene')
            );
    }

    public function test_editor_returns_active_scene_content(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);

        $content = [
            'type' => 'doc',
            'content' => [
                ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Test content']]],
            ],
        ];

        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'content' => $content,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/write/'.$scene->id);

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('activeScene.content')
            );
    }

    public function test_user_cannot_access_other_users_novel_editor(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$otherNovel->id.'/write');

        $response->assertForbidden();
    }

    public function test_editor_excludes_archived_scenes(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);

        $activeScene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Active Scene',
            'archived_at' => null,
        ]);

        $archivedScene = Scene::factory()->archived()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Archived Scene',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/write');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('chapters.0.scenes', 1)
                ->where('chapters.0.scenes.0.title', 'Active Scene')
            );
    }

    public function test_editor_marks_onboarding_as_toured(): void
    {
        UserOnboardingState::factory()->create([
            'user_id' => $this->user->id,
            'editor_toured' => false,
        ]);

        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create(['chapter_id' => $chapter->id]);

        $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/write');

        $this->assertDatabaseHas('user_onboarding_states', [
            'user_id' => $this->user->id,
            'editor_toured' => true,
        ]);
    }

    public function test_guest_cannot_access_editor(): void
    {
        $response = $this->get('/novels/'.$this->novel->id.'/write');

        $response->assertRedirect('/login');
    }

    public function test_scenes_are_ordered_by_position(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);

        $scene3 = Scene::factory()->position(2)->create([
            'chapter_id' => $chapter->id,
            'title' => 'Scene 3',
        ]);

        $scene1 = Scene::factory()->position(0)->create([
            'chapter_id' => $chapter->id,
            'title' => 'Scene 1',
        ]);

        $scene2 = Scene::factory()->position(1)->create([
            'chapter_id' => $chapter->id,
            'title' => 'Scene 2',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/write');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('chapters.0.scenes.0.title', 'Scene 1')
                ->where('chapters.0.scenes.1.title', 'Scene 2')
                ->where('chapters.0.scenes.2.title', 'Scene 3')
            );
    }

    public function test_chapters_are_ordered_by_position(): void
    {
        $chapter3 = Chapter::factory()->position(2)->create([
            'novel_id' => $this->novel->id,
            'title' => 'Chapter 3',
        ]);

        $chapter1 = Chapter::factory()->position(0)->create([
            'novel_id' => $this->novel->id,
            'title' => 'Chapter 1',
        ]);

        $chapter2 = Chapter::factory()->position(1)->create([
            'novel_id' => $this->novel->id,
            'title' => 'Chapter 2',
        ]);

        // Add scenes to chapters
        Scene::factory()->create(['chapter_id' => $chapter1->id]);
        Scene::factory()->create(['chapter_id' => $chapter2->id]);
        Scene::factory()->create(['chapter_id' => $chapter3->id]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/write');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('chapters.0.title', 'Chapter 1')
                ->where('chapters.1.title', 'Chapter 2')
                ->where('chapters.2.title', 'Chapter 3')
            );
    }
}
