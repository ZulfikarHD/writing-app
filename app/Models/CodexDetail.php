<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CodexDetail extends Model
{
    protected $fillable = [
        'codex_entry_id',
        'key_name',
        'value',
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
     * Progressions that modify this specific detail.
     *
     * @return HasMany<CodexProgression, $this>
     */
    public function progressions(): HasMany
    {
        return $this->hasMany(CodexProgression::class)->orderBy('sort_order');
    }
}
