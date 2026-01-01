<?php

namespace App\Services\AI;

use App\Models\AIConnection;
use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\GroqProvider;
use App\Services\AI\Providers\LMStudioProvider;
use App\Services\AI\Providers\OllamaProvider;
use App\Services\AI\Providers\OpenAICompatibleProvider;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Providers\OpenRouterProvider;
use InvalidArgumentException;

class AIServiceFactory
{
    /**
     * Provider class mapping.
     *
     * @var array<string, class-string<AIProviderInterface>>
     */
    protected static array $providers = [
        AIConnection::PROVIDER_OPENAI => OpenAIProvider::class,
        AIConnection::PROVIDER_ANTHROPIC => AnthropicProvider::class,
        AIConnection::PROVIDER_OPENROUTER => OpenRouterProvider::class,
        AIConnection::PROVIDER_OLLAMA => OllamaProvider::class,
        AIConnection::PROVIDER_GROQ => GroqProvider::class,
        AIConnection::PROVIDER_LM_STUDIO => LMStudioProvider::class,
        AIConnection::PROVIDER_OPENAI_COMPATIBLE => OpenAICompatibleProvider::class,
    ];

    /**
     * Create an AI provider instance for the given connection.
     */
    public static function make(AIConnection $connection): AIProviderInterface
    {
        return self::makeFromProvider($connection->provider);
    }

    /**
     * Create an AI provider instance for the given provider name.
     */
    public static function makeFromProvider(string $provider): AIProviderInterface
    {
        if (! isset(self::$providers[$provider])) {
            throw new InvalidArgumentException("Unsupported AI provider: {$provider}");
        }

        $class = self::$providers[$provider];

        return new $class;
    }

    /**
     * Get all available providers.
     *
     * @return array<string, class-string<AIProviderInterface>>
     */
    public static function getProviders(): array
    {
        return self::$providers;
    }

    /**
     * Check if a provider is supported.
     */
    public static function isSupported(string $provider): bool
    {
        return isset(self::$providers[$provider]);
    }
}
