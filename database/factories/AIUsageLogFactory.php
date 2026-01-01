<?php

namespace Database\Factories;

use App\Models\AIConnection;
use App\Models\AIUsageLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AIUsageLog>
 */
class AIUsageLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'connection_id' => AIConnection::factory(),
            'model' => fake()->randomElement(['gpt-4o', 'gpt-4o-mini', 'claude-3-5-sonnet-20241022']),
            'input_tokens' => fake()->numberBetween(100, 5000),
            'output_tokens' => fake()->numberBetween(100, 2000),
            'estimated_cost' => fake()->randomFloat(6, 0.001, 0.5),
            'feature_type' => fake()->randomElement([
                AIUsageLog::FEATURE_CHAT,
                AIUsageLog::FEATURE_PROSE,
                AIUsageLog::FEATURE_PROMPT,
            ]),
            'metadata' => null,
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function chat(): static
    {
        return $this->state(fn (array $attributes) => [
            'feature_type' => AIUsageLog::FEATURE_CHAT,
        ]);
    }

    public function prose(): static
    {
        return $this->state(fn (array $attributes) => [
            'feature_type' => AIUsageLog::FEATURE_PROSE,
        ]);
    }

    public function prompt(): static
    {
        return $this->state(fn (array $attributes) => [
            'feature_type' => AIUsageLog::FEATURE_PROMPT,
        ]);
    }
}
