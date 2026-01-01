<?php

namespace Database\Factories;

use App\Models\AIConnection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AIConnection>
 */
class AIConnectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provider = fake()->randomElement([
            AIConnection::PROVIDER_OPENAI,
            AIConnection::PROVIDER_ANTHROPIC,
            AIConnection::PROVIDER_OPENROUTER,
        ]);

        return [
            'user_id' => User::factory(),
            'provider' => $provider,
            'name' => fake()->words(2, true).' Connection',
            'api_key_encrypted' => Crypt::encryptString('sk-test-'.fake()->sha256()),
            'base_url' => AIConnection::getProviders()[$provider]['default_base_url'],
            'settings' => null,
            'is_active' => true,
            'is_default' => false,
            'last_tested_at' => null,
            'last_test_status' => AIConnection::STATUS_PENDING,
        ];
    }

    public function openai(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => AIConnection::PROVIDER_OPENAI,
            'name' => 'OpenAI',
            'base_url' => 'https://api.openai.com/v1',
        ]);
    }

    public function anthropic(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => AIConnection::PROVIDER_ANTHROPIC,
            'name' => 'Anthropic',
            'base_url' => 'https://api.anthropic.com',
        ]);
    }

    public function ollama(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => AIConnection::PROVIDER_OLLAMA,
            'name' => 'Ollama Local',
            'api_key_encrypted' => null,
            'base_url' => 'http://localhost:11434',
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function tested(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_tested_at' => now(),
            'last_test_status' => AIConnection::STATUS_SUCCESS,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_tested_at' => now(),
            'last_test_status' => AIConnection::STATUS_FAILED,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
