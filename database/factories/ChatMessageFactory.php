<?php

namespace Database\Factories;

use App\Models\ChatThread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thread_id' => ChatThread::factory(),
            'role' => 'user',
            'content' => fake()->paragraph(3),
            'model_used' => null,
            'tokens_input' => null,
            'tokens_output' => null,
            'context_snapshot' => null,
        ];
    }

    /**
     * Message for a specific thread.
     */
    public function forThread(ChatThread $thread): static
    {
        return $this->state(fn (array $attributes) => [
            'thread_id' => $thread->id,
        ]);
    }

    /**
     * User message.
     */
    public function fromUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'user',
            'model_used' => null,
            'tokens_input' => null,
            'tokens_output' => null,
        ]);
    }

    /**
     * Assistant (AI) message.
     */
    public function fromAssistant(?string $model = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'assistant',
            'model_used' => $model ?? fake()->randomElement(['gpt-4o', 'gpt-4o-mini', 'claude-3-sonnet']),
            'tokens_input' => fake()->numberBetween(100, 2000),
            'tokens_output' => fake()->numberBetween(50, 1000),
        ]);
    }

    /**
     * System message.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'system',
            'content' => 'You are a helpful writing assistant.',
        ]);
    }

    /**
     * Message with context snapshot.
     */
    public function withContext(array $context): static
    {
        return $this->state(fn (array $attributes) => [
            'context_snapshot' => $context,
        ]);
    }

    /**
     * Create a conversation pair (user question + assistant response).
     */
    public function conversationPair(ChatThread $thread, string $userMessage, string $assistantResponse): array
    {
        return [
            self::new()->forThread($thread)->fromUser()->create(['content' => $userMessage]),
            self::new()->forThread($thread)->fromAssistant()->create(['content' => $assistantResponse]),
        ];
    }
}
