<?php

namespace App\Http\Controllers;

use App\Models\AIConnection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Display the settings index page.
     */
    public function index(): Response
    {
        return Inertia::render('Settings/Index');
    }

    /**
     * Display the AI connections settings page.
     */
    public function aiConnections(Request $request): Response
    {
        $connections = $request->user()
            ->aiConnections()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (AIConnection $conn) => [
                'id' => $conn->id,
                'provider' => $conn->provider,
                'provider_name' => AIConnection::getProviders()[$conn->provider]['name'] ?? $conn->provider,
                'name' => $conn->name,
                'base_url' => $conn->base_url,
                'has_api_key' => (bool) $conn->api_key_encrypted,
                'masked_api_key' => $conn->getMaskedApiKey(),
                'is_active' => $conn->is_active,
                'is_default' => $conn->is_default,
                'last_tested_at' => $conn->last_tested_at?->toISOString(),
                'last_test_status' => $conn->last_test_status,
                'created_at' => $conn->created_at->toISOString(),
            ]);

        return Inertia::render('Settings/AIConnections', [
            'connections' => $connections,
            'providers' => AIConnection::getProviders(),
        ]);
    }
}
