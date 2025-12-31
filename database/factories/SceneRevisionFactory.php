<?php

namespace Database\Factories;

use App\Models\Scene;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SceneRevision>
 */
class SceneRevisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paragraphs = fake()->paragraphs(2);
        $wordCount = str_word_count(implode(' ', $paragraphs));

        return [
            'scene_id' => Scene::factory(),
            'content' => $this->generateTipTapContent($paragraphs),
            'word_count' => $wordCount,
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
}
