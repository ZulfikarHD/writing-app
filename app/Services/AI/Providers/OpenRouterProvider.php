<?php

namespace App\Services\AI\Providers;

use App\Models\AIConnection;

/**
 * OpenRouter provider for accessing multiple AI models through a unified API.
 *
 * @see https://openrouter.ai/docs/api/reference/overview
 */
class OpenRouterProvider extends BaseProvider
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
        $headers = $this->getOpenRouterHeaders($connection);

        $response = $this->get("{$baseUrl}/models", $headers);

        $models = collect($response['data'] ?? [])
            ->map(function ($model) {
                return [
                    'id' => $model['id'],
                    'name' => $model['name'] ?? $model['id'],
                    'context_length' => $model['context_length'] ?? 8192,
                    'pricing' => [
                        'prompt' => $model['pricing']['prompt'] ?? null,
                        'completion' => $model['pricing']['completion'] ?? null,
                    ],
                    'description' => $model['description'] ?? null,
                    'top_provider' => $model['top_provider']['context_length'] ?? null,
                    'per_request_limits' => $model['per_request_limits'] ?? null,
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
        return 'OpenRouter';
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
        return 'https://openrouter.ai/api/v1';
    }

    /**
     * Get OpenRouter-specific headers including app identification.
     *
     * Per OpenRouter docs, these headers help identify your app:
     * - HTTP-Referer: Site URL for rankings on openrouter.ai
     * - X-Title: Site title for rankings on openrouter.ai
     *
     * @see https://openrouter.ai/docs/api/reference/overview
     *
     * @return array<string, string>
     */
    protected function getOpenRouterHeaders(AIConnection $connection): array
    {
        return array_merge($this->getAuthHeaders($connection), [
            'HTTP-Referer' => config('app.url', 'http://localhost'),
            'X-Title' => config('app.name', 'NovelWrite'),
            'Content-Type' => 'application/json',
        ]);
    }
}
