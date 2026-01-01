<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SceneLabel extends Model
{
    /** @use HasFactory<\Database\Factories\SceneLabelFactory> */
    use HasFactory;

    protected $fillable = [
        'novel_id',
        'name',
        'color',
        'position',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
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
     * @return BelongsToMany<Scene, $this>
     */
    public function scenes(): BelongsToMany
    {
        return $this->belongsToMany(Scene::class, 'scene_label');
    }
}
