<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodexAlias extends Model
{
    protected $fillable = [
        'codex_entry_id',
        'alias',
    ];

    /**
     * @return BelongsTo<CodexEntry, $this>
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(CodexEntry::class, 'codex_entry_id');
    }
}
