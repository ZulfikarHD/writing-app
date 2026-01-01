<?php

namespace App\Services\AI\Providers;

use App\Models\AIConnection;

class OpenAIProvider extends BaseProvider
{
    /**
     * {@inheritDoc}
     */
    public function testConnection(AIConnection $connection): array
    {
        try {
            $models = $this->fetchModels($connection);

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
        $headers = $this->getAuthHeaders($connection);

        $response = $this->get("{$baseUrl}/models", $headers);

        $models = collect($response['data'] ?? [])
            ->filter(function ($model) {
                // Filter to only show chat models
                $id = $model['id'] ?? '';

                return str_contains($id, 'gpt')
                    || str_contains($id, 'o1')
                    || str_contains($id, 'chatgpt');
            })
            ->map(function ($model) {
                return [
                    'id' => $model['id'],
                    'name' => $this->formatModelName($model['id']),
                    'context_length' => $this->getContextLength($model['id']),
                    'owned_by' => $model['owned_by'] ?? 'openai',
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
        return 'OpenAI';
    }

    /**
     * {@inheritDoc}
     */
    public function requiresApiKey(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultBaseUrl(): ?string
    {
        return 'https://api.openai.com/v1';
    }

    /**
     * Format the model ID into a readable name.
     */
    protected function formatModelName(string $modelId): string
    {
        $mapping = [
            'gpt-4o' => 'GPT-4o',
            'gpt-4o-mini' => 'GPT-4o Mini',
            'gpt-4-turbo' => 'GPT-4 Turbo',
            'gpt-4-turbo-preview' => 'GPT-4 Turbo Preview',
            'gpt-4' => 'GPT-4',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            'o1-preview' => 'O1 Preview',
            'o1-mini' => 'O1 Mini',
        ];

        return $mapping[$modelId] ?? $modelId;
    }

    /**
     * Get the context length for a model.
     */
    protected function getContextLength(string $modelId): int
    {
        $contextLengths = [
            'gpt-4o' => 128000,
            'gpt-4o-mini' => 128000,
            'gpt-4-turbo' => 128000,
            'gpt-4-turbo-preview' => 128000,
            'gpt-4' => 8192,
            'gpt-3.5-turbo' => 16385,
            'o1-preview' => 128000,
            'o1-mini' => 128000,
        ];

        foreach ($contextLengths as $key => $length) {
            if (str_starts_with($modelId, $key)) {
                return $length;
            }
        }

        return 8192; // Default
    }
}
