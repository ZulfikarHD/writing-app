<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SceneSection extends Model
{
    /** @use HasFactory<\Database\Factories\SceneSectionFactory> */
    use HasFactory;

    /**
     * Section types.
     */
    public const TYPE_CONTENT = 'content';

    public const TYPE_NOTE = 'note';

    public const TYPE_ALTERNATIVE = 'alternative';

    public const TYPE_BEAT = 'beat';

    public const TYPES = [
        self::TYPE_CONTENT => 'Content',
        self::TYPE_NOTE => 'Note',
        self::TYPE_ALTERNATIVE => 'Alternative',
        self::TYPE_BEAT => 'Beat',
    ];

    /**
     * Default colors per section type.
     */
    public const TYPE_COLORS = [
        self::TYPE_CONTENT => '#6366f1', // Indigo
        self::TYPE_NOTE => '#eab308', // Yellow
        self::TYPE_ALTERNATIVE => '#8b5cf6', // Violet
        self::TYPE_BEAT => '#22c55e', // Green
    ];

    protected $fillable = [
        'scene_id',
        'type',
        'title',
        'content',
        'color',
        'is_collapsed',
        'exclude_from_ai',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_collapsed' => 'boolean',
            'exclude_from_ai' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Scene, $this>
     */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    /**
     * Calculate word count from content JSON.
     */
    public function calculateWordCount(): int
    {
        if (empty($this->content)) {
            return 0;
        }

        $text = $this->extractTextFromContent($this->content);

        return str_word_count($text);
    }

    /**
     * Extract plain text from TipTap JSON content recursively.
     *
     * @param  array<string, mixed>  $content
     */
    private function extractTextFromContent(array $content): string
    {
        $text = '';

        if (isset($content['text'])) {
            $text .= $content['text'].' ';
        }

        if (isset($content['content']) && is_array($content['content'])) {
            foreach ($content['content'] as $node) {
                $text .= $this->extractTextFromContent($node);
            }
        }

        return $text;
    }

    /**
     * Scope for sections that should be included in AI context.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<SceneSection>  $query
     * @return \Illuminate\Database\Eloquent\Builder<SceneSection>
     */
    public function scopeForAiContext($query)
    {
        return $query->where('exclude_from_ai', false);
    }

    /**
     * Scope for sections that should be exported.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<SceneSection>  $query
     * @return \Illuminate\Database\Eloquent\Builder<SceneSection>
     */
    public function scopeExportable($query)
    {
        return $query->where('type', self::TYPE_CONTENT);
    }

    /**
     * Scope for sections by type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<SceneSection>  $query
     * @return \Illuminate\Database\Eloquent\Builder<SceneSection>
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if section should be included in AI context.
     */
    public function isVisibleToAi(): bool
    {
        return ! $this->exclude_from_ai;
    }

    /**
     * Check if section should be exported.
     */
    public function isExportable(): bool
    {
        return $this->type === self::TYPE_CONTENT;
    }

    /**
     * Get the default exclude_from_ai value based on type.
     */
    public static function getDefaultExcludeFromAi(string $type): bool
    {
        return match ($type) {
            self::TYPE_NOTE => true,
            self::TYPE_ALTERNATIVE => true,
            default => false,
        };
    }
}
