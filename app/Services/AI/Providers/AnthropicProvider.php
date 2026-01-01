<?php

namespace App\Services\AI\Providers;

use App\Models\AIConnection;
use Illuminate\Support\Facades\Http;

class AnthropicProvider extends BaseProvider
{
    /**
     * {@inheritDoc}
     */
    public function testConnection(AIConnection $connection): array
    {
        try {
            // Anthropic doesn't have a models endpoint, so we make a minimal messages request
            $baseUrl = $this->getBaseUrl($connection);
            $apiKey = $connection->getApiKey();

            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post("{$baseUrl}/v1/messages", [
                    'model' => 'claude-3-haiku-20240307',
                    'max_tokens' => 1,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Hi'],
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connection successful!',
                    'model_count' => count($this->getAvailableModels()),
                ];
            }

            $error = $response->json();

            return [
                'success' => false,
                'message' => $error['error']['message'] ?? 'Connection test failed.',
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
        // Anthropic doesn't have a models endpoint, return known models
        return $this->getAvailableModels();
    }

    /**
     * {@inheritDoc}
     */
    public function getProviderName(): string
    {
        return 'Anthropic';
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
        return 'https://api.anthropic.com';
    }

    /**
     * Get the list of known Claude models.
     *
     * @return array<int, array{id: string, name: string, context_length: int}>
     */
    protected function getAvailableModels(): array
    {
        return [
            [
                'id' => 'claude-3-5-sonnet-20241022',
                'name' => 'Claude 3.5 Sonnet',
                'context_length' => 200000,
            ],
            [
                'id' => 'claude-3-5-haiku-20241022',
                'name' => 'Claude 3.5 Haiku',
                'context_length' => 200000,
            ],
            [
                'id' => 'claude-3-opus-20240229',
                'name' => 'Claude 3 Opus',
                'context_length' => 200000,
            ],
            [
                'id' => 'claude-3-sonnet-20240229',
                'name' => 'Claude 3 Sonnet',
                'context_length' => 200000,
            ],
            [
                'id' => 'claude-3-haiku-20240307',
                'name' => 'Claude 3 Haiku',
                'context_length' => 200000,
            ],
        ];
    }
}
