<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Scene;
use App\Models\SceneLabel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SceneLabelTest extends TestCase
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

    public function test_user_can_list_labels_for_novel(): void
    {
        $labels = SceneLabel::factory(3)
            ->sequence(
                ['position' => 0, 'name' => 'Action', 'color' => '#EF4444'],
                ['position' => 1, 'name' => 'Romance', 'color' => '#EC4899'],
                ['position' => 2, 'name' => 'Drama', 'color' => '#3B82F6'],
            )
            ->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/novels/'.$this->novel->id.'/labels');

        $response->assertOk()
            ->assertJsonCount(3, 'labels')
            ->assertJsonPath('labels.0.name', 'Action')
            ->assertJsonPath('labels.1.name', 'Romance')
            ->assertJsonPath('labels.2.name', 'Drama');
    }

    public function test_user_cannot_list_labels_for_other_users_novel(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/novels/'.$otherNovel->id.'/labels');

        $response->assertForbidden();
    }

    public function test_user_can_create_label(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$this->novel->id.'/labels', [
                'name' => 'Important',
                'color' => '#F59E0B',
            ]);

        $response->assertCreated()
            ->assertJsonPath('label.name', 'Important')
            ->assertJsonPath('label.color', '#F59E0B')
            ->assertJsonStructure([
                'label' => [
                    'id',
                    'name',
                    'color',
                    'position',
                ],
            ]);

        $this->assertDatabaseHas('scene_labels', [
            'novel_id' => $this->novel->id,
            'name' => 'Important',
            'color' => '#F59E0B',
        ]);
    }

    public function test_label_gets_default_color_if_not_provided(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$this->novel->id.'/labels', [
                'name' => 'Default Color Label',
            ]);

        $response->assertCreated()
            ->assertJsonPath('label.color', '#6B7280');
    }

    public function test_user_cannot_create_label_for_other_users_novel(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$otherNovel->id.'/labels', [
                'name' => 'Label',
            ]);

        $response->assertForbidden();
    }

    public function test_user_can_update_label(): void
    {
        $label = SceneLabel::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Old Name',
            'color' => '#000000',
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/labels/'.$label->id, [
                'name' => 'New Name',
                'color' => '#FFFFFF',
            ]);

        $response->assertOk()
            ->assertJsonPath('label.name', 'New Name')
            ->assertJsonPath('label.color', '#FFFFFF');

        $this->assertDatabaseHas('scene_labels', [
            'id' => $label->id,
            'name' => 'New Name',
            'color' => '#FFFFFF',
        ]);
    }

    public function test_user_cannot_update_other_users_label(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $label = SceneLabel::factory()->create(['novel_id' => $otherNovel->id]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/labels/'.$label->id, [
                'name' => 'Hacked',
            ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_label(): void
    {
        $label = SceneLabel::factory()->create([
            'novel_id' => $this->novel->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/labels/'.$label->id);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('scene_labels', ['id' => $label->id]);
    }

    public function test_user_cannot_delete_other_users_label(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $label = SceneLabel::factory()->create(['novel_id' => $otherNovel->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/labels/'.$label->id);

        $response->assertForbidden();
    }

    public function test_user_can_assign_labels_to_scene(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        $scene = Scene::factory()->create(['chapter_id' => $chapter->id]);

        $labels = SceneLabel::factory(2)->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/scenes/'.$scene->id.'/labels', [
                'label_ids' => $labels->pluck('id')->toArray(),
            ]);

        $response->assertOk()
            ->assertJsonCount(2, 'labels');

        $this->assertDatabaseHas('scene_label', [
            'scene_id' => $scene->id,
            'scene_label_id' => $labels[0]->id,
        ]);
        $this->assertDatabaseHas('scene_label', [
            'scene_id' => $scene->id,
            'scene_label_id' => $labels[1]->id,
        ]);
    }

    public function test_assigning_labels_replaces_existing_labels(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        $scene = Scene::factory()->create(['chapter_id' => $chapter->id]);

        $oldLabel = SceneLabel::factory()->create(['novel_id' => $this->novel->id, 'name' => 'Old']);
        $newLabel = SceneLabel::factory()->create(['novel_id' => $this->novel->id, 'name' => 'New']);

        $scene->labels()->attach($oldLabel->id);

        $response = $this->actingAs($this->user)
            ->postJson('/api/scenes/'.$scene->id.'/labels', [
                'label_ids' => [$newLabel->id],
            ]);

        $response->assertOk();

        $this->assertDatabaseMissing('scene_label', [
            'scene_id' => $scene->id,
            'scene_label_id' => $oldLabel->id,
        ]);
        $this->assertDatabaseHas('scene_label', [
            'scene_id' => $scene->id,
            'scene_label_id' => $newLabel->id,
        ]);
    }

    public function test_user_cannot_assign_labels_from_other_novel(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        $scene = Scene::factory()->create(['chapter_id' => $chapter->id]);

        $otherNovel = Novel::factory()->create(['user_id' => $this->user->id]);
        $otherLabel = SceneLabel::factory()->create(['novel_id' => $otherNovel->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/scenes/'.$scene->id.'/labels', [
                'label_ids' => [$otherLabel->id],
            ]);

        $response->assertOk()
            ->assertJsonCount(0, 'labels');

        $this->assertDatabaseMissing('scene_label', [
            'scene_id' => $scene->id,
            'scene_label_id' => $otherLabel->id,
        ]);
    }

    public function test_label_name_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$this->novel->id.'/labels', [
                'name' => '',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_label_color_must_be_valid_hex(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$this->novel->id.'/labels', [
                'name' => 'Invalid Color',
                'color' => 'not-a-color',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['color']);
    }
}
