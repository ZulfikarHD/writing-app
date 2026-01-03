<?php

namespace Database\Factories;

use App\Models\Prompt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prompt>
 */
class PromptFactory extends Factory
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
            'name' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(Prompt::getTypes()),
            'system_message' => fake()->paragraphs(2, true),
            'user_message' => '{{selection}}',
            'is_system' => false,
            'is_active' => true,
            'sort_order' => 0,
            'usage_count' => 0,
        ];
    }

    /**
     * Create a system prompt.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'is_system' => true,
        ]);
    }

    /**
     * Create a chat prompt.
     */
    public function chat(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Prompt::TYPE_CHAT,
        ]);
    }

    /**
     * Create a prose prompt.
     */
    public function prose(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Prompt::TYPE_PROSE,
        ]);
    }

    /**
     * Create a replacement prompt.
     */
    public function replacement(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Prompt::TYPE_REPLACEMENT,
        ]);
    }

    /**
     * Create a summary prompt.
     */
    public function summary(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Prompt::TYPE_SUMMARY,
        ]);
    }

    /**
     * Create an inactive prompt.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a prompt with usage.
     */
    public function withUsage(int $count = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_count' => $count,
        ]);
    }
}
