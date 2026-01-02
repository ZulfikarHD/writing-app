<?php

namespace App\Services\Chat;

use App\Events\ChatMessageCreated;
use App\Events\ChatThreadUpdated;
use App\Models\AIConnection;
use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\User;
use Generator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatService
{
    /**
     * Stream a chat response from the AI provider.
     *
     * @return Generator<int, array{type: string, content?: string, error?: string}>
     */
    public function streamResponse(ChatThread $thread, string $userMessage, ?string $model = null, ?int $connectionId = null): Generator
    {
        // Get connection - use thread's connection, provided connection, or user's default
        $connection = $this->resolveConnection($thread, $connectionId);

        if (! $connection) {
            yield ['type' => 'error', 'error' => 'No AI connection available. Please configure an AI provider in settings.'];

            return;
        }

        // Resolve model
        $modelToUse = $model ?? $thread->model ?? $this->getDefaultModel($connection);

        // Build messages array for the API
        $messages = $this->buildMessagesArray($thread, $userMessage);

        // Build context snapshot for this message
        $contextSnapshot = $this->buildContextSnapshot($thread);

        // Create the user message record
        $userMessageRecord = ChatMessage::create([
            'thread_id' => $thread->id,
            'role' => 'user',
            'content' => $userMessage,
            'context_snapshot' => $contextSnapshot,
        ]);

        yield ['type' => 'user_message', 'message_id' => $userMessageRecord->id];

        // Stream the response
        $fullContent = '';
        $tokensInput = null;
        $tokensOutput = null;

        try {
            foreach ($this->streamFromProvider($connection, $messages, $modelToUse) as $chunk) {
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

            // Create the assistant message record
            $assistantMessage = ChatMessage::create([
                'thread_id' => $thread->id,
                'role' => 'assistant',
                'content' => $fullContent,
                'model_used' => $modelToUse,
                'tokens_input' => $tokensInput,
                'tokens_output' => $tokensOutput,
                'context_snapshot' => $contextSnapshot,
            ]);

            // Update thread's updated_at timestamp
            $thread->touch();

            // Auto-generate title if not set
            if (! $thread->title) {
                $thread->update(['title' => str($userMessage)->limit(50)->toString()]);
            }

            // Broadcast events for real-time updates (Sprint 21 F4)
            try {
                ChatMessageCreated::dispatch($assistantMessage);
                ChatThreadUpdated::dispatch($thread->fresh(), 'message_added');
            } catch (\Exception $e) {
                // Don't fail the request if broadcasting fails
                Log::warning('Failed to broadcast chat event', ['error' => $e->getMessage()]);
            }

            yield ['type' => 'done', 'message_id' => $assistantMessage->id];

        } catch (\Exception $e) {
            Log::error('Chat streaming error', [
                'thread_id' => $thread->id,
                'error' => $e->getMessage(),
            ]);
            yield ['type' => 'error', 'error' => 'Failed to generate response: '.$e->getMessage()];
        }
    }

    /**
     * Regenerate the last assistant message.
     *
     * @return Generator<int, array{type: string, content?: string, error?: string}>
     */
    public function regenerateLastResponse(ChatThread $thread, ?string $model = null, ?int $connectionId = null): Generator
    {
        // Get the last assistant message
        $lastAssistantMessage = $thread->messages()
            ->where('role', 'assistant')
            ->latest('created_at')
            ->first();

        if (! $lastAssistantMessage) {
            yield ['type' => 'error', 'error' => 'No assistant message to regenerate.'];

            return;
        }

        // Get the user message before it
        $userMessage = $thread->messages()
            ->where('role', 'user')
            ->where('created_at', '<', $lastAssistantMessage->created_at)
            ->latest('created_at')
            ->first();

        if (! $userMessage) {
            yield ['type' => 'error', 'error' => 'Cannot find the original user message.'];

            return;
        }

        // Delete the old assistant message
        $lastAssistantMessage->delete();

        // Get connection - use provided connectionId, thread's connection, or user's default
        $connection = $this->resolveConnection($thread, $connectionId);

        if (! $connection) {
            yield ['type' => 'error', 'error' => 'No AI connection available.'];

            return;
        }

        // Resolve model - use provided model, thread's model, or default for connection
        $modelToUse = $model ?? $thread->model ?? $this->getDefaultModel($connection);

        // Build messages (excluding the deleted assistant message)
        $messages = $this->buildMessagesArray($thread, null);

        // Stream new response
        $fullContent = '';
        $tokensInput = null;
        $tokensOutput = null;

        try {
            foreach ($this->streamFromProvider($connection, $messages, $modelToUse) as $chunk) {
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

            // Create new assistant message
            $assistantMessage = ChatMessage::create([
                'thread_id' => $thread->id,
                'role' => 'assistant',
                'content' => $fullContent,
                'model_used' => $modelToUse,
                'tokens_input' => $tokensInput,
                'tokens_output' => $tokensOutput,
            ]);

            $thread->touch();

            // Broadcast events for real-time updates (Sprint 21 F4)
            try {
                ChatMessageCreated::dispatch($assistantMessage);
                ChatThreadUpdated::dispatch($thread->fresh(), 'message_regenerated');
            } catch (\Exception $e) {
                // Don't fail the request if broadcasting fails
                Log::warning('Failed to broadcast chat event', ['error' => $e->getMessage()]);
            }

            yield ['type' => 'done', 'message_id' => $assistantMessage->id, 'model_used' => $modelToUse];

        } catch (\Exception $e) {
            Log::error('Chat regeneration error', [
                'thread_id' => $thread->id,
                'error' => $e->getMessage(),
            ]);
            yield ['type' => 'error', 'error' => 'Failed to regenerate response: '.$e->getMessage()];
        }
    }

    /**
     * Resolve the AI connection to use.
     */
    protected function resolveConnection(ChatThread $thread, ?int $connectionId = null): ?AIConnection
    {
        if ($connectionId) {
            return AIConnection::where('id', $connectionId)
                ->where('user_id', $thread->user_id)
                ->where('is_active', true)
                ->first();
        }

        if ($thread->connection_id) {
            return $thread->connection;
        }

        // Get user's default connection
        return AIConnection::where('user_id', $thread->user_id)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first()
            ?? AIConnection::where('user_id', $thread->user_id)
                ->where('is_active', true)
                ->first();
    }

    /**
     * Get default model for a connection.
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
     * Build the messages array for the API call.
     *
     * @return array<int, array{role: string, content: string}>
     */
    protected function buildMessagesArray(ChatThread $thread, ?string $newUserMessage): array
    {
        $messages = [];

        // Add system message with context
        $systemMessage = $this->buildSystemMessage($thread);
        if ($systemMessage) {
            $messages[] = ['role' => 'system', 'content' => $systemMessage];
        }

        // Add existing conversation history
        foreach ($thread->messages as $message) {
            $messages[] = [
                'role' => $message->role,
                'content' => $message->content,
            ];
        }

        // Add new user message if provided
        if ($newUserMessage) {
            $messages[] = ['role' => 'user', 'content' => $newUserMessage];
        }

        return $messages;
    }

    /**
     * Build system message with context.
     */
    protected function buildSystemMessage(ChatThread $thread): string
    {
        $parts = ['You are a helpful creative writing assistant.'];

        // Add novel context
        $novel = $thread->novel;
        if ($novel) {
            $parts[] = "You are helping with a novel titled \"{$novel->title}\".";

            if ($novel->genre) {
                $parts[] = "Genre: {$novel->genre}.";
            }
            if ($novel->pov) {
                $parts[] = "POV: {$novel->pov}.";
            }
            if ($novel->tense) {
                $parts[] = "Tense: {$novel->tense}.";
            }
        }

        // Add linked scene context
        if ($thread->linkedScene) {
            $scene = $thread->linkedScene;
            $parts[] = "Currently discussing scene: \"{$scene->title}\".";
            if ($scene->summary) {
                $parts[] = "Scene summary: {$scene->summary}";
            }
        }

        // Group context items by type for better organization
        $contextByType = $this->groupContextByType($thread);

        // Add scene contexts
        if (! empty($contextByType['scene'])) {
            $parts[] = "\n\n=== SCENE CONTEXT ===";
            foreach ($contextByType['scene'] as $content) {
                $parts[] = $content;
            }
        }

        // Add codex/character contexts
        if (! empty($contextByType['codex'])) {
            $parts[] = "\n\n=== CHARACTER & WORLD INFO ===";
            foreach ($contextByType['codex'] as $content) {
                $parts[] = $content;
            }
        }

        // Add outline contexts
        if (! empty($contextByType['outline'])) {
            $parts[] = "\n\n=== STORY OUTLINE ===";
            foreach ($contextByType['outline'] as $content) {
                $parts[] = $content;
            }
        }

        // Add custom contexts
        if (! empty($contextByType['custom'])) {
            $parts[] = "\n\n=== ADDITIONAL CONTEXT ===";
            foreach ($contextByType['custom'] as $content) {
                $parts[] = $content;
            }
        }

        return implode("\n", $parts);
    }

    /**
     * Group active context items by type.
     *
     * @return array<string, array<string>>
     */
    protected function groupContextByType(ChatThread $thread): array
    {
        $grouped = [
            'scene' => [],
            'codex' => [],
            'outline' => [],
            'summary' => [],
            'custom' => [],
        ];

        foreach ($thread->activeContextItems as $contextItem) {
            $content = $contextItem->content;
            if (! $content) {
                continue;
            }

            // Respect AI context mode for codex entries
            if ($contextItem->context_type === 'codex' && $contextItem->codexEntry) {
                $entry = $contextItem->codexEntry;
                if ($entry->ai_context_mode === 'hidden') {
                    continue;
                }
            }

            $grouped[$contextItem->context_type][] = $content;
        }

        return $grouped;
    }

    /**
     * Build context snapshot for storing with messages.
     *
     * @return array<string, mixed>
     */
    public function buildContextSnapshot(ChatThread $thread): array
    {
        $items = [];

        foreach ($thread->activeContextItems as $contextItem) {
            $items[] = [
                'id' => $contextItem->id,
                'type' => $contextItem->context_type,
                'reference_id' => $contextItem->reference_id,
                'name' => match ($contextItem->context_type) {
                    'scene' => $contextItem->scene?->title ?? 'Untitled Scene',
                    'codex' => $contextItem->codexEntry?->name ?? 'Unknown Entry',
                    'summary' => 'Novel Summary',
                    'outline' => 'Story Outline',
                    'custom' => 'Custom Context',
                    default => 'Context Item',
                },
            ];
        }

        return [
            'items' => $items,
            'linked_scene_id' => $thread->linked_scene_id,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Stream response from the AI provider.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return Generator<int, array{content?: string, error?: string, usage?: array}>
     */
    protected function streamFromProvider(AIConnection $connection, array $messages, string $model): Generator
    {
        $baseUrl = $connection->base_url ?? $this->getDefaultBaseUrl($connection);
        $apiKey = $connection->getApiKey();

        // Build request body based on provider
        $body = $this->buildRequestBody($connection, $messages, $model);

        try {
            $response = Http::withHeaders($this->getHeaders($connection, $apiKey))
                ->timeout(120)
                ->withOptions(['stream' => true])
                ->post("{$baseUrl}/chat/completions", $body);

            if (! $response->successful()) {
                $error = $response->json('error.message') ?? "Request failed with status {$response->status()}";
                yield ['error' => $error];

                return;
            }

            $body = $response->body();

            // Parse SSE stream
            foreach ($this->parseSSEStream($body) as $event) {
                if ($event === '[DONE]') {
                    break;
                }

                $data = json_decode($event, true);
                if (! $data) {
                    continue;
                }

                // Handle different response formats
                if (isset($data['choices'][0]['delta']['content'])) {
                    yield ['content' => $data['choices'][0]['delta']['content']];
                } elseif (isset($data['choices'][0]['message']['content'])) {
                    // Non-streaming response
                    yield ['content' => $data['choices'][0]['message']['content']];
                }

                if (isset($data['usage'])) {
                    yield ['usage' => $data['usage']];
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
     * Parse SSE stream.
     *
     * @return Generator<string>
     */
    protected function parseSSEStream(string $body): Generator
    {
        $lines = explode("\n", $body);

        foreach ($lines as $line) {
            $line = trim($line);

            if (str_starts_with($line, 'data: ')) {
                yield substr($line, 6);
            }
        }
    }

    /**
     * Build request body for the API call.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<string, mixed>
     */
    protected function buildRequestBody(AIConnection $connection, array $messages, string $model): array
    {
        $body = [
            'model' => $model,
            'messages' => $messages,
            'stream' => true,
        ];

        // Add provider-specific options
        if ($connection->provider === AIConnection::PROVIDER_ANTHROPIC) {
            $body['max_tokens'] = 4096;
        }

        return $body;
    }

    /**
     * Get headers for the API request.
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
     * Create a new thread for a novel.
     */
    public function createThread(User $user, int $novelId, ?string $title = null, ?int $connectionId = null, ?string $model = null): ChatThread
    {
        return ChatThread::create([
            'novel_id' => $novelId,
            'user_id' => $user->id,
            'title' => $title,
            'connection_id' => $connectionId,
            'model' => $model,
        ]);
    }
}
