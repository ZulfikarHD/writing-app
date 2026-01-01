<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeriesCodexEntry extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Share type constants with CodexEntry
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
        'series_id',
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
     * @return BelongsTo<Series, $this>
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    /**
     * @return HasMany<SeriesCodexAlias, $this>
     */
    public function aliases(): HasMany
    {
        return $this->hasMany(SeriesCodexAlias::class);
    }

    /**
     * @return HasMany<SeriesCodexDetail, $this>
     */
    public function details(): HasMany
    {
        return $this->hasMany(SeriesCodexDetail::class)->orderBy('sort_order');
    }

    /**
     * Get novel-level overrides for this series entry.
     *
     * @return HasMany<NovelSeriesCodexOverride, $this>
     */
    public function overrides(): HasMany
    {
        return $this->hasMany(NovelSeriesCodexOverride::class);
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
     * @param  \Illuminate\Database\Eloquent\Builder<SeriesCodexEntry>  $query
     * @return \Illuminate\Database\Eloquent\Builder<SeriesCodexEntry>
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope for a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<SeriesCodexEntry>  $query
     * @return \Illuminate\Database\Eloquent\Builder<SeriesCodexEntry>
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
}
