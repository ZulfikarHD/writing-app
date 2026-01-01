<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeriesCodexAlias extends Model
{
    protected $fillable = [
        'series_codex_entry_id',
        'alias',
    ];

    /**
     * @return BelongsTo<SeriesCodexEntry, $this>
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(SeriesCodexEntry::class, 'series_codex_entry_id');
    }
}
