<?php

namespace Database\Factories;

use App\Models\CodexEntry;
use App\Models\Novel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CodexEntry>
 */
class CodexEntryFactory extends Factory
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
            'type' => fake()->randomElement(CodexEntry::getTypes()),
            'name' => fake()->name(),
            'description' => fake()->paragraph(),
            'ai_context_mode' => CodexEntry::CONTEXT_DETECTED,
            'sort_order' => 0,
            'is_archived' => false,
        ];
    }

    /**
     * Configure the model factory to create a character.
     */
    public function character(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CodexEntry::TYPE_CHARACTER,
            'name' => fake()->name(),
        ]);
    }

    /**
     * Configure the model factory to create a location.
     */
    public function location(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CodexEntry::TYPE_LOCATION,
            'name' => fake()->city(),
        ]);
    }

    /**
     * Configure the model factory to create an item.
     */
    public function item(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CodexEntry::TYPE_ITEM,
            'name' => fake()->word().' '.fake()->randomElement(['Sword', 'Ring', 'Book', 'Amulet']),
        ]);
    }

    /**
     * Configure the model factory to create a lore entry.
     */
    public function lore(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CodexEntry::TYPE_LORE,
            'name' => 'The '.fake()->word().' of '.fake()->lastName(),
        ]);
    }

    /**
     * Configure the model factory to create an organization.
     */
    public function organization(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CodexEntry::TYPE_ORGANIZATION,
            'name' => 'The '.fake()->word().' '.fake()->randomElement(['Guild', 'Order', 'Society', 'Council']),
        ]);
    }

    /**
     * Configure the model factory to create a subplot.
     */
    public function subplot(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CodexEntry::TYPE_SUBPLOT,
            'name' => fake()->sentence(3),
        ]);
    }

    /**
     * Configure the model factory for archived entries.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
        ]);
    }
}
