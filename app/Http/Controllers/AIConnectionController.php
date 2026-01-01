<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAIConnectionRequest;
use App\Http\Requests\UpdateAIConnectionRequest;
use App\Models\AIConnection;
use App\Services\AI\AIServiceFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIConnectionController extends Controller
{
    /**
     * List all AI connections for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $connections = $request->user()
            ->aiConnections()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (AIConnection $conn) => $this->formatConnection($conn));

        return response()->json([
            'connections' => $connections,
        ]);
    }

    /**
     * Store a new AI connection.
     */
    public function store(StoreAIConnectionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $connection = new AIConnection;
        $connection->user_id = $request->user()->id;
        $connection->provider = $validated['provider'];
        $connection->name = $validated['name'];
        $connection->base_url = $validated['base_url'] ?? AIConnection::getProviders()[$validated['provider']]['default_base_url'];
        $connection->settings = $validated['settings'] ?? null;
        $connection->is_active = true;
        $connection->is_default = $validated['is_default'] ?? false;

        // Set encrypted API key if provided
        if (isset($validated['api_key']) && $validated['api_key']) {
            $connection->setApiKey($validated['api_key']);
        }

        // If this is the default connection, unset any existing defaults
        if ($connection->is_default) {
            $request->user()->aiConnections()->update(['is_default' => false]);
        }

        $connection->save();

        return response()->json([
            'connection' => $this->formatConnection($connection),
            'message' => 'Connection created successfully.',
        ], 201);
    }

    /**
     * Get a specific AI connection.
     */
    public function show(Request $request, AIConnection $aiConnection): JsonResponse
    {
        $this->authorizeConnection($request, $aiConnection);

        return response()->json([
            'connection' => $this->formatConnection($aiConnection),
        ]);
    }

    /**
     * Update an AI connection.
     */
    public function update(UpdateAIConnectionRequest $request, AIConnection $aiConnection): JsonResponse
    {
        $this->authorizeConnection($request, $aiConnection);

        $validated = $request->validated();

        if (isset($validated['name'])) {
            $aiConnection->name = $validated['name'];
        }

        if (isset($validated['base_url'])) {
            $aiConnection->base_url = $validated['base_url'];
        }

        if (isset($validated['settings'])) {
            $aiConnection->settings = $validated['settings'];
        }

        if (isset($validated['is_active'])) {
            $aiConnection->is_active = $validated['is_active'];
        }

        if (isset($validated['is_default'])) {
            if ($validated['is_default']) {
                // Unset any existing defaults
                $request->user()->aiConnections()->update(['is_default' => false]);
            }
            $aiConnection->is_default = $validated['is_default'];
        }

        // Update API key if provided
        if (isset($validated['api_key']) && $validated['api_key']) {
            $aiConnection->setApiKey($validated['api_key']);
            // Reset test status when key changes
            $aiConnection->last_test_status = AIConnection::STATUS_PENDING;
            $aiConnection->last_tested_at = null;
        }

        $aiConnection->save();

        return response()->json([
            'connection' => $this->formatConnection($aiConnection),
            'message' => 'Connection updated successfully.',
        ]);
    }

    /**
     * Delete an AI connection.
     */
    public function destroy(Request $request, AIConnection $aiConnection): JsonResponse
    {
        $this->authorizeConnection($request, $aiConnection);

        $aiConnection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Connection deleted successfully.',
        ]);
    }

    /**
     * Test an AI connection.
     */
    public function test(Request $request, AIConnection $aiConnection): JsonResponse
    {
        $this->authorizeConnection($request, $aiConnection);

        $provider = AIServiceFactory::make($aiConnection);
        $result = $provider->testConnection($aiConnection);

        // Update connection status
        $aiConnection->last_tested_at = now();
        $aiConnection->last_test_status = $result['success'] ? AIConnection::STATUS_SUCCESS : AIConnection::STATUS_FAILED;
        $aiConnection->save();

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'model_count' => $result['model_count'] ?? null,
            'connection' => $this->formatConnection($aiConnection),
        ]);
    }

    /**
     * Fetch available models for a connection.
     */
    public function models(Request $request, AIConnection $aiConnection): JsonResponse
    {
        $this->authorizeConnection($request, $aiConnection);

        try {
            $provider = AIServiceFactory::make($aiConnection);
            $models = $provider->fetchModels($aiConnection);

            return response()->json([
                'models' => $models,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'models' => [],
                'error' => 'Failed to fetch models: '.$e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get all available providers with their configuration.
     */
    public function providers(): JsonResponse
    {
        return response()->json([
            'providers' => AIConnection::getProviders(),
        ]);
    }

    /**
     * Authorize that the user owns the connection.
     */
    protected function authorizeConnection(Request $request, AIConnection $connection): void
    {
        if ($connection->user_id !== $request->user()->id) {
            abort(403, 'You do not have access to this connection.');
        }
    }

    /**
     * Format a connection for JSON response.
     *
     * @return array<string, mixed>
     */
    protected function formatConnection(AIConnection $connection): array
    {
        return [
            'id' => $connection->id,
            'provider' => $connection->provider,
            'provider_name' => AIConnection::getProviders()[$connection->provider]['name'] ?? $connection->provider,
            'name' => $connection->name,
            'base_url' => $connection->base_url,
            'has_api_key' => (bool) $connection->api_key_encrypted,
            'masked_api_key' => $connection->getMaskedApiKey(),
            'settings' => $connection->settings,
            'is_active' => $connection->is_active,
            'is_default' => $connection->is_default,
            'last_tested_at' => $connection->last_tested_at?->toISOString(),
            'last_test_status' => $connection->last_test_status,
            'created_at' => $connection->created_at->toISOString(),
            'updated_at' => $connection->updated_at->toISOString(),
        ];
    }
}
