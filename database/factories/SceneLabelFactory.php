<?php

namespace Database\Factories;

use App\Models\Novel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SceneLabel>
 */
class SceneLabelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899', '#6B7280'];

        return [
            'novel_id' => Novel::factory(),
            'name' => $this->faker->randomElement(['Action', 'Romance', 'Flashback', 'Important', 'Needs Review', 'Draft', 'Final']),
            'color' => $this->faker->randomElement($colors),
            'position' => 0,
        ];
    }
}
