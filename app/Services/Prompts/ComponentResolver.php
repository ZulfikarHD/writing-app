<?php

namespace App\Services\Prompts;

use App\Models\PromptComponent;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Resolves component references in prompt templates.
 *
 * Components use the syntax: [[component_name]]
 */
class ComponentResolver
{
    /**
     * Cached components for the current user.
     *
     * @var Collection<int, PromptComponent>|null
     */
    protected ?Collection $components = null;

    /**
     * User ID for component lookup.
     */
    protected ?int $userId = null;

    /**
     * Maximum nesting depth to prevent infinite loops.
     */
    protected int $maxDepth = 10;

    /**
     * Set the user for component resolution.
     */
    public function setUser(User $user): self
    {
        $this->userId = $user->id;
        $this->components = null; // Reset cache

        return $this;
    }

    /**
     * Set the user ID directly.
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        $this->components = null; // Reset cache

        return $this;
    }

    /**
     * Resolve all component references in a text.
     */
    public function resolve(string $text, int $depth = 0): string
    {
        if ($depth >= $this->maxDepth) {
            return $text; // Prevent infinite loops
        }

        // Match [[component_name]] patterns
        $resolved = preg_replace_callback(
            '/\[\[([a-zA-Z_][a-zA-Z0-9_]*)\]\]/',
            fn ($matches) => $this->resolveComponent($matches[1], $depth),
            $text
        ) ?? $text;

        // Check if we need to resolve more (nested components)
        if ($resolved !== $text && preg_match('/\[\[[a-zA-Z_][a-zA-Z0-9_]*\]\]/', $resolved)) {
            return $this->resolve($resolved, $depth + 1);
        }

        return $resolved;
    }

    /**
     * Resolve a single component by name.
     */
    protected function resolveComponent(string $name, int $depth): string
    {
        $component = $this->getComponent($name);

        if (! $component) {
            return "[[{$name}]]"; // Return as-is if not found
        }

        // Recursively resolve nested components
        return $this->resolve($component->content, $depth + 1);
    }

    /**
     * Get a component by name.
     */
    protected function getComponent(string $name): ?PromptComponent
    {
        $this->loadComponents();

        return $this->components?->firstWhere('name', $name);
    }

    /**
     * Load all accessible components for the user.
     */
    protected function loadComponents(): void
    {
        if ($this->components !== null) {
            return;
        }

        if ($this->userId === null) {
            $this->components = collect();

            return;
        }

        $this->components = PromptComponent::accessibleBy($this->userId)->get();
    }

    /**
     * Get all component names referenced in a text.
     *
     * @return array<string>
     */
    public function getReferencedComponents(string $text): array
    {
        preg_match_all('/\[\[([a-zA-Z_][a-zA-Z0-9_]*)\]\]/', $text, $matches);

        return array_unique($matches[1] ?? []);
    }

    /**
     * Validate that all referenced components exist.
     *
     * @return array<string> List of missing component names
     */
    public function validateComponents(string $text): array
    {
        $referenced = $this->getReferencedComponents($text);
        $missing = [];

        foreach ($referenced as $name) {
            if (! $this->getComponent($name)) {
                $missing[] = $name;
            }
        }

        return $missing;
    }

    /**
     * Get component usages - find which prompts use a component.
     *
     * @return Collection<int, array{prompt_id: int, prompt_name: string}>
     */
    public function getComponentUsages(string $componentName, int $userId): Collection
    {
        return \App\Models\Prompt::accessibleBy($userId)
            ->where(function ($query) use ($componentName) {
                $query->where('system_message', 'like', "%[[{$componentName}]]%")
                    ->orWhere('user_message', 'like', "%[[{$componentName}]]%");
            })
            ->get(['id', 'name'])
            ->map(fn ($prompt) => [
                'prompt_id' => $prompt->id,
                'prompt_name' => $prompt->name,
            ]);
    }
}
