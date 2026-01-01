<?php

namespace App\Services\AI\Providers;

use App\Models\AIConnection;

class OllamaProvider extends BaseProvider
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
                    'message' => 'Connected to Ollama, but no models are installed. Run "ollama pull <model>" to download models.',
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

        // Ollama uses /api/tags endpoint
        $response = $this->get("{$baseUrl}/api/tags");

        $models = collect($response['models'] ?? [])
            ->map(function ($model) {
                return [
                    'id' => $model['name'],
                    'name' => $this->formatModelName($model['name']),
                    'context_length' => $this->estimateContextLength($model['name']),
                    'size' => $model['size'] ?? null,
                    'modified_at' => $model['modified_at'] ?? null,
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
        return 'Ollama';
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
        return 'http://localhost:11434';
    }

    /**
     * Format the model name for display.
     */
    protected function formatModelName(string $name): string
    {
        // Remove version tags and format nicely
        $baseName = explode(':', $name)[0];

        return ucwords(str_replace(['-', '_'], ' ', $baseName));
    }

    /**
     * Estimate context length based on model name.
     */
    protected function estimateContextLength(string $name): int
    {
        $name = strtolower($name);

        if (str_contains($name, 'llama3') || str_contains($name, 'llama-3')) {
            return 8192;
        }

        if (str_contains($name, 'llama2') || str_contains($name, 'llama-2')) {
            return 4096;
        }

        if (str_contains($name, 'mistral')) {
            return 32768;
        }

        if (str_contains($name, 'mixtral')) {
            return 32768;
        }

        if (str_contains($name, 'codellama')) {
            return 16384;
        }

        return 4096; // Default
    }
}
