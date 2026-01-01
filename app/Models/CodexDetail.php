<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * CodexDetail Model
 *
 * Represents individual details/attributes on a codex entry.
 * Sprint 14 adds support for different types and AI visibility control.
 *
 * @property int $id
 * @property int $codex_entry_id
 * @property int|null $definition_id
 * @property string $key_name
 * @property string $value
 * @property int $sort_order
 * @property string $ai_visibility
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CodexDetail extends Model
{
    public const TYPE_TEXT = 'text';

    public const TYPE_LINE = 'line';

    public const TYPE_DROPDOWN = 'dropdown';

    public const TYPE_CODEX_REFERENCE = 'codex_reference';

    public const AI_VISIBILITY_ALWAYS = 'always';

    public const AI_VISIBILITY_NEVER = 'never';

    public const AI_VISIBILITY_NSFW_ONLY = 'nsfw_only';

    protected $fillable = [
        'codex_entry_id',
        'definition_id',
        'key_name',
        'value',
        'sort_order',
        'ai_visibility',
        'type',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
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
     * @return BelongsTo<CodexDetailDefinition, $this>
     */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(CodexDetailDefinition::class, 'definition_id');
    }

    /**
     * Progressions that modify this specific detail.
     *
     * @return HasMany<CodexProgression, $this>
     */
    public function progressions(): HasMany
    {
        return $this->hasMany(CodexProgression::class)->orderBy('sort_order');
    }

    /**
     * Get the effective type (from definition or direct type field).
     */
    public function getEffectiveType(): string
    {
        if ($this->definition) {
            return $this->definition->type;
        }

        return $this->type ?? self::TYPE_TEXT;
    }

    /**
     * Get the effective AI visibility (from definition or direct field).
     */
    public function getEffectiveAiVisibility(): string
    {
        if ($this->definition) {
            return $this->definition->ai_visibility;
        }

        return $this->ai_visibility ?? self::AI_VISIBILITY_ALWAYS;
    }

    /**
     * Check if this detail should be included in AI context.
     */
    public function shouldIncludeInAiContext(bool $isNsfw = false): bool
    {
        $visibility = $this->getEffectiveAiVisibility();

        return match ($visibility) {
            self::AI_VISIBILITY_ALWAYS => true,
            self::AI_VISIBILITY_NEVER => false,
            self::AI_VISIBILITY_NSFW_ONLY => $isNsfw,
            default => true,
        };
    }

    /**
     * Get available detail types.
     *
     * @return array<string>
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_TEXT,
            self::TYPE_LINE,
            self::TYPE_DROPDOWN,
            self::TYPE_CODEX_REFERENCE,
        ];
    }

    /**
     * Get available AI visibility modes.
     *
     * @return array<string>
     */
    public static function getAiVisibilityModes(): array
    {
        return [
            self::AI_VISIBILITY_ALWAYS,
            self::AI_VISIBILITY_NEVER,
            self::AI_VISIBILITY_NSFW_ONLY,
        ];
    }
}
