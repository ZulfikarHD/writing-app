<?php

namespace Database\Factories;

use App\Models\Scene;
use App\Models\SceneSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SceneSection>
 */
class SceneSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(array_keys(SceneSection::TYPES));

        return [
            'scene_id' => Scene::factory(),
            'type' => $type,
            'title' => fake()->optional(0.7)->sentence(3),
            'content' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => fake()->paragraph(),
                            ],
                        ],
                    ],
                ],
            ],
            'color' => SceneSection::TYPE_COLORS[$type],
            'is_collapsed' => false,
            'exclude_from_ai' => SceneSection::getDefaultExcludeFromAi($type),
            'sort_order' => 0,
        ];
    }

    /**
     * Set section type to content.
     */
    public function content(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => SceneSection::TYPE_CONTENT,
            'color' => SceneSection::TYPE_COLORS[SceneSection::TYPE_CONTENT],
            'exclude_from_ai' => false,
        ]);
    }

    /**
     * Set section type to note.
     */
    public function note(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => SceneSection::TYPE_NOTE,
            'color' => SceneSection::TYPE_COLORS[SceneSection::TYPE_NOTE],
            'exclude_from_ai' => true,
        ]);
    }

    /**
     * Set section type to alternative.
     */
    public function alternative(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => SceneSection::TYPE_ALTERNATIVE,
            'color' => SceneSection::TYPE_COLORS[SceneSection::TYPE_ALTERNATIVE],
            'exclude_from_ai' => true,
        ]);
    }

    /**
     * Set section type to beat.
     */
    public function beat(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => SceneSection::TYPE_BEAT,
            'color' => SceneSection::TYPE_COLORS[SceneSection::TYPE_BEAT],
            'exclude_from_ai' => false,
        ]);
    }

    /**
     * Set section as collapsed.
     */
    public function collapsed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_collapsed' => true,
        ]);
    }

    /**
     * Set section to be excluded from AI context.
     */
    public function excludedFromAi(): static
    {
        return $this->state(fn (array $attributes) => [
            'exclude_from_ai' => true,
        ]);
    }
}
