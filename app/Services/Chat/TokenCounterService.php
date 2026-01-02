<?php

namespace App\Services\Chat;

use App\Models\ChatContextItem;
use App\Models\ChatThread;
use App\Models\CodexEntry;
use App\Models\Scene;

class TokenCounterService
{
    /**
     * Average characters per token (approximation for most models).
     */
    protected const CHARS_PER_TOKEN = 4;

    /**
     * Default context limits per model family.
     *
     * @var array<string, int>
     */
    protected array $modelLimits = [
        'gpt-4o' => 128000,
        'gpt-4o-mini' => 128000,
        'gpt-4-turbo' => 128000,
        'gpt-4' => 8192,
        'gpt-3.5-turbo' => 16385,
        'claude-3-5-sonnet' => 200000,
        'claude-3-opus' => 200000,
        'claude-3-sonnet' => 200000,
        'claude-3-haiku' => 200000,
        'llama' => 8192,
        'mistral' => 32768,
        'default' => 8192,
    ];

    /**
     * Estimate token count for a given text.
     */
    public function estimateTokens(string $text): int
    {
        if (empty($text)) {
            return 0;
        }

        return (int) ceil(mb_strlen($text) / self::CHARS_PER_TOKEN);
    }

    /**
     * Get token count for a scene.
     */
    public function countSceneTokens(Scene $scene): int
    {
        $content = '';

        if ($scene->title) {
            $content .= "Scene: {$scene->title}\n";
        }

        if ($scene->summary) {
            $content .= "Summary: {$scene->summary}\n";
        }

        if ($scene->content) {
            $content .= $this->extractTextFromContent($scene->content);
        }

        return $this->estimateTokens($content);
    }

    /**
     * Get token count for a codex entry.
     */
    public function countCodexTokens(CodexEntry $entry): int
    {
        $content = "{$entry->name}: {$entry->description}";

        // Include visible details
        foreach ($entry->details as $detail) {
            if ($detail->ai_visibility !== 'hidden') {
                $content .= "\n{$detail->key_name}: {$detail->value}";
            }
        }

        return $this->estimateTokens($content);
    }

    /**
     * Get token count for a context item.
     */
    public function countContextItemTokens(ChatContextItem $item): int
    {
        if ($item->custom_content) {
            return $this->estimateTokens($item->custom_content);
        }

        return match ($item->context_type) {
            'scene' => $item->scene ? $this->countSceneTokens($item->scene) : 0,
            'codex' => $item->codexEntry ? $this->countCodexTokens($item->codexEntry) : 0,
            default => 0,
        };
    }

    /**
     * Get total token count for a thread's active context.
     *
     * @return array{total: int, items: array<int, array{id: int, type: string, name: string, tokens: int}>}
     */
    public function countThreadContextTokens(ChatThread $thread): array
    {
        $items = [];
        $total = 0;

        foreach ($thread->activeContextItems as $item) {
            $tokens = $this->countContextItemTokens($item);
            $total += $tokens;

            $items[] = [
                'id' => $item->id,
                'type' => $item->context_type,
                'name' => $this->getContextItemName($item),
                'tokens' => $tokens,
            ];
        }

        // Add base system message tokens (approximate)
        $baseTokens = $this->estimateBaseSystemTokens($thread);
        $total += $baseTokens;

        return [
            'total' => $total,
            'base_tokens' => $baseTokens,
            'items' => $items,
        ];
    }

    /**
     * Estimate tokens used by the base system message.
     */
    protected function estimateBaseSystemTokens(ChatThread $thread): int
    {
        $baseMessage = 'You are a helpful creative writing assistant.';

        if ($thread->novel) {
            $baseMessage .= " You are helping with a novel titled \"{$thread->novel->title}\".";

            if ($thread->novel->genre) {
                $baseMessage .= " Genre: {$thread->novel->genre}.";
            }
            if ($thread->novel->pov) {
                $baseMessage .= " POV: {$thread->novel->pov}.";
            }
            if ($thread->novel->tense) {
                $baseMessage .= " Tense: {$thread->novel->tense}.";
            }
        }

        return $this->estimateTokens($baseMessage);
    }

    /**
     * Get the display name for a context item.
     */
    protected function getContextItemName(ChatContextItem $item): string
    {
        return match ($item->context_type) {
            'scene' => $item->scene?->title ?? 'Untitled Scene',
            'codex' => $item->codexEntry?->name ?? 'Unknown Entry',
            'summary' => 'Novel Summary',
            'outline' => 'Story Outline',
            'custom' => 'Custom Context',
            default => 'Context Item',
        };
    }

    /**
     * Get the context limit for a model.
     */
    public function getModelLimit(string $model): int
    {
        // Check for exact match first
        if (isset($this->modelLimits[$model])) {
            return $this->modelLimits[$model];
        }

        // Check for partial match (model family)
        foreach ($this->modelLimits as $key => $limit) {
            if (str_contains(strtolower($model), strtolower($key))) {
                return $limit;
            }
        }

        return $this->modelLimits['default'];
    }

    /**
     * Check if context is within the model's limit.
     *
     * @return array{within_limit: bool, usage_percentage: float, tokens_used: int, limit: int}
     */
    public function checkContextLimit(ChatThread $thread, ?string $model = null): array
    {
        $modelToUse = $model ?? $thread->model ?? 'default';
        $limit = $this->getModelLimit($modelToUse);
        $contextTokens = $this->countThreadContextTokens($thread);

        // Reserve ~25% for conversation and response
        $effectiveLimit = (int) ($limit * 0.75);

        $usagePercentage = $effectiveLimit > 0
            ? ($contextTokens['total'] / $effectiveLimit) * 100
            : 0;

        return [
            'within_limit' => $contextTokens['total'] <= $effectiveLimit,
            'usage_percentage' => round($usagePercentage, 1),
            'tokens_used' => $contextTokens['total'],
            'limit' => $effectiveLimit,
            'model_limit' => $limit,
        ];
    }

    /**
     * Extract plain text from TipTap JSON content.
     */
    protected function extractTextFromContent(mixed $content): string
    {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            if ($decoded) {
                $content = $decoded;
            } else {
                return strip_tags($content);
            }
        }

        if (! is_array($content)) {
            return '';
        }

        return $this->extractTextFromNode($content);
    }

    /**
     * Recursively extract text from a TipTap node.
     */
    protected function extractTextFromNode(array $node): string
    {
        $text = '';

        if (isset($node['text'])) {
            $text .= $node['text'];
        }

        if (isset($node['content']) && is_array($node['content'])) {
            foreach ($node['content'] as $child) {
                $text .= $this->extractTextFromNode($child);
            }
        }

        // Add spacing for block elements
        if (isset($node['type']) && in_array($node['type'], ['paragraph', 'heading', 'blockquote'])) {
            $text .= "\n";
        }

        return $text;
    }

    /**
     * Get a preview of context content (truncated).
     */
    public function getContextPreview(ChatContextItem $item, int $maxLength = 500): string
    {
        $content = $item->content ?? '';

        if (mb_strlen($content) > $maxLength) {
            return mb_substr($content, 0, $maxLength).'...';
        }

        return $content;
    }

    /**
     * Build full context text for preview.
     *
     * @return array{text: string, tokens: int}
     */
    public function buildContextPreview(ChatThread $thread): array
    {
        $parts = [];

        foreach ($thread->activeContextItems as $item) {
            $name = $this->getContextItemName($item);
            $content = $item->content ?? '';
            $tokens = $this->countContextItemTokens($item);

            $parts[] = [
                'name' => $name,
                'type' => $item->context_type,
                'content' => $content,
                'tokens' => $tokens,
            ];
        }

        $fullText = '';
        $totalTokens = 0;

        foreach ($parts as $part) {
            $fullText .= "=== {$part['name']} ({$part['type']}) ===\n";
            $fullText .= $part['content']."\n\n";
            $totalTokens += $part['tokens'];
        }

        return [
            'text' => $fullText,
            'tokens' => $totalTokens,
            'items' => $parts,
        ];
    }
}
