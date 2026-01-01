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
        'preset_type',
        'position',
    ];

    /**
     * Preset types for quick label creation.
     */
    public const PRESET_STATUS = [
        ['name' => 'Idea', 'color' => '#FDE68A'],
        ['name' => 'Draft', 'color' => '#FCA5A5'],
        ['name' => 'Edited', 'color' => '#6EE7B7'],
        ['name' => 'Finalized', 'color' => '#93C5FD'],
    ];

    public const PRESET_TEMPORAL = [
        ['name' => 'Past', 'color' => '#C4B5FD'],
        ['name' => 'Present', 'color' => '#6EE7B7'],
        ['name' => 'Future', 'color' => '#93C5FD'],
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
