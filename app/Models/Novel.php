<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Novel extends Model
{
    /** @use HasFactory<\Database\Factories\NovelFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pen_name_id',
        'title',
        'description',
        'genre',
        'pov',
        'tense',
        'cover_path',
        'word_count',
        'chapter_count',
        'status',
        'last_edited_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'word_count' => 'integer',
            'chapter_count' => 'integer',
            'last_edited_at' => 'datetime',
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
     * @return BelongsTo<PenName, $this>
     */
    public function penName(): BelongsTo
    {
        return $this->belongsTo(PenName::class);
    }

    /**
     * @return HasMany<Chapter, $this>
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('position');
    }

    /**
     * @return HasMany<Act, $this>
     */
    public function acts(): HasMany
    {
        return $this->hasMany(Act::class)->orderBy('position');
    }

    /**
     * @return HasMany<SceneLabel, $this>
     */
    public function labels(): HasMany
    {
        return $this->hasMany(SceneLabel::class)->orderBy('position');
    }

    /**
     * @return HasManyThrough<Scene, Chapter, $this>
     */
    public function scenes(): HasManyThrough
    {
        return $this->hasManyThrough(Scene::class, Chapter::class);
    }

    /**
     * Recalculate and update word count from all scenes.
     */
    public function recalculateWordCount(): void
    {
        $this->word_count = $this->scenes()->sum('word_count');
        $this->chapter_count = $this->chapters()->count();
        $this->save();
    }
}
