<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CodexCategory extends Model
{
    protected $fillable = [
        'novel_id',
        'name',
        'color',
        'parent_id',
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
     * @return BelongsTo<Novel, $this>
     */
    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }

    /**
     * @return BelongsTo<CodexCategory, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CodexCategory::class, 'parent_id');
    }

    /**
     * @return HasMany<CodexCategory, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(CodexCategory::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @return BelongsToMany<CodexEntry, $this>
     */
    public function entries(): BelongsToMany
    {
        return $this->belongsToMany(CodexEntry::class, 'codex_entry_categories');
    }
}
