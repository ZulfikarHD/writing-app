<?php

namespace App\Services\AI\Providers;

use App\Models\AIConnection;

class LMStudioProvider extends BaseProvider
{
    /**
     * {@inheritDoc}
     */
    public function testConnection(AIConnection $connection): array
    {
        try {
            $models = $this->fetchModels($connection);

            if (empty($models)) {
                return [
                    'success' => true,
                    'message' => 'Connected to LM Studio, but no models are currently loaded.',
                    'model_count' => 0,
                ];
            }

            return [
                'success' => true,
                'message' => 'Connection successful!',
                'model_count' => count($models),
            ];
        } catch (\Exception $e) {
            return $this->handleConnectionError($e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fetchModels(AIConnection $connection): array
    {
        $baseUrl = $this->getBaseUrl($connection);

        // LM Studio uses OpenAI-compatible endpoint
        $response = $this->get("{$baseUrl}/models");

        $models = collect($response['data'] ?? [])
            ->map(function ($model) {
                return [
                    'id' => $model['id'],
                    'name' => $this->formatModelName($model['id']),
                    'context_length' => 4096, // LM Studio doesn't always report this
                    'owned_by' => $model['owned_by'] ?? 'local',
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();

        return $models;
    }

    /**
     * {@inheritDoc}
     */
    public function getProviderName(): string
    {
        return 'LM Studio';
    }

    /**
     * {@inheritDoc}
     */
    public function requiresApiKey(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultBaseUrl(): ?string
    {
        return 'http://localhost:1234/v1';
    }

    /**
     * Format the model name for display.
     */
    protected function formatModelName(string $name): string
    {
        // LM Studio model names are often file paths, extract the meaningful part
        $parts = explode('/', $name);
        $baseName = end($parts);

        // Remove common extensions
        $baseName = preg_replace('/\.(gguf|bin|safetensors)$/i', '', $baseName);

        return $baseName;
    }
}
