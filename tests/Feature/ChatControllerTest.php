<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatControllerTest extends TestCase
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

    public function test_user_can_list_their_chat_threads(): void
    {
        ChatThread::factory(3)->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/chat/threads");

        $response->assertOk()
            ->assertJsonCount(3, 'threads')
            ->assertJsonStructure([
                'threads' => [
                    '*' => [
                        'id',
                        'novel_id',
                        'user_id',
                        'title',
                        'model',
                        'is_pinned',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'total',
                ],
            ]);
    }

    public function test_user_cannot_see_other_users_threads(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        ChatThread::factory(2)->create([
            'novel_id' => $otherNovel->id,
            'user_id' => $otherUser->id,
        ]);

        ChatThread::factory(1)->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/chat/threads");

        $response->assertOk()
            ->assertJsonCount(1, 'threads');
    }

    public function test_user_can_create_chat_thread(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/chat/threads", [
                'title' => 'My Chat Thread',
            ]);

        $response->assertCreated()
            ->assertJsonPath('thread.title', 'My Chat Thread')
            ->assertJsonPath('thread.novel_id', $this->novel->id);

        $this->assertDatabaseHas('chat_threads', [
            'user_id' => $this->user->id,
            'novel_id' => $this->novel->id,
            'title' => 'My Chat Thread',
        ]);
    }

    public function test_user_can_create_thread_without_title(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/novels/{$this->novel->id}/chat/threads", []);

        $response->assertCreated()
            ->assertJsonPath('thread.title', null);
    }

    public function test_user_can_view_thread_with_messages(): void
    {
        $thread = ChatThread::factory()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
            'title' => 'Test Thread',
        ]);

        ChatMessage::factory(3)->create([
            'thread_id' => $thread->id,
            'role' => 'user',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/chat/threads/{$thread->id}");

        $response->assertOk()
            ->assertJsonPath('thread.id', $thread->id)
            ->assertJsonCount(3, 'thread.messages');
    }

    public function test_user_cannot_view_other_users_thread(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $thread = ChatThread::factory()->create([
            'novel_id' => $otherNovel->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/chat/threads/{$thread->id}");

        $response->assertForbidden();
    }

    public function test_user_can_update_chat_thread(): void
    {
        $thread = ChatThread::factory()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
            'title' => 'Old Title',
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/chat/threads/{$thread->id}", [
                'title' => 'New Title',
            ]);

        $response->assertOk()
            ->assertJsonPath('thread.title', 'New Title');

        $this->assertDatabaseHas('chat_threads', [
            'id' => $thread->id,
            'title' => 'New Title',
        ]);
    }

    public function test_user_can_pin_thread(): void
    {
        $thread = ChatThread::factory()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
            'is_pinned' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/chat/threads/{$thread->id}", [
                'is_pinned' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('thread.is_pinned', true);
    }

    public function test_user_cannot_update_other_users_thread(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $thread = ChatThread::factory()->create([
            'novel_id' => $otherNovel->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/chat/threads/{$thread->id}", [
                'title' => 'Hacked Title',
            ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_chat_thread(): void
    {
        $thread = ChatThread::factory()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/chat/threads/{$thread->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Thread deleted successfully.');

        $this->assertDatabaseMissing('chat_threads', ['id' => $thread->id]);
    }

    public function test_deleting_thread_deletes_messages(): void
    {
        $thread = ChatThread::factory()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
        ]);

        $message = ChatMessage::factory()->create([
            'thread_id' => $thread->id,
        ]);

        $this->actingAs($this->user)
            ->deleteJson("/api/chat/threads/{$thread->id}");

        $this->assertDatabaseMissing('chat_messages', ['id' => $message->id]);
    }

    public function test_user_cannot_delete_other_users_thread(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $thread = ChatThread::factory()->create([
            'novel_id' => $otherNovel->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/chat/threads/{$thread->id}");

        $response->assertForbidden();
    }

    public function test_user_can_archive_thread(): void
    {
        $thread = ChatThread::factory()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
            'archived_at' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/threads/{$thread->id}/archive");

        $response->assertOk();

        $thread->refresh();
        $this->assertNotNull($thread->archived_at);
    }

    public function test_user_can_restore_archived_thread(): void
    {
        $thread = ChatThread::factory()->archived()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/chat/threads/{$thread->id}/restore");

        $response->assertOk();

        $thread->refresh();
        $this->assertNull($thread->archived_at);
    }

    public function test_archived_threads_not_shown_by_default(): void
    {
        ChatThread::factory()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
            'archived_at' => null,
        ]);

        ChatThread::factory()->archived()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/chat/threads");

        $response->assertOk()
            ->assertJsonCount(1, 'threads');
    }

    public function test_can_include_archived_threads(): void
    {
        ChatThread::factory()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
            'archived_at' => null,
        ]);

        ChatThread::factory()->archived()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/chat/threads?include_archived=true");

        $response->assertOk()
            ->assertJsonCount(2, 'threads');
    }

    public function test_user_can_delete_message(): void
    {
        $thread = ChatThread::factory()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
        ]);

        $message = ChatMessage::factory()->create([
            'thread_id' => $thread->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/chat/messages/{$message->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Message deleted successfully.');

        $this->assertDatabaseMissing('chat_messages', ['id' => $message->id]);
    }

    public function test_user_cannot_delete_message_from_other_users_thread(): void
    {
        $otherUser = User::factory()->create();
        $otherNovel = Novel::factory()->create(['user_id' => $otherUser->id]);

        $thread = ChatThread::factory()->create([
            'novel_id' => $otherNovel->id,
            'user_id' => $otherUser->id,
        ]);

        $message = ChatMessage::factory()->create([
            'thread_id' => $thread->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/chat/messages/{$message->id}");

        $response->assertForbidden();
    }

    public function test_user_can_get_thread_messages(): void
    {
        $thread = ChatThread::factory()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
        ]);

        ChatMessage::factory(5)->create([
            'thread_id' => $thread->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/chat/threads/{$thread->id}/messages");

        $response->assertOk()
            ->assertJsonCount(5, 'messages')
            ->assertJsonStructure([
                'messages' => [
                    '*' => [
                        'id',
                        'thread_id',
                        'role',
                        'content',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'total',
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_access_chat(): void
    {
        $response = $this->getJson("/api/novels/{$this->novel->id}/chat/threads");

        $response->assertUnauthorized();
    }

    public function test_threads_ordered_by_updated_at(): void
    {
        $oldThread = ChatThread::factory()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
            'title' => 'Old Thread',
            'updated_at' => now()->subDays(2),
        ]);

        $newThread = ChatThread::factory()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
            'title' => 'New Thread',
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/novels/{$this->novel->id}/chat/threads");

        $response->assertOk();

        $threads = $response->json('threads');
        $this->assertEquals('New Thread', $threads[0]['title']);
        $this->assertEquals('Old Thread', $threads[1]['title']);
    }

    public function test_thread_model_can_be_set(): void
    {
        $thread = ChatThread::factory()->create([
            'novel_id' => $this->novel->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/chat/threads/{$thread->id}", [
                'model' => 'gpt-4o',
            ]);

        $response->assertOk()
            ->assertJsonPath('thread.model', 'gpt-4o');
    }
}
