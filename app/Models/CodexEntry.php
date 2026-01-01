<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'thumbnail_path',
        'ai_context_mode',
        'sort_order',
        'is_archived',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_archived' => 'boolean',
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
     * @return HasMany<CodexMention, $this>
     */
    public function mentions(): HasMany
    {
        return $this->hasMany(CodexMention::class);
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
