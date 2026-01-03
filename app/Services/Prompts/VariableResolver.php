<?php

namespace App\Services\Prompts;

use App\Models\Novel;
use App\Models\Scene;
use App\Models\User;
use App\Services\Codex\CodexContextBuilder;

/**
 * Resolves variables in prompt templates.
 *
 * Variables use the syntax: {variable_name} or {function(param1, param2)}
 */
class VariableResolver
{
    /**
     * Context data available for variable resolution.
     *
     * @var array<string, mixed>
     */
    protected array $context = [];

    public function __construct(
        protected ?CodexContextBuilder $codexContextBuilder = null
    ) {}

    /**
     * Set the context for variable resolution.
     *
     * @param  array<string, mixed>  $context
     */
    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Add context data.
     *
     * @param  array<string, mixed>  $context
     */
    public function addContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);

        return $this;
    }

    /**
     * Build context from a scene.
     */
    public function buildSceneContext(Scene $scene): self
    {
        $scene->load(['chapter.act', 'labels', 'mentions.codexEntry']);

        $novel = $scene->chapter?->act?->novel ?? $scene->chapter?->novel;

        $this->context['scene'] = [
            'title' => $scene->title,
            'fullText' => $scene->content,
            'summary' => $scene->summary,
            'labels' => $scene->labels->pluck('name')->join(', '),
            'number' => $scene->sort_order + 1,
        ];

        if ($scene->chapter) {
            $this->context['chapter'] = [
                'name' => $scene->chapter->title,
                'number' => $scene->chapter->sort_order + 1,
            ];
        }

        if ($scene->chapter?->act) {
            $this->context['act'] = [
                'name' => $scene->chapter->act->title,
                'number' => $scene->chapter->act->sort_order + 1,
            ];
        }

        if ($novel) {
            $this->context['novel'] = [
                'title' => $novel->title,
                'author' => $novel->pen_name ?? $novel->user?->name ?? 'Unknown',
                'tense' => $novel->tense ?? 'past',
                'language' => $novel->language ?? 'English',
            ];
        }

        // Build codex context
        if ($this->codexContextBuilder && $novel) {
            $codexContext = $this->codexContextBuilder->buildForScene($scene, $novel);
            $this->context['codex'] = $codexContext;
        }

        return $this;
    }

    /**
     * Build context from a novel.
     */
    public function buildNovelContext(Novel $novel): self
    {
        $this->context['novel'] = [
            'title' => $novel->title,
            'author' => $novel->pen_name ?? $novel->user?->name ?? 'Unknown',
            'outline' => $novel->outline ?? '',
            'tense' => $novel->tense ?? 'past',
            'language' => $novel->language ?? 'English',
        ];

        return $this;
    }

    /**
     * Resolve all variables in a text.
     */
    public function resolve(string $text): string
    {
        // Match {variable} and {function(params)} patterns
        return preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_.]*(?:\([^)]*\))?)\}/',
            fn ($matches) => $this->resolveVariable($matches[1]),
            $text
        ) ?? $text;
    }

    /**
     * Resolve a single variable.
     */
    protected function resolveVariable(string $variable): string
    {
        // Check for function syntax: function(params)
        if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_.]*)(?:\(([^)]*)\))?$/', $variable, $matches)) {
            $name = $matches[1];
            $params = isset($matches[2]) ? $this->parseParams($matches[2]) : [];

            return $this->resolveFunction($name, $params);
        }

        return "{{$variable}}"; // Return as-is if not matched
    }

    /**
     * Parse function parameters.
     *
     * @return array<string>
     */
    protected function parseParams(string $paramsString): array
    {
        if (empty($paramsString)) {
            return [];
        }

        // Simple comma-separated params, trimmed and unquoted
        return array_map(
            fn ($param) => trim($param, " \t\n\r\0\x0B\"'"),
            explode(',', $paramsString)
        );
    }

    /**
     * Resolve a function/variable name to its value.
     *
     * @param  array<string>  $params
     */
    protected function resolveFunction(string $name, array $params): string
    {
        // Handle dot notation for nested access
        $parts = explode('.', $name);
        $value = $this->context;

        foreach ($parts as $part) {
            if (is_array($value) && isset($value[$part])) {
                $value = $value[$part];
            } elseif (is_object($value) && isset($value->$part)) {
                $value = $value->$part;
            } else {
                // Try built-in functions
                return $this->resolveBuiltIn($name, $params);
            }
        }

        // Format arrays/objects as strings
        if (is_array($value)) {
            return $this->formatArrayValue($value);
        }

        return (string) $value;
    }

    /**
     * Resolve built-in functions.
     *
     * @param  array<string>  $params
     */
    protected function resolveBuiltIn(string $name, array $params): string
    {
        return match ($name) {
            // Context functions
            'textBefore' => $this->context['textBefore'] ?? '',
            'textAfter' => $this->context['textAfter'] ?? '',
            'storySoFar' => $this->context['storySoFar'] ?? '',
            'storyToCome' => $this->context['storyToCome'] ?? '',
            'message' => $this->context['message'] ?? '',
            'content' => $this->context['content'] ?? '',
            'nextBeat' => $this->context['nextBeat'] ?? '',
            'previousBeat' => $this->context['previousBeat'] ?? '',

            // Input function
            'input' => $this->resolveInput($params),

            // Include component
            'include' => $this->resolveInclude($params),

            // Date
            'date.today' => now()->format('F j, Y'),

            // Personas
            'personas' => $this->context['personas'] ?? '',

            // Conditional logic
            'ifs' => $this->resolveIfs($params),
            'isEmpty' => $this->resolveIsEmpty($params),

            // Text functions
            'wordCount' => $this->resolveWordCount($params),
            'firstWords' => $this->resolveFirstWords($params),
            'lastWords' => $this->resolveLastWords($params),

            // Default: return as-is
            default => "{{$name}}" . ($params ? '(' . implode(', ', $params) . ')' : ''),
        };
    }

    /**
     * Resolve input() function - gets user input value.
     *
     * @param  array<string>  $params
     */
    protected function resolveInput(array $params): string
    {
        $inputName = $params[0] ?? '';
        $inputs = $this->context['inputs'] ?? [];

        return $inputs[$inputName] ?? '';
    }

    /**
     * Resolve include() function - includes a component.
     *
     * @param  array<string>  $params
     */
    protected function resolveInclude(array $params): string
    {
        $componentName = $params[0] ?? '';

        // Component resolution is handled by ComponentResolver
        return "[[{$componentName}]]";
    }

    /**
     * Resolve ifs() conditional function.
     *
     * @param  array<string>  $params
     */
    protected function resolveIfs(array $params): string
    {
        $condition = $params[0] ?? '';
        $then = $params[1] ?? '';
        $else = $params[2] ?? '';

        // Resolve the condition
        $conditionResolved = $this->resolve("{{$condition}}");

        // Check if truthy
        $isTruthy = ! empty($conditionResolved) && $conditionResolved !== 'false' && $conditionResolved !== '0';

        return $isTruthy ? $then : $else;
    }

    /**
     * Resolve isEmpty() function.
     *
     * @param  array<string>  $params
     */
    protected function resolveIsEmpty(array $params): string
    {
        $varName = $params[0] ?? '';
        $resolved = $this->resolve("{{$varName}}");

        return empty(trim($resolved)) ? 'true' : 'false';
    }

    /**
     * Resolve wordCount() function.
     *
     * @param  array<string>  $params
     */
    protected function resolveWordCount(array $params): string
    {
        $varName = $params[0] ?? '';
        $text = $this->resolve("{{$varName}}");

        return (string) str_word_count(strip_tags($text));
    }

    /**
     * Resolve firstWords() function.
     *
     * @param  array<string>  $params
     */
    protected function resolveFirstWords(array $params): string
    {
        $varName = $params[0] ?? '';
        $count = (int) ($params[1] ?? 100);
        $text = $this->resolve("{{$varName}}");

        $words = explode(' ', $text);

        return implode(' ', array_slice($words, 0, $count));
    }

    /**
     * Resolve lastWords() function.
     *
     * @param  array<string>  $params
     */
    protected function resolveLastWords(array $params): string
    {
        $varName = $params[0] ?? '';
        $count = (int) ($params[1] ?? 100);
        $text = $this->resolve("{{$varName}}");

        $words = explode(' ', $text);

        return implode(' ', array_slice($words, -$count));
    }

    /**
     * Format an array value as a string.
     *
     * @param  array<mixed>  $value
     */
    protected function formatArrayValue(array $value): string
    {
        // Check if it's a list of items with 'name' or 'title' keys
        if (isset($value[0]) && is_array($value[0])) {
            $items = array_map(function ($item) {
                if (isset($item['name'])) {
                    return $item['name'];
                }
                if (isset($item['title'])) {
                    return $item['title'];
                }

                return json_encode($item);
            }, $value);

            return implode("\n", $items);
        }

        // Check for specific structured data
        if (isset($value['characters'])) {
            return $this->formatCodexEntries($value['characters']);
        }

        if (isset($value['locations'])) {
            return $this->formatCodexEntries($value['locations']);
        }

        return json_encode($value, JSON_PRETTY_PRINT);
    }

    /**
     * Format codex entries for prompt context.
     *
     * @param  array<mixed>  $entries
     */
    protected function formatCodexEntries(array $entries): string
    {
        $formatted = [];

        foreach ($entries as $entry) {
            $name = $entry['name'] ?? 'Unknown';
            $description = $entry['description'] ?? '';
            $formatted[] = "**{$name}**: {$description}";
        }

        return implode("\n", $formatted);
    }
}
