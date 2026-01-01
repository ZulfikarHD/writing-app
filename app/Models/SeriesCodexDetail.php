<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeriesCodexDetail extends Model
{
    protected $fillable = [
        'series_codex_entry_id',
        'label',
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
     * @return BelongsTo<SeriesCodexEntry, $this>
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(SeriesCodexEntry::class, 'series_codex_entry_id');
    }
}
