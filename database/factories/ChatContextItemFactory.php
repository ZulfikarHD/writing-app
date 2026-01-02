<?php

namespace Database\Factories;

use App\Models\ChatContextItem;
use App\Models\ChatThread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatContextItem>
 */
class ChatContextItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<ChatContextItem>
     */
    protected $model = ChatContextItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thread_id' => ChatThread::factory(),
            'context_type' => $this->faker->randomElement(['scene', 'codex', 'custom']),
            'reference_id' => null,
            'custom_content' => $this->faker->paragraph(),
            'is_active' => true,
            'created_at' => now(),
        ];
    }

    /**
     * Indicate that the context item is for a scene.
     */
    public function scene(?int $sceneId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'context_type' => 'scene',
            'reference_id' => $sceneId,
            'custom_content' => null,
        ]);
    }

    /**
     * Indicate that the context item is for a codex entry.
     */
    public function codex(?int $codexEntryId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'context_type' => 'codex',
            'reference_id' => $codexEntryId,
            'custom_content' => null,
        ]);
    }

    /**
     * Indicate that the context item is custom content.
     */
    public function custom(?string $content = null): static
    {
        return $this->state(fn (array $attributes) => [
            'context_type' => 'custom',
            'reference_id' => null,
            'custom_content' => $content ?? $this->faker->paragraphs(2, true),
        ]);
    }

    /**
     * Indicate that the context item is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
