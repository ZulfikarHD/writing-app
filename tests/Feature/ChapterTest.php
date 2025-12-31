<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChapterTest extends TestCase
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

    public function test_user_can_list_chapters_for_novel(): void
    {
        $chapters = Chapter::factory(3)
            ->sequence(
                ['position' => 0],
                ['position' => 1],
                ['position' => 2],
            )
            ->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/novels/'.$this->novel->id.'/chapters');

        $response->assertOk()
            ->assertJsonCount(3, 'chapters')
            ->assertJsonPath('chapters.0.id', $chapters[0]->id)
            ->assertJsonPath('chapters.1.id', $chapters[1]->id)
            ->assertJsonPath('chapters.2.id', $chapters[2]->id);
    }

    public function test_user_cannot_list_chapters_for_other_users_novel(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/novels/'.$otherNovel->id.'/chapters');

        $response->assertForbidden();
    }

    public function test_user_can_create_chapter(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$this->novel->id.'/chapters', [
                'title' => 'Chapter 1: The Beginning',
            ]);

        $response->assertCreated()
            ->assertJsonPath('chapter.title', 'Chapter 1: The Beginning')
            ->assertJsonStructure([
                'chapter' => [
                    'id',
                    'title',
                    'position',
                    'scenes',
                ],
            ]);

        $this->assertDatabaseHas('chapters', [
            'novel_id' => $this->novel->id,
            'title' => 'Chapter 1: The Beginning',
        ]);

        // Default scene should be created
        $chapter = Chapter::find($response->json('chapter.id'));
        $this->assertCount(1, $chapter->scenes);
    }

    public function test_user_cannot_create_chapter_for_other_users_novel(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$otherNovel->id.'/chapters', [
                'title' => 'Chapter 1',
            ]);

        $response->assertForbidden();
    }

    public function test_chapter_position_is_auto_incremented(): void
    {
        Chapter::factory()->create([
            'novel_id' => $this->novel->id,
            'position' => 0,
        ]);

        Chapter::factory()->create([
            'novel_id' => $this->novel->id,
            'position' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$this->novel->id.'/chapters', [
                'title' => 'Chapter 3',
            ]);

        $response->assertCreated()
            ->assertJsonPath('chapter.position', 2);
    }

    public function test_user_can_update_chapter(): void
    {
        $chapter = Chapter::factory()->create([
            'novel_id' => $this->novel->id,
            'title' => 'Old Title',
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/chapters/'.$chapter->id, [
                'title' => 'New Title',
            ]);

        $response->assertOk()
            ->assertJsonPath('chapter.title', 'New Title');

        $this->assertDatabaseHas('chapters', [
            'id' => $chapter->id,
            'title' => 'New Title',
        ]);
    }

    public function test_user_cannot_update_other_users_chapter(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $chapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/chapters/'.$chapter->id, [
                'title' => 'Hacked Title',
            ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_chapter(): void
    {
        $chapter = Chapter::factory()->create([
            'novel_id' => $this->novel->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/chapters/'.$chapter->id);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('chapters', ['id' => $chapter->id]);
    }

    public function test_user_cannot_delete_other_users_chapter(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $chapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/chapters/'.$chapter->id);

        $response->assertForbidden();
    }

    public function test_user_can_reorder_chapters(): void
    {
        $chapters = Chapter::factory(3)
            ->sequence(
                ['position' => 0],
                ['position' => 1],
                ['position' => 2],
            )
            ->create(['novel_id' => $this->novel->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$this->novel->id.'/chapters/reorder', [
                'chapters' => [
                    ['id' => $chapters[2]->id, 'position' => 0],
                    ['id' => $chapters[0]->id, 'position' => 1],
                    ['id' => $chapters[1]->id, 'position' => 2],
                ],
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('chapters', ['id' => $chapters[2]->id, 'position' => 0]);
        $this->assertDatabaseHas('chapters', ['id' => $chapters[0]->id, 'position' => 1]);
        $this->assertDatabaseHas('chapters', ['id' => $chapters[1]->id, 'position' => 2]);
    }

    public function test_chapter_title_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/novels/'.$this->novel->id.'/chapters', [
                'title' => '',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }
}
