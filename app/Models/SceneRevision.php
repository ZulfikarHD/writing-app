<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SceneRevision extends Model
{
    /** @use HasFactory<\Database\Factories\SceneRevisionFactory> */
    use HasFactory;

    /**
     * Disable updated_at timestamp since we only track created_at.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'scene_id',
        'content',
        'word_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'word_count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Scene, $this>
     */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }
}
