<?php

namespace App\Services\Editor;

use App\Models\AIConnection;
use App\Models\Novel;
use App\Models\Prompt;
use App\Models\Scene;
use App\Models\User;
use App\Services\Codex\CodexContextBuilder;
use Generator;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ProseGenerationService
{
    /**
     * Generation modes.
     */
    public const MODE_SCENE_BEAT = 'scene_beat';

    public const MODE_CONTINUE = 'continue';

    public const MODE_CUSTOM = 'custom';

    protected Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 120,
            'stream' => true,
        ]);
    }

    /**
     * Generate prose with streaming output.
     *
     * @param  array<string, mixed>  $options
     * @return Generator<int, array{type: string, content?: string, error?: string}>
     */
    public function generate(Scene $scene, User $user, array $options = []): Generator
    {
        // Get AI connection
        $connection = $this->resolveConnection($user, $options['connection_id'] ?? null);

        if (! $connection) {
            yield ['type' => 'error', 'error' => 'No AI connection available. Please configure an AI provider in settings.'];

            return;
        }

        // Get model
        $model = $options['model'] ?? $this->getDefaultModel($connection);

        // Get prompt or build default
        $prompt = $this->resolvePrompt($user, $options['prompt_id'] ?? null, $options['mode'] ?? self::MODE_SCENE_BEAT);

        // Build messages for AI
        $messages = $this->buildMessages($scene, $prompt, $options);

        yield ['type' => 'start', 'model' => $model];

        // Stream the response
        $fullContent = '';
        $tokensInput = null;
        $tokensOutput = null;

        try {
            foreach ($this->streamFromProvider($connection, $messages, $model, $options) as $chunk) {
                if (isset($chunk['error'])) {
                    yield ['type' => 'error', 'error' => $chunk['error']];

                    return;
                }

                if (isset($chunk['content'])) {
                    $fullContent .= $chunk['content'];
                    yield ['type' => 'content', 'content' => $chunk['content']];
                }

                if (isset($chunk['usage'])) {
                    $tokensInput = $chunk['usage']['prompt_tokens'] ?? null;
                    $tokensOutput = $chunk['usage']['completion_tokens'] ?? null;
                }
            }

            // Record prompt usage if applicable
            if ($prompt) {
                $prompt->incrementUsage();
            }

            yield [
                'type' => 'done',
                'full_content' => $fullContent,
                'tokens_input' => $tokensInput,
                'tokens_output' => $tokensOutput,
            ];

        } catch (\Exception $e) {
            Log::error('Prose generation error', [
                'scene_id' => $scene->id,
                'error' => $e->getMessage(),
            ]);
            yield ['type' => 'error', 'error' => 'Failed to generate prose: '.$e->getMessage()];
        }
    }

    /**
     * Build messages array for the AI request.
     *
     * @param  array<string, mixed>  $options
     * @return array<int, array{role: string, content: string}>
     */
    protected function buildMessages(Scene $scene, ?Prompt $prompt, array $options): array
    {
        $messages = [];

        // Build system message
        $systemMessage = $this->buildSystemMessage($scene, $prompt, $options);
        if ($systemMessage) {
            $messages[] = ['role' => 'system', 'content' => $systemMessage];
        }

        // Build user message with beat/instructions
        $userMessage = $this->buildUserMessage($scene, $options);
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }

    /**
     * Build the system message with context.
     *
     * @param  array<string, mixed>  $options
     */
    protected function buildSystemMessage(Scene $scene, ?Prompt $prompt, array $options): string
    {
        $parts = [];

        // Use prompt's system message if available
        if ($prompt && $prompt->system_message) {
            $parts[] = $prompt->system_message;
        } else {
            $parts[] = $this->getDefaultSystemMessage($options['mode'] ?? self::MODE_SCENE_BEAT);
        }

        // Add novel context
        $novel = $scene->chapter?->act?->novel ?? $scene->chapter?->novel;
        if ($novel) {
            $parts[] = $this->buildNovelContext($novel);
        }

        // Add scene context
        $parts[] = $this->buildSceneContext($scene, $options);

        // Add codex context
        $codexContext = $this->buildCodexContext($scene);
        if ($codexContext) {
            $parts[] = "\n\n=== CHARACTER & WORLD INFO ===\n".$codexContext;
        }

        return implode("\n\n", array_filter($parts));
    }

    /**
     * Build user message with beat/instructions.
     *
     * @param  array<string, mixed>  $options
     */
    protected function buildUserMessage(Scene $scene, array $options): string
    {
        $mode = $options['mode'] ?? self::MODE_SCENE_BEAT;
        $beat = $options['beat'] ?? '';
        $instructions = $options['instructions'] ?? '';

        $parts = [];

        if ($mode === self::MODE_CONTINUE) {
            $parts[] = 'Continue writing the story naturally from where it left off.';
            if ($instructions) {
                $parts[] = "Additional guidance: {$instructions}";
            }
        } elseif ($mode === self::MODE_SCENE_BEAT) {
            if ($beat) {
                $parts[] = "Write prose for this scene beat:\n\n{$beat}";
            }
            if ($instructions) {
                $parts[] = "Additional instructions: {$instructions}";
            }
        } else {
            // Custom mode
            if ($beat) {
                $parts[] = $beat;
            }
            if ($instructions) {
                $parts[] = $instructions;
            }
        }

        return implode("\n\n", array_filter($parts));
    }

    /**
     * Build novel context string.
     */
    protected function buildNovelContext(Novel $novel): string
    {
        $parts = ["You are helping write a novel titled \"{$novel->title}\"."];

        if ($novel->genre) {
            $parts[] = "Genre: {$novel->genre}.";
        }
        if ($novel->pov) {
            $parts[] = "POV: {$novel->pov}.";
        }
        if ($novel->tense) {
            $parts[] = "Tense: {$novel->tense}.";
        }

        return implode(' ', $parts);
    }

    /**
     * Build scene context with content before cursor.
     *
     * @param  array<string, mixed>  $options
     */
    protected function buildSceneContext(Scene $scene, array $options): string
    {
        $parts = [];

        if ($scene->title) {
            $parts[] = "Current scene: \"{$scene->title}\"";
        }

        if ($scene->summary) {
            $parts[] = "Scene summary: {$scene->summary}";
        }

        // Get content before cursor position
        $contentBefore = $options['content_before'] ?? '';
        if ($contentBefore) {
            $parts[] = "=== STORY SO FAR (Continue from here) ===\n{$contentBefore}";
        } else {
            // Use scene's AI visible content
            $sceneContent = $scene->getAiVisibleContent();
            if ($sceneContent) {
                // Limit content to last ~2000 words for context
                $words = explode(' ', $sceneContent);
                if (count($words) > 2000) {
                    $sceneContent = '...[earlier content omitted]... '.implode(' ', array_slice($words, -2000));
                }
                $parts[] = "=== CURRENT SCENE CONTENT ===\n{$sceneContent}";
            }
        }

        return implode("\n\n", array_filter($parts));
    }

    /**
     * Build codex context for relevant entries.
     */
    protected function buildCodexContext(Scene $scene): string
    {
        $novel = $scene->chapter?->act?->novel ?? $scene->chapter?->novel;
        if (! $novel) {
            return '';
        }

        // Get mentioned codex entries from the scene
        $mentionedEntryIds = $scene->codexMentions()->pluck('codex_entry_id')->unique()->toArray();

        if (empty($mentionedEntryIds)) {
            return '';
        }

        // Build context from codex entries
        $contextBuilder = app(CodexContextBuilder::class);

        return $contextBuilder->buildContextFromIds($mentionedEntryIds);
    }

    /**
     * Get default system message based on mode.
     */
    protected function getDefaultSystemMessage(string $mode): string
    {
        return match ($mode) {
            self::MODE_CONTINUE => 'You are a creative writing assistant. Continue the story naturally, matching the established tone, style, and voice. Write immersive prose that flows seamlessly from the existing content.',
            self::MODE_SCENE_BEAT => 'You are a creative writing assistant. Expand the given scene beat into vivid, engaging prose. Match the established style and voice of the story. Show, don\'t tell. Include sensory details and character emotions.',
            default => 'You are a creative writing assistant. Write engaging, immersive prose that matches the story\'s style and voice.',
        };
    }

    /**
     * Resolve AI connection.
     */
    protected function resolveConnection(User $user, ?int $connectionId = null): ?AIConnection
    {
        if ($connectionId) {
            return AIConnection::where('id', $connectionId)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first();
        }

        // Get user's default connection
        return AIConnection::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first()
            ?? AIConnection::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();
    }

    /**
     * Resolve prompt to use.
     */
    protected function resolvePrompt(User $user, ?int $promptId, string $mode): ?Prompt
    {
        if ($promptId) {
            return Prompt::query()
                ->accessibleBy($user->id)
                ->find($promptId);
        }

        // Get default prose prompt
        return Prompt::query()
            ->where('type', Prompt::TYPE_PROSE)
            ->where('is_system', true)
            ->first();
    }

    /**
     * Get default model for connection.
     */
    protected function getDefaultModel(AIConnection $connection): string
    {
        return match ($connection->provider) {
            AIConnection::PROVIDER_OPENAI => 'gpt-4o-mini',
            AIConnection::PROVIDER_ANTHROPIC => 'claude-3-5-sonnet-20241022',
            AIConnection::PROVIDER_OPENROUTER => 'openai/gpt-4o-mini',
            AIConnection::PROVIDER_OLLAMA => 'llama3.2',
            AIConnection::PROVIDER_GROQ => 'llama-3.1-8b-instant',
            AIConnection::PROVIDER_LM_STUDIO => 'local-model',
            default => 'gpt-4o-mini',
        };
    }

    /**
     * Stream response from AI provider.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     * @return Generator<int, array{content?: string, error?: string, usage?: array}>
     */
    protected function streamFromProvider(AIConnection $connection, array $messages, string $model, array $options = []): Generator
    {
        $baseUrl = $connection->base_url ?? $this->getDefaultBaseUrl($connection);
        $apiKey = $connection->getApiKey();

        $body = [
            'model' => $model,
            'messages' => $messages,
            'stream' => true,
            'temperature' => $options['temperature'] ?? 0.8,
            'max_tokens' => $options['max_tokens'] ?? 2000,
        ];

        // Add provider-specific options
        if ($connection->provider === AIConnection::PROVIDER_ANTHROPIC) {
            $body['max_tokens'] = $body['max_tokens'] ?? 4096;
        }

        try {
            $response = $this->httpClient->post("{$baseUrl}/chat/completions", [
                'headers' => $this->getHeaders($connection, $apiKey),
                'json' => $body,
                'stream' => true,
            ]);

            if ($response->getStatusCode() !== 200) {
                yield ['error' => "Request failed with status {$response->getStatusCode()}"];

                return;
            }

            $stream = $response->getBody();
            $buffer = '';

            while (! $stream->eof()) {
                $chunk = $stream->read(1024);
                $buffer .= $chunk;

                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);

                    $line = trim($line);

                    if (str_starts_with($line, 'data: ')) {
                        $eventData = substr($line, 6);

                        if ($eventData === '[DONE]') {
                            break 2;
                        }

                        $data = json_decode($eventData, true);
                        if (! $data) {
                            continue;
                        }

                        if (isset($data['choices'][0]['delta']['content'])) {
                            yield ['content' => $data['choices'][0]['delta']['content']];
                        } elseif (isset($data['choices'][0]['message']['content'])) {
                            yield ['content' => $data['choices'][0]['message']['content']];
                        }

                        if (isset($data['usage'])) {
                            yield ['usage' => $data['usage']];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Stream provider error', [
                'provider' => $connection->provider,
                'error' => $e->getMessage(),
            ]);
            yield ['error' => $e->getMessage()];
        }
    }

    /**
     * Get request headers.
     *
     * @return array<string, string>
     */
    protected function getHeaders(AIConnection $connection, ?string $apiKey): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'text/event-stream',
        ];

        if ($apiKey) {
            if ($connection->provider === AIConnection::PROVIDER_ANTHROPIC) {
                $headers['x-api-key'] = $apiKey;
                $headers['anthropic-version'] = '2023-06-01';
            } else {
                $headers['Authorization'] = "Bearer {$apiKey}";
            }
        }

        return $headers;
    }

    /**
     * Get default base URL for provider.
     */
    protected function getDefaultBaseUrl(AIConnection $connection): string
    {
        return match ($connection->provider) {
            AIConnection::PROVIDER_OPENAI => 'https://api.openai.com/v1',
            AIConnection::PROVIDER_ANTHROPIC => 'https://api.anthropic.com/v1',
            AIConnection::PROVIDER_OPENROUTER => 'https://openrouter.ai/api/v1',
            AIConnection::PROVIDER_GROQ => 'https://api.groq.com/openai/v1',
            AIConnection::PROVIDER_OLLAMA => 'http://localhost:11434/v1',
            AIConnection::PROVIDER_LM_STUDIO => 'http://localhost:1234/v1',
            default => $connection->base_url ?? '',
        };
    }
}
