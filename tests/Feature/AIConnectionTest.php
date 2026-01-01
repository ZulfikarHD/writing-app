<?php

namespace Tests\Feature;

use App\Models\AIConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIConnectionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_can_list_their_ai_connections(): void
    {
        AIConnection::factory(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/ai-connections');

        $response->assertOk()
            ->assertJsonCount(3, 'connections')
            ->assertJsonStructure([
                'connections' => [
                    '*' => [
                        'id',
                        'provider',
                        'provider_name',
                        'name',
                        'base_url',
                        'has_api_key',
                        'masked_api_key',
                        'is_active',
                        'is_default',
                        'last_tested_at',
                        'last_test_status',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_user_cannot_see_other_users_connections(): void
    {
        $otherUser = User::factory()->create();
        AIConnection::factory(2)->create(['user_id' => $otherUser->id]);
        AIConnection::factory(1)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/ai-connections');

        $response->assertOk()
            ->assertJsonCount(1, 'connections');
    }

    public function test_user_can_create_ai_connection(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-connections', [
                'provider' => 'openai',
                'name' => 'My OpenAI',
                'api_key' => 'sk-test-key-12345',
            ]);

        $response->assertCreated()
            ->assertJsonPath('connection.provider', 'openai')
            ->assertJsonPath('connection.name', 'My OpenAI')
            ->assertJsonPath('connection.has_api_key', true)
            ->assertJsonPath('connection.is_active', true);

        $this->assertDatabaseHas('ai_connections', [
            'user_id' => $this->user->id,
            'provider' => 'openai',
            'name' => 'My OpenAI',
        ]);
    }

    public function test_api_key_is_encrypted_in_database(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-connections', [
                'provider' => 'openai',
                'name' => 'My OpenAI',
                'api_key' => 'sk-test-key-12345',
            ]);

        $response->assertCreated();

        $connection = AIConnection::find($response->json('connection.id'));

        // Verify the key is encrypted (not stored as plaintext)
        $this->assertNotEquals('sk-test-key-12345', $connection->api_key_encrypted);
        // But can be decrypted back
        $this->assertEquals('sk-test-key-12345', $connection->getApiKey());
    }

    public function test_api_key_is_never_exposed_in_response(): void
    {
        $connection = AIConnection::factory()->openai()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/ai-connections/'.$connection->id);

        $response->assertOk()
            ->assertJsonMissing(['api_key'])
            ->assertJsonMissing(['api_key_encrypted']);

        // But has_api_key and masked_api_key should be present
        $response->assertJsonPath('connection.has_api_key', true);
        $this->assertNotNull($response->json('connection.masked_api_key'));
    }

    public function test_user_can_create_connection_without_api_key_for_local_providers(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-connections', [
                'provider' => 'ollama',
                'name' => 'My Local Ollama',
                'base_url' => 'http://localhost:11434',
            ]);

        $response->assertCreated()
            ->assertJsonPath('connection.provider', 'ollama')
            ->assertJsonPath('connection.has_api_key', false);
    }

    public function test_api_key_required_for_cloud_providers(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-connections', [
                'provider' => 'openai',
                'name' => 'My OpenAI',
                // No api_key provided
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['api_key']);
    }

    public function test_user_can_update_ai_connection(): void
    {
        $connection = AIConnection::factory()->openai()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/ai-connections/'.$connection->id, [
                'name' => 'Updated Name',
            ]);

        $response->assertOk()
            ->assertJsonPath('connection.name', 'Updated Name');

        $this->assertDatabaseHas('ai_connections', [
            'id' => $connection->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_cannot_update_other_users_connection(): void
    {
        $otherUser = User::factory()->create();
        $connection = AIConnection::factory()->openai()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/ai-connections/'.$connection->id, [
                'name' => 'Hacked Name',
            ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_ai_connection(): void
    {
        $connection = AIConnection::factory()->openai()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/ai-connections/'.$connection->id);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('ai_connections', ['id' => $connection->id]);
    }

    public function test_user_cannot_delete_other_users_connection(): void
    {
        $otherUser = User::factory()->create();
        $connection = AIConnection::factory()->openai()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/ai-connections/'.$connection->id);

        $response->assertForbidden();
    }

    public function test_setting_default_connection_unsets_previous_default(): void
    {
        $oldDefault = AIConnection::factory()->openai()->default()->create(['user_id' => $this->user->id]);
        $newConnection = AIConnection::factory()->anthropic()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/ai-connections/'.$newConnection->id, [
                'is_default' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('connection.is_default', true);

        // Old default should be unset
        $oldDefault->refresh();
        $this->assertFalse($oldDefault->is_default);
    }

    public function test_user_can_toggle_connection_active_status(): void
    {
        $connection = AIConnection::factory()->openai()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/ai-connections/'.$connection->id, [
                'is_active' => false,
            ]);

        $response->assertOk()
            ->assertJsonPath('connection.is_active', false);
    }

    public function test_user_can_get_available_providers(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/ai-connections/providers');

        $response->assertOk()
            ->assertJsonStructure([
                'providers' => [
                    'openai' => ['name', 'requires_api_key', 'default_base_url'],
                    'anthropic' => ['name', 'requires_api_key', 'default_base_url'],
                    'openrouter' => ['name', 'requires_api_key', 'default_base_url'],
                    'ollama' => ['name', 'requires_api_key', 'default_base_url'],
                ],
            ]);
    }

    /**
     * @group requires-vite
     */
    public function test_settings_ai_page_renders_correctly(): void
    {
        $this->markTestSkipped('This test requires Vite to be built (yarn run build).');
    }

    public function test_provider_validation_rejects_invalid_provider(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-connections', [
                'provider' => 'invalid_provider',
                'name' => 'Test',
                'api_key' => 'test-key',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['provider']);
    }

    public function test_name_is_required(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai-connections', [
                'provider' => 'openai',
                'api_key' => 'test-key',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_updating_api_key_resets_test_status(): void
    {
        $connection = AIConnection::factory()->openai()->tested()->create([
            'user_id' => $this->user->id,
        ]);

        $this->assertEquals('success', $connection->last_test_status);
        $this->assertNotNull($connection->last_tested_at);

        $response = $this->actingAs($this->user)
            ->patchJson('/api/ai-connections/'.$connection->id, [
                'api_key' => 'new-api-key-12345',
            ]);

        $response->assertOk()
            ->assertJsonPath('connection.last_test_status', 'pending');

        $connection->refresh();
        $this->assertEquals('pending', $connection->last_test_status);
        $this->assertNull($connection->last_tested_at);
    }

    public function test_unauthenticated_user_cannot_access_ai_connections(): void
    {
        $response = $this->getJson('/api/ai-connections');

        $response->assertUnauthorized();
    }

    public function test_masked_api_key_format(): void
    {
        $connection = AIConnection::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $connection->setApiKey('sk-proj-1234567890abcdefghijklmnop');
        $connection->save();

        $response = $this->actingAs($this->user)
            ->getJson('/api/ai-connections/'.$connection->id);

        $response->assertOk();

        // Masked key should show first 4 and last 4 characters
        $maskedKey = $response->json('connection.masked_api_key');
        $this->assertStringStartsWith('sk-p', $maskedKey);
        $this->assertStringEndsWith('mnop', $maskedKey);
        $this->assertStringContainsString('*', $maskedKey);
    }
}
