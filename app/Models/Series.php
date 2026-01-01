<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Series extends Model
{
    /** @use HasFactory<\Database\Factories\SeriesFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'cover_path',
        'genre',
        'sort_order',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Novel, $this>
     */
    public function novels(): HasMany
    {
        return $this->hasMany(Novel::class)->orderBy('series_order');
    }

    /**
     * @return HasMany<SeriesCodexEntry, $this>
     */
    public function codexEntries(): HasMany
    {
        return $this->hasMany(SeriesCodexEntry::class)->orderBy('sort_order');
    }

    /**
     * Get total word count across all novels in the series.
     */
    public function getTotalWordCountAttribute(): int
    {
        return $this->novels()->sum('word_count');
    }

    /**
     * Get novel count in the series.
     */
    public function getNovelCountAttribute(): int
    {
        return $this->novels()->count();
    }
}
