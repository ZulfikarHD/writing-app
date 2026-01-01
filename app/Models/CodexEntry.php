<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CodexEntry Model
 *
 * Represents a world-building entry (character, location, item, etc.)
 * Sprint 14 adds tags relationship for quick organizational labels.
 *
 * @property int $id
 * @property int $novel_id
 * @property string $type
 * @property string $name
 * @property string|null $description
 * @property string|null $research_notes
 * @property string|null $thumbnail_path
 * @property string $ai_context_mode
 * @property int $sort_order
 * @property bool $is_archived
 * @property bool $is_tracking_enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class CodexEntry extends Model
{
    /** @use HasFactory<\Database\Factories\CodexEntryFactory> */
    use HasFactory;

    use SoftDeletes;

    public const TYPE_CHARACTER = 'character';

    public const TYPE_LOCATION = 'location';

    public const TYPE_ITEM = 'item';

    public const TYPE_LORE = 'lore';

    public const TYPE_ORGANIZATION = 'organization';

    public const TYPE_SUBPLOT = 'subplot';

    public const CONTEXT_ALWAYS = 'always';

    public const CONTEXT_DETECTED = 'detected';

    public const CONTEXT_MANUAL = 'manual';

    public const CONTEXT_NEVER = 'never';

    protected $fillable = [
        'novel_id',
        'type',
        'name',
        'description',
        'research_notes',
        'thumbnail_path',
        'ai_context_mode',
        'sort_order',
        'is_archived',
        'is_tracking_enabled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_archived' => 'boolean',
            'is_tracking_enabled' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Novel, $this>
     */
    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }

    /**
     * @return HasMany<CodexAlias, $this>
     */
    public function aliases(): HasMany
    {
        return $this->hasMany(CodexAlias::class);
    }

    /**
     * @return HasMany<CodexDetail, $this>
     */
    public function details(): HasMany
    {
        return $this->hasMany(CodexDetail::class)->orderBy('sort_order');
    }

    /**
     * Relations where this entry is the source.
     *
     * @return HasMany<CodexRelation, $this>
     */
    public function outgoingRelations(): HasMany
    {
        return $this->hasMany(CodexRelation::class, 'source_entry_id');
    }

    /**
     * Relations where this entry is the target.
     *
     * @return HasMany<CodexRelation, $this>
     */
    public function incomingRelations(): HasMany
    {
        return $this->hasMany(CodexRelation::class, 'target_entry_id');
    }

    /**
     * @return HasMany<CodexProgression, $this>
     */
    public function progressions(): HasMany
    {
        return $this->hasMany(CodexProgression::class)->orderBy('sort_order');
    }

    /**
     * @return BelongsToMany<CodexCategory, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(CodexCategory::class, 'codex_entry_categories');
    }

    /**
     * Tags for quick organization (Sprint 14: US-12.4).
     * Tags are NOT sent to AI - they're purely organizational.
     *
     * @return BelongsToMany<CodexTag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(CodexTag::class, 'codex_entry_tags');
    }

    /**
     * @return HasMany<CodexMention, $this>
     */
    public function mentions(): HasMany
    {
        return $this->hasMany(CodexMention::class);
    }

    /**
     * @return HasMany<CodexExternalLink, $this>
     */
    public function externalLinks(): HasMany
    {
        return $this->hasMany(CodexExternalLink::class)->orderBy('sort_order');
    }

    /**
     * Get all searchable terms (name + aliases) for mention detection.
     *
     * @return array<string>
     */
    public function getSearchableTerms(): array
    {
        $terms = [$this->name];
        foreach ($this->aliases as $alias) {
            $terms[] = $alias->alias;
        }

        return $terms;
    }

    /**
     * Scope for active (non-archived) entries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<CodexEntry>  $query
     * @return \Illuminate\Database\Eloquent\Builder<CodexEntry>
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope for a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<CodexEntry>  $query
     * @return \Illuminate\Database\Eloquent\Builder<CodexEntry>
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get available types.
     *
     * @return array<string>
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_CHARACTER,
            self::TYPE_LOCATION,
            self::TYPE_ITEM,
            self::TYPE_LORE,
            self::TYPE_ORGANIZATION,
            self::TYPE_SUBPLOT,
        ];
    }

    /**
     * Get available AI context modes.
     *
     * @return array<string>
     */
    public static function getContextModes(): array
    {
        return [
            self::CONTEXT_ALWAYS,
            self::CONTEXT_DETECTED,
            self::CONTEXT_MANUAL,
            self::CONTEXT_NEVER,
        ];
    }
}
