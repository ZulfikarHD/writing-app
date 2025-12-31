<?php

namespace Database\Factories;

use App\Models\Chapter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Scene>
 */
class SceneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paragraphs = fake()->paragraphs(3);
        $wordCount = str_word_count(implode(' ', $paragraphs));

        return [
            'chapter_id' => Chapter::factory(),
            'title' => fake()->sentence(3),
            'content' => $this->generateTipTapContent($paragraphs),
            'summary' => null,
            'position' => 0,
            'pov_character_id' => null,
            'status' => 'draft',
            'word_count' => $wordCount,
            'subtitle' => null,
            'notes' => null,
            'exclude_from_ai' => false,
            'metadata' => null,
            'archived_at' => null,
        ];
    }

    /**
     * Generate TipTap-compatible JSON content from paragraphs.
     *
     * @param  array<int, string>  $paragraphs
     * @return array<string, mixed>
     */
    private function generateTipTapContent(array $paragraphs): array
    {
        $content = [];

        foreach ($paragraphs as $paragraph) {
            $content[] = [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $paragraph,
                    ],
                ],
            ];
        }

        return [
            'type' => 'doc',
            'content' => $content,
        ];
    }

    /**
     * Create an empty scene.
     */
    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                    ],
                ],
            ],
            'word_count' => 0,
        ]);
    }

    /**
     * Set the scene position.
     */
    public function position(int $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }

    /**
     * Set scene status.
     */
    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    /**
     * Create an archived scene.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'archived_at' => now(),
        ]);
    }
}
