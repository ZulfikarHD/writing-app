<?php

namespace Database\Factories;

use App\Models\PenName;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Novel>
 */
class NovelFactory extends Factory
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
            'pen_name_id' => null,
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'genre' => fake()->randomElement(['Fantasy', 'Sci-Fi', 'Romance', 'Mystery', 'Thriller', 'Horror', 'Literary Fiction']),
            'pov' => fake()->randomElement(['first_person', 'third_person_limited', 'third_person_omniscient']),
            'tense' => fake()->randomElement(['past', 'present']),
            'cover_path' => null,
            'word_count' => fake()->numberBetween(0, 100000),
            'chapter_count' => fake()->numberBetween(0, 30),
            'status' => fake()->randomElement(['draft', 'in_progress', 'completed']),
            'last_edited_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function withPenName(?PenName $penName = null): static
    {
        return $this->state(fn (array $attributes) => [
            'pen_name_id' => $penName?->id ?? PenName::factory(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'word_count' => 0,
            'chapter_count' => 0,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'word_count' => fake()->numberBetween(5000, 50000),
            'chapter_count' => fake()->numberBetween(3, 15),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'word_count' => fake()->numberBetween(60000, 120000),
            'chapter_count' => fake()->numberBetween(20, 40),
        ]);
    }
}
