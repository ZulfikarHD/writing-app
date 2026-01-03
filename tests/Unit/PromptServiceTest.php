<?php

namespace Tests\Unit;

use App\Models\Prompt;
use App\Models\User;
use App\Services\Prompts\PromptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptServiceTest extends TestCase
{
    use RefreshDatabase;

    private PromptService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PromptService;
        $this->user = User::factory()->create();
    }

    public function test_get_accessible_prompts_returns_system_and_user_prompts(): void
    {
        // Create system prompts
        Prompt::factory(2)->system()->create();

        // Create user's prompts
        Prompt::factory(3)->create(['user_id' => $this->user->id]);

        // Create another user's prompts
        $otherUser = User::factory()->create();
        Prompt::factory(2)->create(['user_id' => $otherUser->id]);

        $prompts = $this->service->getAccessiblePrompts($this->user);

        $this->assertCount(5, $prompts);
    }

    public function test_get_accessible_prompts_filters_by_type(): void
    {
        Prompt::factory(2)->chat()->create(['user_id' => $this->user->id]);
        Prompt::factory(3)->replacement()->create(['user_id' => $this->user->id]);

        $prompts = $this->service->getAccessiblePrompts($this->user, 'chat');

        $this->assertCount(2, $prompts);
        $this->assertTrue($prompts->every(fn ($p) => $p->type === 'chat'));
    }

    public function test_get_accessible_prompts_searches_by_name_and_description(): void
    {
        Prompt::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Searchable Name',
            'description' => 'Generic description',
        ]);
        Prompt::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Generic Name',
            'description' => 'Searchable description',
        ]);
        Prompt::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Other',
            'description' => 'Other',
        ]);

        $prompts = $this->service->getAccessiblePrompts($this->user, null, 'Searchable');

        $this->assertCount(2, $prompts);
    }

    public function test_get_prompts_by_type_returns_correct_type(): void
    {
        Prompt::factory(2)->chat()->create(['user_id' => $this->user->id]);
        Prompt::factory(3)->prose()->create(['user_id' => $this->user->id]);

        $chatPrompts = $this->service->getPromptsByType($this->user, 'chat');
        $prosePrompts = $this->service->getPromptsByType($this->user, 'prose');

        $this->assertCount(2, $chatPrompts);
        $this->assertCount(3, $prosePrompts);
    }

    public function test_get_system_prompts_only_returns_system_prompts(): void
    {
        Prompt::factory(2)->system()->create();
        Prompt::factory(3)->create(['user_id' => $this->user->id]);

        $prompts = $this->service->getSystemPrompts();

        $this->assertCount(2, $prompts);
        $this->assertTrue($prompts->every(fn ($p) => $p->is_system === true));
    }

    public function test_create_prompt_sets_user_id(): void
    {
        $prompt = $this->service->createPrompt($this->user, [
            'name' => 'Test Prompt',
            'type' => 'chat',
        ]);

        $this->assertEquals($this->user->id, $prompt->user_id);
        $this->assertFalse($prompt->is_system);
    }

    public function test_create_prompt_sets_sort_order(): void
    {
        Prompt::factory()->create([
            'user_id' => $this->user->id,
            'sort_order' => 5,
        ]);

        $prompt = $this->service->createPrompt($this->user, [
            'name' => 'Test Prompt',
            'type' => 'chat',
        ]);

        $this->assertEquals(6, $prompt->sort_order);
    }

    public function test_update_prompt_succeeds_for_own_prompt(): void
    {
        $prompt = Prompt::factory()->create(['user_id' => $this->user->id]);

        $updated = $this->service->updatePrompt($prompt, $this->user, [
            'name' => 'Updated Name',
        ]);

        $this->assertEquals('Updated Name', $updated->name);
    }

    public function test_update_prompt_fails_for_system_prompt(): void
    {
        $prompt = Prompt::factory()->system()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You cannot edit this prompt.');

        $this->service->updatePrompt($prompt, $this->user, [
            'name' => 'Hacked Name',
        ]);
    }

    public function test_update_prompt_cannot_change_system_status(): void
    {
        $prompt = Prompt::factory()->create(['user_id' => $this->user->id]);

        $updated = $this->service->updatePrompt($prompt, $this->user, [
            'is_system' => true,
            'user_id' => null,
        ]);

        $this->assertFalse($updated->is_system);
        $this->assertEquals($this->user->id, $updated->user_id);
    }

    public function test_delete_prompt_succeeds_for_own_prompt(): void
    {
        $prompt = Prompt::factory()->create(['user_id' => $this->user->id]);
        $id = $prompt->id;

        $result = $this->service->deletePrompt($prompt, $this->user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('prompts', ['id' => $id]);
    }

    public function test_delete_prompt_fails_for_system_prompt(): void
    {
        $prompt = Prompt::factory()->system()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('You cannot delete this prompt.');

        $this->service->deletePrompt($prompt, $this->user);
    }

    public function test_clone_prompt_creates_copy_with_user_ownership(): void
    {
        $original = Prompt::factory()->system()->create([
            'name' => 'Original',
            'system_message' => 'System message content',
        ]);

        $clone = $this->service->clonePrompt($original, $this->user);

        $this->assertEquals('Original (Copy)', $clone->name);
        $this->assertEquals($this->user->id, $clone->user_id);
        $this->assertFalse($clone->is_system);
        $this->assertEquals('System message content', $clone->system_message);
        $this->assertEquals(0, $clone->usage_count);
    }

    public function test_clone_prompt_with_custom_name(): void
    {
        $original = Prompt::factory()->create(['user_id' => $this->user->id]);

        $clone = $this->service->clonePrompt($original, $this->user, 'My Custom Name');

        $this->assertEquals('My Custom Name', $clone->name);
    }

    public function test_record_usage_increments_count(): void
    {
        $prompt = Prompt::factory()->create([
            'user_id' => $this->user->id,
            'usage_count' => 5,
        ]);

        $this->service->recordUsage($prompt);

        $prompt->refresh();
        $this->assertEquals(6, $prompt->usage_count);
    }

    public function test_get_prompt_types_returns_all_types(): void
    {
        $types = $this->service->getPromptTypes();

        $this->assertArrayHasKey('chat', $types);
        $this->assertArrayHasKey('prose', $types);
        $this->assertArrayHasKey('replacement', $types);
        $this->assertArrayHasKey('summary', $types);
    }

    public function test_get_statistics_returns_correct_counts(): void
    {
        // Create system prompts
        Prompt::factory(2)->system()->chat()->create();
        Prompt::factory(1)->system()->replacement()->create();

        // Create user's prompts
        Prompt::factory(3)->chat()->withUsage(10)->create(['user_id' => $this->user->id]);
        Prompt::factory(2)->prose()->withUsage(5)->create(['user_id' => $this->user->id]);

        $stats = $this->service->getStatistics($this->user);

        $this->assertEquals(5, $stats['user_prompts']);
        $this->assertEquals(3, $stats['system_prompts']);
        $this->assertEquals(8, $stats['total']);
        $this->assertEquals(40, $stats['total_usage']); // 3*10 + 2*5 = 40
    }

    public function test_reorder_prompts_updates_sort_order(): void
    {
        $prompt1 = Prompt::factory()->create(['user_id' => $this->user->id, 'sort_order' => 1]);
        $prompt2 = Prompt::factory()->create(['user_id' => $this->user->id, 'sort_order' => 2]);
        $prompt3 = Prompt::factory()->create(['user_id' => $this->user->id, 'sort_order' => 3]);

        $this->service->reorderPrompts($this->user, [
            $prompt1->id => 3,
            $prompt2->id => 1,
            $prompt3->id => 2,
        ]);

        $prompt1->refresh();
        $prompt2->refresh();
        $prompt3->refresh();

        $this->assertEquals(3, $prompt1->sort_order);
        $this->assertEquals(1, $prompt2->sort_order);
        $this->assertEquals(2, $prompt3->sort_order);
    }

    public function test_inactive_prompts_are_not_included_in_accessible_prompts(): void
    {
        Prompt::factory(2)->create(['user_id' => $this->user->id]);
        Prompt::factory()->inactive()->create(['user_id' => $this->user->id]);

        $prompts = $this->service->getAccessiblePrompts($this->user);

        $this->assertCount(2, $prompts);
    }

    public function test_get_prompt_returns_accessible_prompt(): void
    {
        $prompt = Prompt::factory()->create(['user_id' => $this->user->id]);

        $found = $this->service->getPrompt($prompt->id, $this->user);

        $this->assertNotNull($found);
        $this->assertEquals($prompt->id, $found->id);
    }

    public function test_get_prompt_returns_system_prompt(): void
    {
        $prompt = Prompt::factory()->system()->create();

        $found = $this->service->getPrompt($prompt->id, $this->user);

        $this->assertNotNull($found);
        $this->assertTrue($found->is_system);
    }

    public function test_get_prompt_returns_null_for_other_users_prompt(): void
    {
        $otherUser = User::factory()->create();
        $prompt = Prompt::factory()->create(['user_id' => $otherUser->id]);

        $found = $this->service->getPrompt($prompt->id, $this->user);

        $this->assertNull($found);
    }
}
