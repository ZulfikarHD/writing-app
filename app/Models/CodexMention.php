<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodexMention extends Model
{
    protected $fillable = [
        'codex_entry_id',
        'scene_id',
        'mention_count',
        'positions',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mention_count' => 'integer',
            'positions' => 'array',
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
     * @return BelongsTo<Scene, $this>
     */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }
}
