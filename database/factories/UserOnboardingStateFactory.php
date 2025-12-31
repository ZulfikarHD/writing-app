<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserOnboardingState>
 */
class UserOnboardingStateFactory extends Factory
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
            'welcome_completed' => false,
            'first_novel_created' => false,
            'editor_toured' => false,
            'codex_introduced' => false,
            'ai_chat_introduced' => false,
            'onboarding_skipped' => false,
            'completed_at' => null,
        ];
    }

    /**
     * Mark welcome as completed.
     */
    public function welcomeCompleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'welcome_completed' => true,
        ]);
    }

    /**
     * Mark all steps as completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'welcome_completed' => true,
            'first_novel_created' => true,
            'editor_toured' => true,
            'codex_introduced' => true,
            'ai_chat_introduced' => true,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark onboarding as skipped.
     */
    public function skipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_skipped' => true,
            'completed_at' => now(),
        ]);
    }
}
