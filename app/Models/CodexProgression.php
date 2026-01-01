<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodexProgression extends Model
{
    public const MODE_ADDITION = 'addition';

    public const MODE_REPLACE = 'replace';

    protected $fillable = [
        'codex_entry_id',
        'codex_detail_id',
        'scene_id',
        'story_timestamp',
        'note',
        'new_value',
        'mode',
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
     * @return BelongsTo<CodexEntry, $this>
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(CodexEntry::class, 'codex_entry_id');
    }

    /**
     * @return BelongsTo<CodexDetail, $this>
     */
    public function detail(): BelongsTo
    {
        return $this->belongsTo(CodexDetail::class, 'codex_detail_id');
    }

    /**
     * @return BelongsTo<Scene, $this>
     */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }
}
