<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PromptFolder Model
 *
 * Represents a folder for organizing prompts.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $color
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PromptFolder extends Model
{
    /** @use HasFactory<\Database\Factories\PromptFolderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_id',
        'name',
        'color',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<PromptFolder, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(PromptFolder::class, 'parent_id');
    }

    /**
     * @return HasMany<PromptFolder, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(PromptFolder::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<Prompt, $this>
     */
    public function prompts(): HasMany
    {
        return $this->hasMany(Prompt::class, 'folder_id')->orderBy('sort_order');
    }

    /**
     * Scope for root folders (no parent).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<PromptFolder>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PromptFolder>
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
