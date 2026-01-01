<?php

namespace App\Services\AI\Providers;

use App\Models\AIConnection;

class OpenAICompatibleProvider extends BaseProvider
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

        // Add any custom headers from settings
        $customHeaders = $connection->settings['headers'] ?? [];
        $headers = array_merge($headers, $customHeaders);

        $response = $this->get("{$baseUrl}/models", $headers);

        $models = collect($response['data'] ?? [])
            ->map(function ($model) {
                return [
                    'id' => $model['id'],
                    'name' => $model['id'],
                    'context_length' => $model['context_length'] ?? 8192,
                    'owned_by' => $model['owned_by'] ?? 'unknown',
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
        return 'OpenAI Compatible';
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
        return null; // User must specify
    }
}
