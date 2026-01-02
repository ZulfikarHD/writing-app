<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\ChatContextItem;
use App\Models\ChatThread;
use App\Models\CodexEntry;
use App\Models\Novel;
use App\Models\Scene;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatContextControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Novel $novel;

    private ChatThread $thread;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->novel = Novel::factory()->create(['user_id' => $this->user->id]);
        $this->thread = ChatThread::factory()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_list_context_items(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        $scene = Scene::factory()->create(['chapter_id' => $chapter->id]);

        ChatContextItem::factory()->create([
            'thread_id' => $this->thread->id,
            'context_type' => 'scene',
            'reference_id' => $scene->id,
        ]);

        ChatContextItem::factory()->create([
            'thread_id' => $this->thread->id,
            'context_type' => 'custom',
            'custom_content' => 'Custom context text',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/chat/threads/{$this->thread->id}/context");

        $response->assertOk()
            ->assertJsonCount(2, 'items')
            ->assertJsonStructure([
                'items' => [
                    '*' => [
                        'id',
                        'thread_id',
                        'context_type',
                        'reference_id',
                        'is_active',
                        'tokens',
                        'preview',
                        'created_at',
                    ],
                ],
                'tokens' => [
                    'total',
                    'base_tokens',
                    'items',
                ],
                'limit' => [
                    'within_limit',
                    'usage_percentage',
                    'tokens_used',
                    'limit',
                    'model_limit',
                ],
            ]);
    }

    public function test_user_can_add_scene_context(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        $scene = Scene::factory()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Test Scene',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/threads/{$this->thread->id}/context", [
                'context_type' => 'scene',
                'reference_id' => $scene->id,
            ]);

        $response->assertCreated()
            ->assertJsonPath('item.context_type', 'scene')
            ->assertJsonPath('item.reference_id', $scene->id)
            ->assertJsonPath('item.reference.title', 'Test Scene');

        $this->assertDatabaseHas('chat_context_items', [
            'thread_id' => $this->thread->id,
            'context_type' => 'scene',
            'reference_id' => $scene->id,
        ]);
    }

    public function test_user_can_add_codex_context(): void
    {
        $codexEntry = CodexEntry::factory()->create([
            'novel_id' => $this->novel->id,
            'name' => 'Test Character',
            'type' => 'character',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/threads/{$this->thread->id}/context", [
                'context_type' => 'codex',
                'reference_id' => $codexEntry->id,
            ]);

        $response->assertCreated()
            ->assertJsonPath('item.context_type', 'codex')
            ->assertJsonPath('item.reference.name', 'Test Character');
    }

    public function test_user_can_add_custom_context(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/threads/{$this->thread->id}/context", [
                'context_type' => 'custom',
                'custom_content' => 'This is custom context text for the AI.',
            ]);

        $response->assertCreated()
            ->assertJsonPath('item.context_type', 'custom')
            ->assertJsonPath('item.custom_content', 'This is custom context text for the AI.');
    }

    public function test_cannot_add_scene_from_other_novel(): void
    {
        $otherNovel = Novel::factory()->create();
        $chapter = Chapter::factory()->create(['novel_id' => $otherNovel->id]);
        $scene = Scene::factory()->create(['chapter_id' => $chapter->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/threads/{$this->thread->id}/context", [
                'context_type' => 'scene',
                'reference_id' => $scene->id,
            ]);

        $response->assertNotFound();
    }

    public function test_user_can_toggle_context_item(): void
    {
        $item = ChatContextItem::factory()->create([
            'thread_id' => $this->thread->id,
            'context_type' => 'custom',
            'custom_content' => 'Test',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/chat/context/{$item->id}", [
                'is_active' => false,
            ]);

        $response->assertOk()
            ->assertJsonPath('item.is_active', false);

        $this->assertDatabaseHas('chat_context_items', [
            'id' => $item->id,
            'is_active' => false,
        ]);
    }

    public function test_user_can_remove_context_item(): void
    {
        $item = ChatContextItem::factory()->create([
            'thread_id' => $this->thread->id,
            'context_type' => 'custom',
            'custom_content' => 'Test',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/chat/context/{$item->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Context item removed.');

        $this->assertDatabaseMissing('chat_context_items', [
            'id' => $item->id,
        ]);
    }

    public function test_user_cannot_access_other_users_context(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);
        $otherThread = ChatThread::factory()->create([
            'novel_id' => $otherNovel->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/chat/threads/{$otherThread->id}/context");

        $response->assertForbidden();
    }

    public function test_user_can_clear_all_context(): void
    {
        ChatContextItem::factory(3)->create([
            'thread_id' => $this->thread->id,
            'context_type' => 'custom',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/chat/threads/{$this->thread->id}/context/clear");

        $response->assertOk()
            ->assertJsonPath('message', 'All context items cleared.');

        $this->assertEquals(0, $this->thread->contextItems()->count());
    }

    public function test_user_can_get_context_preview(): void
    {
        ChatContextItem::factory()->create([
            'thread_id' => $this->thread->id,
            'context_type' => 'custom',
            'custom_content' => 'Test preview content',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/chat/threads/{$this->thread->id}/context/preview");

        $response->assertOk()
            ->assertJsonStructure([
                'preview' => [
                    'text',
                    'tokens',
                    'items',
                ],
                'limit',
            ]);
    }

    public function test_user_can_get_context_sources(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        Scene::factory(2)->create(['chapter_id' => $chapter->id]);
        CodexEntry::factory(2)->create(['novel_id' => $this->novel->id, 'type' => 'character']);

        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/context-sources");

        $response->assertOk()
            ->assertJsonStructure([
                'chapters' => [
                    '*' => [
                        'id',
                        'title',
                        'scenes' => [
                            '*' => [
                                'id',
                                'title',
                                'word_count',
                                'tokens',
                            ],
                        ],
                    ],
                ],
                'codex' => [
                    '*' => [
                        'type',
                        'entries' => [
                            '*' => [
                                'id',
                                'name',
                                'type',
                                'tokens',
                            ],
                        ],
                    ],
                ],
                'has_summary',
                'has_outline',
            ]);
    }

    public function test_user_can_bulk_add_context(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        $scene1 = Scene::factory()->create(['chapter_id' => $chapter->id]);
        $scene2 = Scene::factory()->create(['chapter_id' => $chapter->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/threads/{$this->thread->id}/context/bulk", [
                'items' => [
                    ['context_type' => 'scene', 'reference_id' => $scene1->id],
                    ['context_type' => 'scene', 'reference_id' => $scene2->id],
                    ['context_type' => 'custom', 'custom_content' => 'Extra context'],
                ],
            ]);

        $response->assertCreated()
            ->assertJsonCount(3, 'items');

        $this->assertEquals(3, $this->thread->contextItems()->count());
    }

    public function test_adding_same_context_twice_reactivates_existing(): void
    {
        $chapter = Chapter::factory()->create(['novel_id' => $this->novel->id]);
        $scene = Scene::factory()->create(['chapter_id' => $chapter->id]);

        // Add context first time
        $this->actingAs($this->user)
            ->postJson("/api/chat/threads/{$this->thread->id}/context", [
                'context_type' => 'scene',
                'reference_id' => $scene->id,
            ]);

        // Deactivate it
        $item = $this->thread->contextItems()->first();
        $item->update(['is_active' => false]);

        // Add same context again
        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/threads/{$this->thread->id}/context", [
                'context_type' => 'scene',
                'reference_id' => $scene->id,
            ]);

        $response->assertCreated();

        // Should only have one item, not two
        $this->assertEquals(1, $this->thread->contextItems()->count());

        // Should be active again
        $this->assertTrue($this->thread->contextItems()->first()->is_active);
    }

    public function test_token_info_is_returned_with_context_operations(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/threads/{$this->thread->id}/context", [
                'context_type' => 'custom',
                'custom_content' => str_repeat('word ', 100), // ~400 chars = ~100 tokens
            ]);

        $response->assertCreated();
        $tokens = $response->json('tokens');

        $this->assertArrayHasKey('total', $tokens);
        $this->assertArrayHasKey('items', $tokens);
        $this->assertGreaterThan(0, $tokens['total']);
    }

    public function test_limit_info_warns_when_near_limit(): void
    {
        // Create a very large context item
        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/threads/{$this->thread->id}/context", [
                'context_type' => 'custom',
                'custom_content' => str_repeat('Large text content. ', 5000),
            ]);

        $response->assertCreated();
        $limit = $response->json('limit');

        $this->assertArrayHasKey('within_limit', $limit);
        $this->assertArrayHasKey('usage_percentage', $limit);
        $this->assertArrayHasKey('tokens_used', $limit);
        $this->assertArrayHasKey('limit', $limit);
    }

    public function test_unauthenticated_user_cannot_access_context(): void
    {
        $response = $this->getJson("/api/chat/threads/{$this->thread->id}/context");

        $response->assertUnauthorized();
    }

    public function test_validation_requires_context_type(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/threads/{$this->thread->id}/context", [
                'custom_content' => 'Some text',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['context_type']);
    }

    public function test_validation_requires_reference_id_for_scene_type(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/threads/{$this->thread->id}/context", [
                'context_type' => 'scene',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['reference_id']);
    }

    public function test_validation_requires_custom_content_for_custom_type(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/threads/{$this->thread->id}/context", [
                'context_type' => 'custom',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['custom_content']);
    }
}
