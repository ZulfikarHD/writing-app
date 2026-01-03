<?php

namespace App\Services\Editor;

use App\Models\AIConnection;
use App\Models\Novel;
use App\Models\Prompt;
use App\Models\Scene;
use App\Models\User;
use Generator;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TextReplacementService
{
    /**
     * Transformation types.
     */
    public const TYPE_EXPAND = 'expand';

    public const TYPE_REPHRASE = 'rephrase';

    public const TYPE_SHORTEN = 'shorten';

    public const TYPE_CUSTOM = 'custom';

    /**
     * Expand amount options.
     */
    public const EXPAND_SLIGHTLY = 'slightly';

    public const EXPAND_DOUBLE = 'double';

    public const EXPAND_TRIPLE = 'triple';

    /**
     * Shorten amount options.
     */
    public const SHORTEN_HALF = 'half';

    public const SHORTEN_QUARTER = 'quarter';

    public const SHORTEN_PARAGRAPH = 'single_paragraph';

    /**
     * Rephrase options.
     */
    public const REPHRASE_OPTIONS = [
        'add_inner_thoughts' => 'Add inner thoughts',
        'convert_to_dialogue' => 'Convert to dialogue',
        'passive_to_active' => 'Convert passive to active voice',
        'different_words' => 'Use different words',
        'show_dont_tell' => 'Show, don\'t tell',
        'change_pov' => 'Change POV',
        'change_tense' => 'Change tense',
    ];

    protected Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 120,
            'stream' => true,
        ]);
    }

    /**
     * Transform selected text with streaming output.
     *
     * @param  array<string, mixed>  $options
     * @return Generator<int, array{type: string, content?: string, error?: string, original?: string}>
     */
    public function transform(string $selectedText, User $user, array $options = []): Generator
    {
        // Validate minimum text length (4 words)
        $wordCount = str_word_count($selectedText);
        if ($wordCount < 4) {
            yield ['type' => 'error', 'error' => 'Please select at least 4 words to use text replacement.'];

            return;
        }

        // Get AI connection
        $connection = $this->resolveConnection($user, $options['connection_id'] ?? null);

        if (! $connection) {
            yield ['type' => 'error', 'error' => 'No AI connection available. Please configure an AI provider in settings.'];

            return;
        }

        // Get model
        $model = $options['model'] ?? $this->getDefaultModel($connection);

        // Get transformation type
        $transformationType = $options['type'] ?? self::TYPE_REPHRASE;

        // Build messages
        $messages = $this->buildMessages($selectedText, $transformationType, $options);

        yield ['type' => 'start', 'model' => $model, 'original' => $selectedText];

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
            if (isset($options['prompt_id'])) {
                $prompt = Prompt::find($options['prompt_id']);
                $prompt?->incrementUsage();
            }

            yield [
                'type' => 'done',
                'full_content' => $fullContent,
                'original' => $selectedText,
                'tokens_input' => $tokensInput,
                'tokens_output' => $tokensOutput,
            ];

        } catch (\Exception $e) {
            Log::error('Text replacement error', [
                'type' => $transformationType,
                'error' => $e->getMessage(),
            ]);
            yield ['type' => 'error', 'error' => 'Failed to transform text: '.$e->getMessage()];
        }
    }

    /**
     * Quick expand transformation.
     *
     * @return Generator<int, array{type: string, content?: string, error?: string}>
     */
    public function expand(string $selectedText, User $user, string $amount = self::EXPAND_DOUBLE, ?string $method = null): Generator
    {
        return $this->transform($selectedText, $user, [
            'type' => self::TYPE_EXPAND,
            'expand_amount' => $amount,
            'expand_method' => $method,
        ]);
    }

    /**
     * Quick rephrase transformation.
     *
     * @param  array<string>  $rephraseOptions
     * @return Generator<int, array{type: string, content?: string, error?: string}>
     */
    public function rephrase(string $selectedText, User $user, array $rephraseOptions = []): Generator
    {
        return $this->transform($selectedText, $user, [
            'type' => self::TYPE_REPHRASE,
            'rephrase_options' => $rephraseOptions,
        ]);
    }

    /**
     * Quick shorten transformation.
     *
     * @return Generator<int, array{type: string, content?: string, error?: string}>
     */
    public function shorten(string $selectedText, User $user, string $amount = self::SHORTEN_HALF): Generator
    {
        return $this->transform($selectedText, $user, [
            'type' => self::TYPE_SHORTEN,
            'shorten_amount' => $amount,
        ]);
    }

    /**
     * Build messages array for AI request.
     *
     * @param  array<string, mixed>  $options
     * @return array<int, array{role: string, content: string}>
     */
    protected function buildMessages(string $selectedText, string $transformationType, array $options): array
    {
        $messages = [];

        // System message
        $systemMessage = $this->buildSystemMessage($transformationType, $options);
        $messages[] = ['role' => 'system', 'content' => $systemMessage];

        // User message with the text to transform
        $userMessage = $this->buildUserMessage($selectedText, $transformationType, $options);
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }

    /**
     * Build system message based on transformation type.
     *
     * @param  array<string, mixed>  $options
     */
    protected function buildSystemMessage(string $transformationType, array $options): string
    {
        // Check for custom prompt
        if (isset($options['prompt_id'])) {
            $prompt = Prompt::find($options['prompt_id']);
            if ($prompt && $prompt->system_message) {
                return $prompt->system_message;
            }
        }

        // Check for novel context
        $novelContext = '';
        if (isset($options['scene_id'])) {
            $scene = Scene::find($options['scene_id']);
            if ($scene) {
                $novel = $scene->chapter?->act?->novel;
                if ($novel) {
                    $novelContext = $this->buildNovelContext($novel);
                }
            }
        }

        $baseMessage = match ($transformationType) {
            self::TYPE_EXPAND => $this->getExpandSystemMessage($options),
            self::TYPE_REPHRASE => $this->getRephraseSystemMessage($options),
            self::TYPE_SHORTEN => $this->getShortenSystemMessage($options),
            default => 'You are a creative writing assistant. Transform the given text as instructed while maintaining its meaning and the story\'s voice.',
        };

        if ($novelContext) {
            return "{$baseMessage}\n\n{$novelContext}";
        }

        return $baseMessage;
    }

    /**
     * Get expand system message.
     *
     * @param  array<string, mixed>  $options
     */
    protected function getExpandSystemMessage(array $options): string
    {
        $amount = $options['expand_amount'] ?? self::EXPAND_DOUBLE;
        $method = $options['expand_method'] ?? null;

        $base = 'You are a creative writing assistant. Expand the given text while maintaining the original meaning, tone, and voice.';

        $amountInstruction = match ($amount) {
            self::EXPAND_SLIGHTLY => 'Expand the text by about 25-50%.',
            self::EXPAND_DOUBLE => 'Expand the text to approximately double its length.',
            self::EXPAND_TRIPLE => 'Expand the text to approximately triple its length.',
            default => 'Expand the text moderately.',
        };

        $methodInstruction = '';
        if ($method) {
            $methodInstruction = match ($method) {
                'sensory_details' => 'Add rich sensory details (sight, sound, smell, touch, taste).',
                'inner_thoughts' => 'Add character inner thoughts and emotions.',
                'description' => 'Add more descriptive prose and setting details.',
                'dialogue' => 'Expand with additional dialogue and character interaction.',
                default => $method,
            };
        }

        return "{$base} {$amountInstruction} {$methodInstruction}\n\nIMPORTANT: Output ONLY the expanded text. Do not include any explanations, notes, or the original text.";
    }

    /**
     * Get rephrase system message.
     *
     * @param  array<string, mixed>  $options
     */
    protected function getRephraseSystemMessage(array $options): string
    {
        $rephraseOptions = $options['rephrase_options'] ?? [];
        $customInstructions = $options['instructions'] ?? '';

        $base = 'You are a creative writing assistant. Rephrase the given text while preserving its core meaning.';

        $instructions = [];

        if (in_array('add_inner_thoughts', $rephraseOptions)) {
            $instructions[] = 'Add character inner thoughts and internal monologue.';
        }
        if (in_array('convert_to_dialogue', $rephraseOptions)) {
            $instructions[] = 'Convert narrative into dialogue where appropriate.';
        }
        if (in_array('passive_to_active', $rephraseOptions)) {
            $instructions[] = 'Convert passive voice to active voice.';
        }
        if (in_array('different_words', $rephraseOptions)) {
            $instructions[] = 'Use different words and phrasing while keeping the same meaning.';
        }
        if (in_array('show_dont_tell', $rephraseOptions)) {
            $instructions[] = 'Apply "show, don\'t tell" - demonstrate through action and description rather than stating directly.';
        }
        if (in_array('change_pov', $rephraseOptions)) {
            $targetPov = $options['target_pov'] ?? 'third person';
            $instructions[] = "Change the point of view to {$targetPov}.";
        }
        if (in_array('change_tense', $rephraseOptions)) {
            $targetTense = $options['target_tense'] ?? 'past tense';
            $instructions[] = "Change the tense to {$targetTense}.";
        }

        if ($customInstructions) {
            $instructions[] = $customInstructions;
        }

        $instructionText = empty($instructions) ? 'Rephrase naturally while improving the prose.' : implode(' ', $instructions);

        return "{$base} {$instructionText}\n\nIMPORTANT: Output ONLY the rephrased text. Do not include any explanations, notes, or the original text. Maintain approximately the same length unless the transformation requires otherwise.";
    }

    /**
     * Get shorten system message.
     *
     * @param  array<string, mixed>  $options
     */
    protected function getShortenSystemMessage(array $options): string
    {
        $amount = $options['shorten_amount'] ?? self::SHORTEN_HALF;

        $base = 'You are a creative writing assistant. Shorten the given text while preserving the essential meaning and key details.';

        $amountInstruction = match ($amount) {
            self::SHORTEN_HALF => 'Reduce the text to approximately half its original length.',
            self::SHORTEN_QUARTER => 'Reduce the text to approximately one quarter of its original length.',
            self::SHORTEN_PARAGRAPH => 'Condense the text into a single, concise paragraph.',
            default => 'Shorten the text while keeping key information.',
        };

        return "{$base} {$amountInstruction}\n\nIMPORTANT: Output ONLY the shortened text. Do not include any explanations, notes, or the original text.";
    }

    /**
     * Build user message.
     *
     * @param  array<string, mixed>  $options
     */
    protected function buildUserMessage(string $selectedText, string $transformationType, array $options): string
    {
        $customPromptMessage = '';
        if (isset($options['prompt_id'])) {
            $prompt = Prompt::find($options['prompt_id']);
            if ($prompt && $prompt->user_message) {
                $customPromptMessage = str_replace('{{selected_text}}', $selectedText, $prompt->user_message);

                return $customPromptMessage;
            }
        }

        // Check for square bracket instructions in the text
        if (preg_match('/\[([^\]]+)\]/', $selectedText, $matches)) {
            return "Transform this text following the instructions in square brackets:\n\n{$selectedText}";
        }

        $action = match ($transformationType) {
            self::TYPE_EXPAND => 'Expand',
            self::TYPE_REPHRASE => 'Rephrase',
            self::TYPE_SHORTEN => 'Shorten',
            default => 'Transform',
        };

        return "{$action} the following text:\n\n{$selectedText}";
    }

    /**
     * Build novel context.
     */
    protected function buildNovelContext(Novel $novel): string
    {
        $parts = ["Writing context - Novel: \"{$novel->title}\""];

        if ($novel->genre) {
            $parts[] = "Genre: {$novel->genre}";
        }
        if ($novel->pov) {
            $parts[] = "POV: {$novel->pov}";
        }
        if ($novel->tense) {
            $parts[] = "Tense: {$novel->tense}";
        }

        return implode('. ', $parts).'.';
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

        return AIConnection::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first()
            ?? AIConnection::where('user_id', $user->id)
                ->where('is_active', true)
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
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 2000,
        ];

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

    /**
     * Get available transformation types.
     *
     * @return array<string, string>
     */
    public static function getTransformationTypes(): array
    {
        return [
            self::TYPE_EXPAND => 'Expand',
            self::TYPE_REPHRASE => 'Rephrase',
            self::TYPE_SHORTEN => 'Shorten',
            self::TYPE_CUSTOM => 'Custom',
        ];
    }

    /**
     * Get expand amount options.
     *
     * @return array<string, string>
     */
    public static function getExpandAmounts(): array
    {
        return [
            self::EXPAND_SLIGHTLY => 'Slightly (~25-50%)',
            self::EXPAND_DOUBLE => 'Double',
            self::EXPAND_TRIPLE => 'Triple',
        ];
    }

    /**
     * Get shorten amount options.
     *
     * @return array<string, string>
     */
    public static function getShortenAmounts(): array
    {
        return [
            self::SHORTEN_HALF => 'Half',
            self::SHORTEN_QUARTER => 'Quarter',
            self::SHORTEN_PARAGRAPH => 'Single Paragraph',
        ];
    }
}
