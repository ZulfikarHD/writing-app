<?php

namespace App\Services\AI\Providers;

use App\Models\AIConnection;

class GroqProvider extends BaseProvider
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
                // Filter to active models
                return ($model['active'] ?? true) === true;
            })
            ->map(function ($model) {
                return [
                    'id' => $model['id'],
                    'name' => $this->formatModelName($model['id']),
                    'context_length' => $model['context_window'] ?? 8192,
                    'owned_by' => $model['owned_by'] ?? 'groq',
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
        return 'Groq';
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
        return 'https://api.groq.com/openai/v1';
    }

    /**
     * Format the model ID into a readable name.
     */
    protected function formatModelName(string $modelId): string
    {
        $mapping = [
            'llama-3.3-70b-versatile' => 'LLaMA 3.3 70B Versatile',
            'llama-3.1-70b-versatile' => 'LLaMA 3.1 70B Versatile',
            'llama-3.1-8b-instant' => 'LLaMA 3.1 8B Instant',
            'llama3-70b-8192' => 'LLaMA 3 70B',
            'llama3-8b-8192' => 'LLaMA 3 8B',
            'mixtral-8x7b-32768' => 'Mixtral 8x7B',
            'gemma-7b-it' => 'Gemma 7B IT',
            'gemma2-9b-it' => 'Gemma 2 9B IT',
        ];

        return $mapping[$modelId] ?? $modelId;
    }
}
