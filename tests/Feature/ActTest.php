<?php

namespace Tests\Feature;

use App\Models\Act;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActTest extends TestCase
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

    public function test_user_can_list_acts_for_novel(): void
    {
        $acts = Act::factory(3)
            ->sequence(
                ['position' => 0, 'title' => 'Act 1'],
                ['position' => 1, 'title' => 'Act 2'],
                ['position' => 2, 'title' => 'Act 3'],
            )
            ->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/novels/'.$this->novel->id.'/acts');

        $response->assertOk()
            ->assertJsonCount(3, 'acts')
            ->assertJsonPath('acts.0.id', $acts[0]->id)
            ->assertJsonPath('acts.1.id', $acts[1]->id)
            ->assertJsonPath('acts.2.id', $acts[2]->id);
    }

    public function test_user_cannot_list_acts_for_other_users_novel(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/novels/'.$otherNovel->id.'/acts');

        $response->assertForbidden();
    }

    public function test_user_can_create_act(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$this->novel->id.'/acts', [
                'title' => 'Act 1: The Beginning',
            ]);

        $response->assertCreated()
            ->assertJsonPath('act.title', 'Act 1: The Beginning')
            ->assertJsonStructure([
                'act' => [
                    'id',
                    'title',
                    'position',
                ],
            ]);

        $this->assertDatabaseHas('acts', [
            'novel_id' => $this->novel->id,
            'title' => 'Act 1: The Beginning',
        ]);
    }

    public function test_user_cannot_create_act_for_other_users_novel(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$otherNovel->id.'/acts', [
                'title' => 'Act 1',
            ]);

        $response->assertForbidden();
    }

    public function test_act_position_is_auto_incremented(): void
    {
        Act::factory()->create([
            'novel_id' => $this->novel->id,
            'position' => 0,
        ]);

        Act::factory()->create([
            'novel_id' => $this->novel->id,
            'position' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$this->novel->id.'/acts', [
                'title' => 'Act 3',
            ]);

        $response->assertCreated()
            ->assertJsonPath('act.position', 2);
    }

    public function test_user_can_update_act(): void
    {
        $act = Act::factory()->create([
            'novel_id' => $this->novel->id,
            'title' => 'Old Title',
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/acts/'.$act->id, [
                'title' => 'New Title',
            ]);

        $response->assertOk()
            ->assertJsonPath('act.title', 'New Title');

        $this->assertDatabaseHas('acts', [
            'id' => $act->id,
            'title' => 'New Title',
        ]);
    }

    public function test_user_cannot_update_other_users_act(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $act = Act::factory()->create(['novel_id' => $otherNovel->id]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/acts/'.$act->id, [
                'title' => 'Hacked Title',
            ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_act(): void
    {
        $act = Act::factory()->create([
            'novel_id' => $this->novel->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/acts/'.$act->id);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('acts', ['id' => $act->id]);
    }

    public function test_deleting_act_sets_chapter_act_id_to_null(): void
    {
        $act = Act::factory()->create([
            'novel_id' => $this->novel->id,
        ]);

        $chapter = Chapter::factory()->create([
            'novel_id' => $this->novel->id,
            'act_id' => $act->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/acts/'.$act->id);

        $response->assertOk();

        $this->assertDatabaseHas('chapters', [
            'id' => $chapter->id,
            'act_id' => null,
        ]);
    }

    public function test_user_cannot_delete_other_users_act(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $act = Act::factory()->create(['novel_id' => $otherNovel->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/acts/'.$act->id);

        $response->assertForbidden();
    }

    public function test_user_can_reorder_acts(): void
    {
        $acts = Act::factory(3)
            ->sequence(
                ['position' => 0],
                ['position' => 1],
                ['position' => 2],
            )
            ->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$this->novel->id.'/acts/reorder', [
                'acts' => [
                    ['id' => $acts[2]->id, 'position' => 0],
                    ['id' => $acts[0]->id, 'position' => 1],
                    ['id' => $acts[1]->id, 'position' => 2],
                ],
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('acts', ['id' => $acts[2]->id, 'position' => 0]);
        $this->assertDatabaseHas('acts', ['id' => $acts[0]->id, 'position' => 1]);
        $this->assertDatabaseHas('acts', ['id' => $acts[1]->id, 'position' => 2]);
    }

    public function test_act_title_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$this->novel->id.'/acts', [
                'title' => '',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }
}
