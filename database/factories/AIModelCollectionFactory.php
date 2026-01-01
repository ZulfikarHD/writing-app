<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AIModelCollection>
 */
class AIModelCollectionFactory extends Factory
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
            'name' => fake()->words(2, true).' Collection',
            'models' => [
                ['provider' => 'openai', 'model' => 'gpt-4o'],
                ['provider' => 'openai', 'model' => 'gpt-4o-mini'],
            ],
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function favorites(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Favorites',
            'models' => [
                ['provider' => 'openai', 'model' => 'gpt-4o'],
                ['provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022'],
            ],
        ]);
    }
}
