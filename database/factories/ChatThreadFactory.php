<?php

namespace Database\Factories;

use App\Models\AIConnection;
use App\Models\Novel;
use App\Models\Scene;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChatThread>
 */
class ChatThreadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'novel_id' => Novel::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'model' => fake()->randomElement(['gpt-4o', 'gpt-4o-mini', 'claude-3-sonnet', null]),
            'connection_id' => null,
            'context_settings' => null,
            'is_pinned' => false,
            'linked_scene_id' => null,
            'archived_at' => null,
        ];
    }

    /**
     * Thread for a specific novel.
     */
    public function forNovel(Novel $novel): static
    {
        return $this->state(fn (array $attributes) => [
            'novel_id' => $novel->id,
            'user_id' => $novel->user_id,
        ]);
    }

    /**
     * Thread with a specific connection.
     */
    public function withConnection(?AIConnection $connection = null): static
    {
        return $this->state(fn (array $attributes) => [
            'connection_id' => $connection?->id ?? AIConnection::factory(),
        ]);
    }

    /**
     * Pinned thread.
     */
    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pinned' => true,
        ]);
    }

    /**
     * Archived thread.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'archived_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Thread linked to a specific scene.
     */
    public function linkedToScene(?Scene $scene = null): static
    {
        return $this->state(fn (array $attributes) => [
            'linked_scene_id' => $scene?->id ?? Scene::factory(),
        ]);
    }

    /**
     * Thread without a title (will be auto-generated).
     */
    public function untitled(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => null,
        ]);
    }
}
