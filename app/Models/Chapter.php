<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    /** @use HasFactory<\Database\Factories\ChapterFactory> */
    use HasFactory;

    protected $fillable = [
        'novel_id',
        'act_id',
        'title',
        'summary',
        'position',
        'settings',
        'disable_numeration',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'settings' => 'array',
            'disable_numeration' => 'boolean',
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
     * @return BelongsTo<Act, $this>
     */
    public function act(): BelongsTo
    {
        return $this->belongsTo(Act::class);
    }

    /**
     * @return HasMany<Scene, $this>
     */
    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class)->orderBy('position');
    }

    /**
     * Get total word count for this chapter.
     */
    public function getWordCountAttribute(): int
    {
        return $this->scenes->sum('word_count');
    }
}
