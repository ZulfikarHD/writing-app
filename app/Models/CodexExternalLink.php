<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores external links for codex entry research.
 *
 * Part of the Research tab - these links are for writer reference
 * and are NOT sent to AI context.
 *
 * @see https://www.novelcrafter.com/help/docs/codex/anatomy-codex-entry
 */
class CodexExternalLink extends Model
{
    protected $fillable = [
        'codex_entry_id',
        'title',
        'url',
        'notes',
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
    public function codexEntry(): BelongsTo
    {
        return $this->belongsTo(CodexEntry::class);
    }
}
