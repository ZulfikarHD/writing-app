<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Scene;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Novel $novel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->user = User::factory()->create();
        $this->novel = Novel::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_user_can_access_workspace(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create(['chapter_id' => $chapter->id]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/workspace');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Workspace/Index')
                ->has('novel')
                ->has('chapters')
                ->has('activeScene')
                ->has('acts')
                ->has('labels')
            );
    }

    public function test_workspace_loads_novel_data(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create(['chapter_id' => $chapter->id]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/workspace');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('novel.id', $this->novel->id)
                ->where('novel.title', $this->novel->title)
            );
    }

    public function test_workspace_loads_chapters_with_scenes(): void
    {
        $chapter = Chapter::factory()->create([
            'novel_id' => $this->novel->id,
            'title' => 'Test Chapter',
        ]);

        Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Test Scene',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/workspace');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('chapters', 1)
                ->where('chapters.0.title', 'Test Chapter')
                ->where('chapters.0.scenes.0.title', 'Test Scene')
            );
    }

    public function test_workspace_creates_default_chapter_and_scene_if_none_exist(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/workspace');

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

    public function test_workspace_can_load_specific_scene(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        $scene1 = Scene::factory()->create(['chapter_id' => $chapter->id, 'title' => 'Scene 1']);
        $scene2 = Scene::factory()->create(['chapter_id' => $chapter->id, 'title' => 'Scene 2']);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/workspace/'.$scene2->id);

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('activeScene.id', $scene2->id)
                ->where('activeScene.title', 'Scene 2')
            );
    }

    public function test_workspace_defaults_to_write_mode(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create(['chapter_id' => $chapter->id]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/workspace');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('initialMode', 'write')
            );
    }

    public function test_workspace_accepts_plan_mode_parameter(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create(['chapter_id' => $chapter->id]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/workspace?mode=plan');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('initialMode', 'plan')
            );
    }

    public function test_workspace_accepts_codex_mode_parameter(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create(['chapter_id' => $chapter->id]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/workspace?mode=codex');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('initialMode', 'codex')
            );
    }

    public function test_workspace_ignores_invalid_mode_parameter(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create(['chapter_id' => $chapter->id]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/workspace?mode=invalid');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('initialMode', 'write')
            );
    }

    public function test_unauthorized_user_cannot_access_workspace(): void
    {
        $otherUser = User::factory()->create();

        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create(['chapter_id' => $chapter->id]);

        $response = $this->actingAs($otherUser)
            ->get('/novels/'.$this->novel->id.'/workspace');

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_access_workspace(): void
    {
        $response = $this->get('/novels/'.$this->novel->id.'/workspace');

        $response->assertRedirect('/login');
    }

    public function test_workspace_includes_acts_data(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create(['chapter_id' => $chapter->id]);

        // Create an act
        $this->novel->acts()->create([
            'title' => 'Act 1',
            'position' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/workspace');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('acts', 1)
                ->where('acts.0.title', 'Act 1')
            );
    }

    public function test_workspace_includes_labels_data(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory()->create(['chapter_id' => $chapter->id]);

        // Create a label
        $this->novel->labels()->create([
            'name' => 'Important',
            'color' => '#FF0000',
            'position' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/novels/'.$this->novel->id.'/workspace');

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('labels', 1)
                ->where('labels.0.name', 'Important')
            );
    }
}
