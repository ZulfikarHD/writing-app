<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
