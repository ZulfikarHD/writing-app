<?php

namespace App\Services\AI\Contracts;

use App\Models\AIConnection;

interface AIProviderInterface
{
    /**
     * Test the connection to the AI provider.
     *
     * @return array{success: bool, message: string, model_count?: int}
     */
    public function testConnection(AIConnection $connection): array;

    /**
     * Fetch available models from the provider.
     *
     * @return array<int, array{id: string, name: string, context_length?: int, pricing?: array}>
     */
    public function fetchModels(AIConnection $connection): array;

    /**
     * Get the provider identifier.
     */
    public function getProviderName(): string;

    /**
     * Check if the provider requires an API key.
     */
    public function requiresApiKey(): bool;

    /**
     * Get the default base URL for this provider.
     */
    public function getDefaultBaseUrl(): ?string;
}
