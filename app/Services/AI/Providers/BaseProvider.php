<?php

namespace App\Services\AI\Providers;

use App\Models\AIConnection;
use App\Services\AI\Contracts\AIProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseProvider implements AIProviderInterface
{
    /**
     * Get the base URL for the connection.
     */
    protected function getBaseUrl(AIConnection $connection): string
    {
        return $connection->base_url ?? $this->getDefaultBaseUrl() ?? '';
    }

    /**
     * Get the authorization header for the connection.
     *
     * @return array<string, string>
     */
    protected function getAuthHeaders(AIConnection $connection): array
    {
        $apiKey = $connection->getApiKey();

        if (! $apiKey) {
            return [];
        }

        return [
            'Authorization' => "Bearer {$apiKey}",
        ];
    }

    /**
     * Make a GET request to the provider.
     *
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     */
    protected function get(string $url, array $headers = []): array
    {
        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->get($url);

        $response->throw();

        return $response->json();
    }

    /**
     * Make a POST request to the provider.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     */
    protected function post(string $url, array $data = [], array $headers = []): array
    {
        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->post($url, $data);

        $response->throw();

        return $response->json();
    }

    /**
     * Handle common connection errors.
     *
     * @return array{success: bool, message: string}
     */
    protected function handleConnectionError(\Exception $e): array
    {
        Log::warning("AI Connection test failed: {$e->getMessage()}");

        if ($e instanceof ConnectionException) {
            return [
                'success' => false,
                'message' => 'Could not connect to the server. Please check the URL and ensure the service is running.',
            ];
        }

        if ($e instanceof RequestException) {
            $status = $e->response->status();
            $body = $e->response->json();

            return match ($status) {
                401 => [
                    'success' => false,
                    'message' => 'Invalid API key. Please check your credentials.',
                ],
                403 => [
                    'success' => false,
                    'message' => 'Access forbidden. Your API key may not have the required permissions.',
                ],
                404 => [
                    'success' => false,
                    'message' => 'API endpoint not found. Please verify the base URL.',
                ],
                429 => [
                    'success' => false,
                    'message' => 'Rate limited. Please try again later.',
                ],
                default => [
                    'success' => false,
                    'message' => $body['error']['message'] ?? "Request failed with status {$status}.",
                ],
            };
        }

        return [
            'success' => false,
            'message' => 'Connection test failed: '.$e->getMessage(),
        ];
    }
}
