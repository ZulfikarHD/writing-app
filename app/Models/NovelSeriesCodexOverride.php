<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovelSeriesCodexOverride extends Model
{
    protected $fillable = [
        'novel_id',
        'series_codex_entry_id',
        'is_excluded',
        'local_entry_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_excluded' => 'boolean',
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
     * @return BelongsTo<SeriesCodexEntry, $this>
     */
    public function seriesEntry(): BelongsTo
    {
        return $this->belongsTo(SeriesCodexEntry::class, 'series_codex_entry_id');
    }

    /**
     * The local codex entry that overrides the series entry for this novel.
     *
     * @return BelongsTo<CodexEntry, $this>
     */
    public function localEntry(): BelongsTo
    {
        return $this->belongsTo(CodexEntry::class, 'local_entry_id');
    }
}
