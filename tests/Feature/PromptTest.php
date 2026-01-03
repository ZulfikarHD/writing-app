<?php

namespace Tests\Feature;

use App\Models\Prompt;
use App\Models\User;
use Database\Seeders\PromptSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_can_list_accessible_prompts(): void
    {
        // Create system prompts
        Prompt::factory(2)->system()->create();

        // Create user's own prompts
        Prompt::factory(3)->create(['user_id' => $this->user->id]);

        // Create another user's prompts (should not be visible)
        $otherUser = User::factory()->create();
        Prompt::factory(2)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/prompts');

        $response->assertOk()
            ->assertJsonCount(5, 'prompts') // 2 system + 3 own
            ->assertJsonStructure([
                'prompts' => [
                    '*' => [
                        'id',
                        'user_id',
                        'name',
                        'description',
                        'type',
                        'type_label',
                        'system_message',
                        'user_message',
                        'is_system',
                        'is_active',
                        'sort_order',
                        'usage_count',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_user_can_filter_prompts_by_type(): void
    {
        Prompt::factory(2)->chat()->create(['user_id' => $this->user->id]);
        Prompt::factory(3)->replacement()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/prompts?type=chat');

        $response->assertOk()
            ->assertJsonCount(2, 'prompts');

        foreach ($response->json('prompts') as $prompt) {
            $this->assertEquals('chat', $prompt['type']);
        }
    }

    public function test_user_can_search_prompts(): void
    {
        Prompt::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Unique Search Term',
        ]);
        Prompt::factory(4)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/prompts?search=Unique');

        $response->assertOk()
            ->assertJsonCount(1, 'prompts')
            ->assertJsonPath('prompts.0.name', 'Unique Search Term');
    }

    public function test_user_can_get_prompts_by_type(): void
    {
        Prompt::factory(3)->chat()->create(['user_id' => $this->user->id]);
        Prompt::factory(2)->replacement()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/prompts/type/chat');

        $response->assertOk()
            ->assertJsonCount(3, 'prompts');
    }

    public function test_invalid_type_returns_error(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/prompts/type/invalid_type');

        $response->assertBadRequest()
            ->assertJson(['error' => 'Invalid prompt type.']);
    }

    public function test_user_can_create_prompt(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/prompts', [
                'name' => 'My Custom Prompt',
                'description' => 'A test description',
                'type' => 'chat',
                'system_message' => 'You are a helpful assistant.',
                'user_message' => '{{selection}}',
            ]);

        $response->assertCreated()
            ->assertJsonPath('prompt.name', 'My Custom Prompt')
            ->assertJsonPath('prompt.type', 'chat')
            ->assertJsonPath('prompt.is_system', false);

        $this->assertDatabaseHas('prompts', [
            'user_id' => $this->user->id,
            'name' => 'My Custom Prompt',
            'type' => 'chat',
        ]);
    }

    public function test_name_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/prompts', [
                'type' => 'chat',
                'system_message' => 'Test',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_type_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/prompts', [
                'name' => 'Test Prompt',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    public function test_invalid_type_is_rejected(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/prompts', [
                'name' => 'Test Prompt',
                'type' => 'invalid_type',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    public function test_user_can_get_single_prompt(): void
    {
        $prompt = Prompt::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/prompts/'.$prompt->id);

        $response->assertOk()
            ->assertJsonPath('prompt.id', $prompt->id)
            ->assertJsonPath('prompt.name', $prompt->name);
    }

    public function test_user_can_access_system_prompts(): void
    {
        $systemPrompt = Prompt::factory()->system()->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/prompts/'.$systemPrompt->id);

        $response->assertOk()
            ->assertJsonPath('prompt.is_system', true);
    }

    public function test_user_cannot_access_other_users_prompts(): void
    {
        $otherUser = User::factory()->create();
        $prompt = Prompt::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/prompts/'.$prompt->id);

        $response->assertForbidden();
    }

    public function test_user_can_update_own_prompt(): void
    {
        $prompt = Prompt::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/prompts/'.$prompt->id, [
                'name' => 'Updated Name',
                'description' => 'Updated description',
            ]);

        $response->assertOk()
            ->assertJsonPath('prompt.name', 'Updated Name')
            ->assertJsonPath('prompt.description', 'Updated description');

        $this->assertDatabaseHas('prompts', [
            'id' => $prompt->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_cannot_update_system_prompt(): void
    {
        $systemPrompt = Prompt::factory()->system()->create();

        $response = $this->actingAs($this->user)
            ->patchJson('/api/prompts/'.$systemPrompt->id, [
                'name' => 'Hacked Name',
            ]);

        $response->assertForbidden();
    }

    public function test_user_cannot_update_other_users_prompt(): void
    {
        $otherUser = User::factory()->create();
        $prompt = Prompt::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/prompts/'.$prompt->id, [
                'name' => 'Hacked Name',
            ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_prompt(): void
    {
        $prompt = Prompt::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/prompts/'.$prompt->id);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('prompts', ['id' => $prompt->id]);
    }

    public function test_user_cannot_delete_system_prompt(): void
    {
        $systemPrompt = Prompt::factory()->system()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/prompts/'.$systemPrompt->id);

        $response->assertForbidden();

        $this->assertDatabaseHas('prompts', ['id' => $systemPrompt->id]);
    }

    public function test_user_cannot_delete_other_users_prompt(): void
    {
        $otherUser = User::factory()->create();
        $prompt = Prompt::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/prompts/'.$prompt->id);

        $response->assertForbidden();
    }

    public function test_user_can_clone_own_prompt(): void
    {
        $prompt = Prompt::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Original Prompt',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/prompts/'.$prompt->id.'/clone');

        $response->assertCreated()
            ->assertJsonPath('prompt.name', 'Original Prompt (Copy)')
            ->assertJsonPath('prompt.is_system', false);

        $this->assertDatabaseCount('prompts', 2);
    }

    public function test_user_can_clone_system_prompt(): void
    {
        $systemPrompt = Prompt::factory()->system()->create([
            'name' => 'System Prompt',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/prompts/'.$systemPrompt->id.'/clone');

        $response->assertCreated()
            ->assertJsonPath('prompt.name', 'System Prompt (Copy)')
            ->assertJsonPath('prompt.is_system', false)
            ->assertJsonPath('prompt.user_id', $this->user->id);
    }

    public function test_user_can_clone_with_custom_name(): void
    {
        $prompt = Prompt::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/prompts/'.$prompt->id.'/clone', [
                'name' => 'My Custom Clone',
            ]);

        $response->assertCreated()
            ->assertJsonPath('prompt.name', 'My Custom Clone');
    }

    public function test_user_cannot_clone_other_users_prompt(): void
    {
        $otherUser = User::factory()->create();
        $prompt = Prompt::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/prompts/'.$prompt->id.'/clone');

        $response->assertForbidden();
    }

    public function test_user_can_record_prompt_usage(): void
    {
        $prompt = Prompt::factory()->create([
            'user_id' => $this->user->id,
            'usage_count' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/prompts/'.$prompt->id.'/usage');

        $response->assertOk()
            ->assertJson(['success' => true]);

        $prompt->refresh();
        $this->assertEquals(1, $prompt->usage_count);
    }

    public function test_get_prompt_types(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/prompts/types');

        $response->assertOk()
            ->assertJsonStructure([
                'types' => [
                    'chat',
                    'prose',
                    'replacement',
                    'summary',
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_access_prompts(): void
    {
        $response = $this->getJson('/api/prompts');

        $response->assertUnauthorized();
    }

    public function test_seeder_creates_system_prompts(): void
    {
        $this->seed(PromptSeeder::class);

        $systemPrompts = Prompt::where('is_system', true)->count();
        $this->assertGreaterThan(0, $systemPrompts);

        // Check all 4 types have system prompts
        foreach (Prompt::getTypes() as $type) {
            $count = Prompt::where('is_system', true)->where('type', $type)->count();
            $this->assertGreaterThan(0, $count, "No system prompts for type: $type");
        }
    }

    public function test_prompts_page_can_be_loaded(): void
    {
        $this->seed(PromptSeeder::class);

        $response = $this->actingAs($this->user)
            ->get('/prompts');

        $response->assertOk();
    }

    public function test_model_settings_are_saved_correctly(): void
    {
        $modelSettings = [
            'temperature' => 0.8,
            'max_tokens' => 2000,
            'top_p' => 0.9,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/prompts', [
                'name' => 'Prompt with Settings',
                'type' => 'chat',
                'model_settings' => $modelSettings,
            ]);

        $response->assertCreated();

        $prompt = Prompt::find($response->json('prompt.id'));
        $this->assertEquals($modelSettings, $prompt->model_settings);
    }
}
