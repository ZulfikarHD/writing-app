<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CodexMention Model
 *
 * Tracks mentions of codex entries in scenes.
 * Sprint 16 (F-12.1.4): Added source field to track where mention was found.
 *
 * @property int $id
 * @property int $codex_entry_id
 * @property int $scene_id
 * @property int $mention_count
 * @property array|null $positions
 * @property string $source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CodexMention extends Model
{
    public const SOURCE_CONTENT = 'content';

    public const SOURCE_SUMMARY = 'summary';

    public const SOURCE_BOTH = 'both';

    protected $fillable = [
        'codex_entry_id',
        'scene_id',
        'mention_count',
        'positions',
        'source',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mention_count' => 'integer',
            'positions' => 'array',
        ];
    }

    /**
     * @return BelongsTo<CodexEntry, $this>
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(CodexEntry::class, 'codex_entry_id');
    }

    /**
     * @return BelongsTo<Scene, $this>
     */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    /**
     * Get available sources.
     *
     * @return array<string>
     */
    public static function getSources(): array
    {
        return [
            self::SOURCE_CONTENT,
            self::SOURCE_SUMMARY,
            self::SOURCE_BOTH,
        ];
    }

    /**
     * Check if mention was found in content.
     */
    public function isInContent(): bool
    {
        return $this->source === self::SOURCE_CONTENT || $this->source === self::SOURCE_BOTH;
    }

    /**
     * Check if mention was found in summary.
     */
    public function isInSummary(): bool
    {
        return $this->source === self::SOURCE_SUMMARY || $this->source === self::SOURCE_BOTH;
    }
}
