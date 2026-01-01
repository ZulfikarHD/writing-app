<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class AIConnection extends Model
{
    /** @use HasFactory<\Database\Factories\AIConnectionFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ai_connections';

    public const PROVIDER_OPENAI = 'openai';

    public const PROVIDER_ANTHROPIC = 'anthropic';

    public const PROVIDER_OPENROUTER = 'openrouter';

    public const PROVIDER_OLLAMA = 'ollama';

    public const PROVIDER_GROQ = 'groq';

    public const PROVIDER_LM_STUDIO = 'lm_studio';

    public const PROVIDER_OPENAI_COMPATIBLE = 'openai_compatible';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const STATUS_PENDING = 'pending';

    protected $fillable = [
        'user_id',
        'provider',
        'name',
        'api_key_encrypted',
        'base_url',
        'settings',
        'is_active',
        'is_default',
        'last_tested_at',
        'last_test_status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'last_tested_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<AIUsageLog, $this>
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(AIUsageLog::class, 'connection_id');
    }

    /**
     * Set the API key (automatically encrypts).
     */
    public function setApiKey(?string $value): void
    {
        $this->api_key_encrypted = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get the decrypted API key.
     */
    public function getApiKey(): ?string
    {
        if (! $this->api_key_encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($this->api_key_encrypted);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get a masked version of the API key for display.
     */
    public function getMaskedApiKey(): ?string
    {
        $key = $this->getApiKey();

        if (! $key) {
            return null;
        }

        $length = strlen($key);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($key, 0, 4).str_repeat('*', $length - 8).substr($key, -4);
    }

    /**
     * Check if this provider requires an API key.
     */
    public function requiresApiKey(): bool
    {
        return ! in_array($this->provider, [
            self::PROVIDER_OLLAMA,
            self::PROVIDER_LM_STUDIO,
        ]);
    }

    /**
     * Get all available providers.
     *
     * @return array<string, array{name: string, requires_api_key: bool, default_base_url: string|null}>
     */
    public static function getProviders(): array
    {
        return [
            self::PROVIDER_OPENAI => [
                'name' => 'OpenAI',
                'requires_api_key' => true,
                'default_base_url' => 'https://api.openai.com/v1',
            ],
            self::PROVIDER_ANTHROPIC => [
                'name' => 'Anthropic',
                'requires_api_key' => true,
                'default_base_url' => 'https://api.anthropic.com',
            ],
            self::PROVIDER_OPENROUTER => [
                'name' => 'OpenRouter',
                'requires_api_key' => true,
                'default_base_url' => 'https://openrouter.ai/api/v1',
            ],
            self::PROVIDER_OLLAMA => [
                'name' => 'Ollama',
                'requires_api_key' => false,
                'default_base_url' => 'http://localhost:11434',
            ],
            self::PROVIDER_GROQ => [
                'name' => 'Groq',
                'requires_api_key' => true,
                'default_base_url' => 'https://api.groq.com/openai/v1',
            ],
            self::PROVIDER_LM_STUDIO => [
                'name' => 'LM Studio',
                'requires_api_key' => false,
                'default_base_url' => 'http://localhost:1234/v1',
            ],
            self::PROVIDER_OPENAI_COMPATIBLE => [
                'name' => 'OpenAI Compatible',
                'requires_api_key' => true,
                'default_base_url' => null,
            ],
        ];
    }
}
